# 🚀 Dropshipping.Com.BD Addon for WooCommerce

A powerful WordPress plugin that seamlessly imports products from **Mohasagor.com.bd** API to your WooCommerce store with advanced features, beautiful UI, and comprehensive management tools.

![Plugin Version](https://img.shields.io/badge/version-2.0.0-blue.svg)
![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-green.svg)
![WooCommerce](https://img.shields.io/badge/WooCommerce-3.0%2B-purple.svg)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-orange.svg)

## ✨ Key Features

### 🛍️ **Advanced Product Import**
- **Bulk Import**: Import thousands of products with pagination support
- **Search & Import**: Find and import specific products individually
- **Resume Import**: Continue from where you left off
- **Duplicate Prevention**: Smart detection of already imported products
- **Progress Tracking**: Real-time import progress monitoring

### 💰 **Smart Pricing System**
- **Automatic Markup**: Configurable markup percentage
- **Price Display**: Show both regular and sale prices with profit calculation
- **Update Pricing**: Sync prices from API to keep them current
- **Profit Tracking**: Visual profit calculation in search results

### 🎨 **Product Management**
- **Variable Products**: Full support for product variations (Size, Color, etc.)
- **Image Import**: Automatic thumbnail and gallery image import
- **Category Mapping**: Intelligent category assignment with ID mapping
- **Custom Attributes**: Dynamic attribute creation for variations
- **SKU Generation**: Automatic SKU creation and validation

### 🔍 **Search & Discovery**
- **Live Search**: Real-time product search with pagination
- **Import Status**: Visual indicators for imported products
- **Quick Actions**: Import, update, or view products directly from search
- **Product Preview**: Rich product cards with images and pricing

### 🎛️ **Modern Admin Interface**
- **Dark/Light Theme**: Beautiful theme switcher [[memory:6981981]]
- **Responsive Design**: Works perfectly on all devices
- **Real-time Updates**: Live progress indicators and status updates
- **Intuitive Navigation**: Tabbed interface for easy management

### 📊 **Analytics & Tracking**
- **Import Statistics**: Track imported products count
- **Progress History**: View last import position and resume capability
- **Error Logging**: Comprehensive error tracking and debugging
- **Sync Status**: Monitor product synchronization status

## 🚀 Quick Start

### 1. Installation
```bash
# Upload to WordPress plugins directory
/wp-content/plugins/dropshippingbd-addon/

# Or install via WordPress admin
Plugins → Add New → Upload Plugin
```

### 2. Activation
1. Go to **Plugins** in your WordPress admin
2. Find **DropshippingBD Addon** and click **Activate**
3. Navigate to **DropshippingBD** in the admin menu

### 3. First Import
1. **Set Configuration**: Choose starting page and products per page
2. **Test Connection**: Verify API connectivity
3. **Start Import**: Click "Start Bulk Import" to begin
4. **Monitor Progress**: Watch real-time import progress

## 📖 Detailed Usage Guide

### 🔧 **Plugin Settings Tab**
Configure your plugin settings for optimal performance:

- **API Credentials**: Set up your Mohasagor.com.bd API access
- **Markup Percentage**: Configure profit margin (default: 20%)
- **Import Settings**: Set default products per page and starting page
- **Cache Management**: Clear API cache when needed

### 📦 **Product Importer Tab**
The main hub for all import operations:

#### **Bulk Import**
```php
// Configuration Options
- Starting Page: Choose which page to start from
- Products Per Page: Control batch size (recommended: 50-100)
- Resume from Last: Continue from previous import position
```

#### **Search & Import**
- **Live Search**: Type product names, categories, or keywords
- **Pagination**: Navigate through search results
- **Quick Actions**: 
  - 🟢 **Import**: Import new products
  - 🔄 **Update**: Update existing products
  - 👁️ **View**: Open product in WooCommerce
  - ✅ **Imported Badge**: Shows already imported products

#### **Import Management**
- **Last Import Progress**: View where your last import stopped
- **Delete All Products**: Remove all imported products (with confirmation)
- **Clear Progress**: Reset import tracking data

### 📈 **Dashboard Tab**
Monitor your store's performance:

- **Financial Overview**: Wallet balance and payment methods
- **Order Statistics**: Total orders and income tracking
- **Product Statistics**: Imported products count and status
- **Quick Actions**: Test connections and sync products

## 🏗️ Technical Architecture

### **Database Structure**
```sql
-- Custom tracking table
wp_dropshippingbd_products
├── id (Primary Key)
├── mohasagor_id (API Product ID)
├── woo_product_id (WooCommerce Product ID)
├── last_synced (Timestamp)
└── status (active/inactive)
```

### **Custom Fields Storage**
```php
// Product Meta Fields
_dropshippingbd_original_data     // Complete API data
_dropshippingbd_mohasagor_id      // Original API ID
_dropshippingbd_original_price    // Price before markup
_dropshippingbd_original_sale_price // Sale price before markup
_dropshippingbd_product_code      // Product code from API
_dropshippingbd_category          // Mapped category name
_dropshippingbd_markup_percentage // Applied markup
_dropshippingbd_last_synced       // Last sync timestamp
```

### **Category Mapping System**
```php
// Intelligent category mapping
1  => "Men's Fashion"
2  => "Women's Fashion"
3  => "Home & Lifestyle"
4  => "Gadgets"
5  => "Winter"
6  => "Year Closing Offer"
7  => "Other's"
9  => "Watch"
10 => "Islamic Item"
11 => "Kids Zone"
12 => "Customize Item"
13 => "Customize & Gift"
14 => "Rain item"
15 => "Gadgets & Electronics"
16 => "OFFER"
```

## 🔌 API Integration

### **Supported Endpoints**
- `GET /api/products` - Fetch products with pagination
- `GET /api/search/product/{keyword}` - Search products
- `GET /api/product/{id}` - Get single product details

### **API Response Structure**
```json
{
  "status": "SUCCESS",
  "products": {
    "current_page": 1,
    "data": [
      {
        "id": 507,
        "name": "Product Name",
        "product_code": "ABC123",
        "category_id": 15,
        "category": "Electronics",
        "thumbnail_img": "products/image.jpg",
        "price": 1000,
        "sale_price": 800,
        "details": "Product description...",
        "product_variants": [
          {
            "attribute": "Size",
            "variant": "L"
          }
        ],
        "product_images": [
          {
            "product_image": "products/gallery1.jpg"
          }
        ]
      }
    ],
    "last_page": 50,
    "total": 10000
  }
}
```

## 🎯 Advanced Features

### **Variable Product Creation**
```php
// Automatic conversion process
1. Detect product variants in API data
2. Convert simple product to variable product
3. Create custom attributes (Size, Color, etc.)
4. Generate all possible variations
5. Set individual variation prices with markup
6. Assign proper SKUs and stock status
```

### **Image Import System**
```php
// Smart image handling
1. Build proper URLs with base prefix
2. Download images from API
3. Create WordPress attachments
4. Set featured image (first image)
5. Add remaining images to gallery
6. Generate thumbnails and metadata
```

### **Error Handling & Logging**
```php
// Comprehensive error tracking
- API connection failures
- Image download errors
- SKU conflicts and resolution
- Variation creation issues
- Category mapping problems
- Debug logging for troubleshooting
```

## 🛠️ Troubleshooting

### **Common Issues & Solutions**

#### **Import Not Starting**
```bash
# Check these:
1. WooCommerce is active
2. API credentials are correct
3. Server has cURL enabled
4. WordPress debug mode shows errors
```

#### **Images Not Importing**
```bash
# Verify:
1. Server write permissions
2. Image URLs are accessible
3. PHP memory limit is sufficient
4. Check error logs for specific issues
```

#### **Variations Not Working**
```bash
# Ensure:
1. Product has valid variant data
2. Attributes are properly created
3. Variation prices are set
4. SKUs are unique
```

### **Debug Mode**
Enable WordPress debug mode for detailed error messages:
```php
// wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

### **Performance Optimization**
```php
// Recommended settings
- Products per page: 50-100 (not too high)
- Enable object caching
- Use CDN for images
- Regular database cleanup
- Monitor server resources
```

## 📋 Requirements

### **System Requirements**
- **WordPress**: 5.0 or higher
- **WooCommerce**: 3.0 or higher
- **PHP**: 7.4 or higher
- **MySQL**: 5.6 or higher
- **Memory**: 256MB minimum (512MB recommended)
- **Extensions**: cURL, GD, JSON

### **Server Requirements**
- **Write Permissions**: wp-content/uploads directory
- **Network Access**: Outbound HTTPS connections
- **Time Limits**: Sufficient for bulk operations
- **Storage**: Space for imported images

## 🔄 Changelog

### **Version 2.0.0** (Latest)
- ✨ **New**: Search & individual import functionality
- ✨ **New**: Resume import from last position
- ✨ **New**: Delete all imported products feature
- ✨ **New**: Category ID mapping system
- ✨ **New**: Enhanced image import with URL prefixing
- ✨ **New**: Profit calculation in search results
- ✨ **New**: View product button for imported products
- ✨ **New**: Comprehensive debug logging
- 🔧 **Improved**: Variable product creation with custom attributes
- 🔧 **Improved**: SKU generation and validation
- 🔧 **Improved**: Error handling and user feedback
- 🔧 **Improved**: UI/UX with better responsive design
- 🐛 **Fixed**: Image import issues for search products
- 🐛 **Fixed**: Category mapping consistency
- 🐛 **Fixed**: Import progress tracking

### **Version 1.0.0**
- 🎉 Initial release
- Basic product import functionality
- Price markup system
- Simple variation support
- Image import
- Category mapping
- Sync functionality

## 🤝 Support & Contributing

### **Getting Help**
- 📧 **Email Support**: Contact the plugin developer
- 🐛 **Bug Reports**: Report issues with detailed information
- 💡 **Feature Requests**: Suggest new features
- 📖 **Documentation**: Check this README for solutions

### **Contributing**
We welcome contributions! Please:
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

This plugin is licensed under the **GPL v2 or later**.

```
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
```

## 🙏 Acknowledgments

- **Mohasagor.com.bd** for providing the API
- **WooCommerce** team for the excellent e-commerce platform
- **WordPress** community for the robust CMS
- **Contributors** who helped improve this plugin

---

## 🚀 Ready to Get Started?

1. **Install** the plugin
2. **Configure** your settings
3. **Import** your first products
4. **Start selling** with DropshippingBD!

**Happy Dropshipping!** 🛍️✨

---

*Made with ❤️ for the WordPress and WooCommerce community*# dropshippingbd-addon
# dropshippingbd-addon
# dropshippingbd-addon
# dropshippingbd-addon
