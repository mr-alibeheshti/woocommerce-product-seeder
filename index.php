<?php
/**
 * WooCommerce test product generator
 * Run with:
 * wp eval-file seed-products.php
 */

if (!defined('ABSPATH')) {
    exit("This file must be run inside WordPress via WP-CLI.\n");
}

if (!class_exists('WooCommerce')) {
    exit("WooCommerce is not active.\n");
}

if (!function_exists('media_sideload_image')) {
    require_once ABSPATH . 'wp-admin/includes/media.php';
}
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

set_time_limit(0);

$count = 100;

// Categories
$categories = [
    'Electronics',
    'Home & Kitchen',
    'Fashion',
    'Sports',
    'Beauty & Health',
    'Books & Stationery',
];

$tags = [
    'New',
    'Best Seller',
    'Special',
    'Test',
    'Popular',
    'Affordable',
    'Recommended',
];

// Create categories if not exists
$category_ids = [];
foreach ($categories as $cat_name) {
    $term = term_exists($cat_name, 'product_cat');
    if (!$term) {
        $term = wp_insert_term($cat_name, 'product_cat');
    }

    if (!is_wp_error($term)) {
        $category_ids[] = is_array($term) ? $term['term_id'] : $term;
    }
}

// Create tags if not exists
$tag_ids = [];
foreach ($tags as $tag_name) {
    $term = term_exists($tag_name, 'product_tag');
    if (!$term) {
        $term = wp_insert_term($tag_name, 'product_tag');
    }

    if (!is_wp_error($term)) {
        $tag_ids[] = is_array($term) ? $term['term_id'] : $term;
    }
}

$product_titles = [
    'Test Product',
    'Sample Item',
    'Special Product',
    'Store Item',
    'Professional Product',
    'Budget Product',
    'Premium Product',
];

$adjectives = [
    'Advanced',
    'Lightweight',
    'Durable',
    'Stylish',
    'Practical',
    'Smart',
    'High Quality',
    'Modern',
];

$created_product_ids = [];

/**
 * Download and attach image to WordPress media library with logging
 */
function seed_attach_image_from_url($image_url, $post_id, $desc = null) {
    if (defined('WP_CLI') && WP_CLI) {
        WP_CLI::log("Downloading image for post {$post_id} from: {$image_url}");
    }

    $head = wp_remote_head($image_url, [
        'timeout'     => 60,
        'redirection' => 10,
        'sslverify'   => false,
    ]);

    $final_url = $image_url;
    $content_type = '';

    if (!is_wp_error($head)) {
        $final_url = wp_remote_retrieve_header($head, 'location') ?: $image_url;

        if (!empty($head['http_response']) && method_exists($head['http_response'], 'get_response_object')) {
            $response_obj = $head['http_response']->get_response_object();
            if (!empty($response_obj->url)) {
                $final_url = $response_obj->url;
            }
        }

        $content_type = wp_remote_retrieve_header($head, 'content-type');
    }

    $tmp = download_url($image_url, 60);

    if (is_wp_error($tmp)) {
        return 0;
    }

    $path = parse_url($final_url, PHP_URL_PATH);
    $filename = basename($path);

    if (!$filename || strpos($filename, '.') === false) {
        $ext = 'jpg';

        if ($content_type) {
            $mime_to_ext = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/gif'  => 'gif',
                'image/webp' => 'webp',
            ];

            if (isset($mime_to_ext[$content_type])) {
                $ext = $mime_to_ext[$content_type];
            }
        }

        $filename = 'product-' . $post_id . '-' . time() . '.' . $ext;
    }

    $file_array = [
        'name'     => sanitize_file_name($filename),
        'tmp_name' => $tmp,
    ];

    $attachment_id = media_handle_sideload($file_array, $post_id, $desc);

    if (is_wp_error($attachment_id)) {
        @unlink($tmp);
        return 0;
    }

    return (int) $attachment_id;
}

for ($i = 1; $i <= $count; $i++) {
    $base_title = $product_titles[array_rand($product_titles)];
    $adj = $adjectives[array_rand($adjectives)];
    $title = "{$base_title} {$adj} {$i}";

    $regular_price = rand(200000, 5000000);
    $sale_price = (rand(0, 1) ? rand((int)($regular_price * 0.7), (int)($regular_price * 0.95)) : '');
    $stock_qty = rand(0, 120);
    $stock_status = $stock_qty > 0 ? 'instock' : 'outofstock';
    $sku = 'TEST-' . strtoupper(wp_generate_password(8, false, false)) . '-' . $i;

    $short_description = "This is a test product used to validate shop UI, cart, inventory, and pricing. Model {$i}.";
    $description = "
        <h2>Product Overview</h2>
        <p>{$title} is a fully featured WooCommerce test product used to test themes, filters, cart, and product pages.</p>

        <h3>Features</h3>
        <ul>
            <li>High build quality</li>
            <li>Modern and practical design</li>
            <li>Perfect for store testing</li>
            <li>Includes price, stock, images, and gallery</li>
        </ul>

        <h3>Specifications</h3>
        <p>This content is for testing purposes only.</p>
    ";

    $product = new WC_Product_Simple();
    $product->set_name($title);
    $product->set_status('publish');
    $product->set_catalog_visibility('visible');
    $product->set_description($description);
    $product->set_short_description($short_description);
    $product->set_sku($sku);
    $product->set_regular_price((string) $regular_price);

    if ($sale_price !== '' && $sale_price < $regular_price) {
        $product->set_sale_price((string) $sale_price);
    }

    $product->set_manage_stock(true);
    $product->set_stock_quantity($stock_qty);
    $product->set_stock_status($stock_status);

    $product->set_weight((string) rand(1, 10));
    $product->set_length((string) rand(10, 100));
    $product->set_width((string) rand(10, 100));
    $product->set_height((string) rand(5, 80));

    shuffle($category_ids);
    shuffle($tag_ids);

    $product->set_category_ids(array_slice($category_ids, 0, rand(1, 2)));
    $product->set_tag_ids(array_slice($tag_ids, 0, rand(1, 3)));

    $product_id = $product->save();

    if (!$product_id) continue;

    $featured_url = "https://picsum.photos/seed/product{$i}/1200/1200";
    $gallery_url_1 = "https://picsum.photos/seed/product{$i}a/1200/1200";
    $gallery_url_2 = "https://picsum.photos/seed/product{$i}b/1200/1200";

    $featured_id = seed_attach_image_from_url($featured_url, $product_id, $title . ' Featured');

    if ($featured_id) {
        set_post_thumbnail($product_id, $featured_id);
    }

    $gallery_ids = [];
    $g1 = seed_attach_image_from_url($gallery_url_1, $product_id);
    $g2 = seed_attach_image_from_url($gallery_url_2, $product_id);

    if ($g1) $gallery_ids[] = $g1;
    if ($g2) $gallery_ids[] = $g2;

    if (!empty($gallery_ids)) {
        update_post_meta($product_id, '_product_image_gallery', implode(',', $gallery_ids));
    }

    $created_product_ids[] = $product_id;

    WP_CLI::log("Created product #{$product_id}: {$title}");
}

WP_CLI::success("{$count} test products created successfully.");
