# gif-module
Magento 2 module that adds a custom checkout step allowing customers to choose a Giphy(use API integration) image based on cart items. The selected image is saved with the order and displayed in both customer and admin order views. Admin can replace the image from the original Giphy suggestions.

## ✅ Features

- Works **only for logged-in customers**.
- Adds custom checkout step: **"Step Gifs"**.
- GIF images are fetched from **Giphy API** when the customer enters the checkout.
- Number of suggested images is **configurable** (`image_limit`, default: `3`).
- Selected image is saved to the database in `quote.gif_url` as a JSON array (the selected image is always the first item).
- On order placement, data is **copied** to `sales_order.gif_url` using Magento's **field copying** mechanism.
- Image is displayed in **Customer Account > My Orders** (from the latest order).
- Admin can **view and change** the selected image in **Customers > View > Gif tab**.
- Admin selects from **already fetched images**.

## ⚙️ Configuration

Navigate to admin panel:

Stores > Configuration > Meme > Giphy

Available options:

- **API Key** – Your Giphy developer API key.
- **Image Limit** – Number of GIFs to show in checkout step (default: 3).

You can also set default values in `config.xml`.

## 🧩 Technical Details

- Selected GIFs are stored in the `quote` table in the `gif_url` column as JSON.
- The array includes all suggested images, with the selected one always in the first position.
- Data is transferred from `quote.gif_url` to `sales_order.gif_url` using Magento's fieldset copying system.
- Admin can modify the GIF selection in a custom tab inside the **Customer Edit** section.

## 📦 Installation

1. Copy the module to `app/code/Vendor/ModuleName`
2. Run the following Magento CLI commands:

```bash
bin/magento module:enable Study_Meme
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:flush

## 📋 Requirements

- Magento 2.4.7 or higher  
- PHP 8.3
- Giphy Developer API Key
