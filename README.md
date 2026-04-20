# woocommerce-product-seeder
A WP-CLI script to seed WooCommerce with realistic test products, including images, categories, tags, pricing, and stock data


# WooCommerce Test Product Generator

This script generates a batch of dummy WooCommerce products for testing purposes.

## 📌 Features

* Creates random products with:

  * Titles, descriptions, and SKUs
  * Prices (regular + optional sale)
  * Stock management
  * Categories and tags (auto-created if missing)
  * Featured images and gallery images
* Uses `picsum.photos` for placeholder images
* Generates realistic product data for UI/UX testing

---

## ⚙️ Requirements

* WordPress installed
* WooCommerce plugin activated
* WP-CLI installed and accessible
* PHP CLI enabled

---

## 🚀 Usage

1. Place the script in your WordPress root directory:

```bash
seed-products.php
```

2. Run the script using WP-CLI:

```bash
wp eval-file seed-products.php
```

---

## 🔧 Configuration

You can customize:

* Number of products:

```php
$count = 100;
```

* Categories & tags:

```php
$categories = [...];
$tags = [...];
```

* Product titles and adjectives:

```php
$product_titles = [...];
$adjectives = [...];
```

---

## 🧪 Use Cases

* Testing WooCommerce themes
* Performance testing with large catalogs
* UI/UX validation
* Plugin compatibility testing

---

## ⚠️ Notes

* This script is intended for **development/testing environments only**
* Do NOT run on production stores unless intended
* Images are fetched from an external placeholder service

---

## ✅ Output

After execution, you will see logs like:

```
Created product #123: Test Product Advanced 1
...
Success: 100 test products created successfully.
```

---

## 🧹 Cleanup

To remove test products, you can:

* Delete manually from admin
* Or use WP-CLI:

```bash
wp post delete $(wp post list --post_type=product --format=ids) --force
```
