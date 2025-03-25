# Crawlaco | کرالاکو

[![WordPress](https://img.shields.io/wordpress/v/crawlaco.svg)](https://wordpress.org/plugins/crawlaco/)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D7.4-blue.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPLv2-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

Connect your WordPress/WooCommerce site to Crawlaco dashboard for seamless product and inventory management.

## Description

Crawlaco WordPress Plugin acts as a bridge between your WordPress/WooCommerce website and the Crawlaco dashboard. It simplifies communication between the two systems by handling API key generation, data synchronization, and attribute mapping.

### Key Features

- Easy setup with WebsiteKey validation
- Automatic API key generation and management
- Seamless data synchronization for articles and products
- User-friendly interface for attribute mapping
- Secure communication between your site and Crawlaco
- Real-time status monitoring
- RTL language support
- Translation ready

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- WooCommerce 3.0 or higher (for WooCommerce features)

## Installation

1. Upload the plugin files to the `/wp-content/plugins/crawlaco` directory, or install the plugin through the WordPress plugins screen directly
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to the Crawlaco menu item in your admin panel
4. Enter your WebsiteKey from the Crawlaco dashboard
5. Follow the setup wizard to complete the configuration

## How It Works

1. Enter your WebsiteKey from Crawlaco dashboard
2. Plugin automatically generates and configures API keys
3. Essential data is synchronized
4. Map your product attributes
5. Start managing your store through Crawlaco dashboard

## Security

We implement industry-standard security practices including:

- Secure API key generation and storage
- Encrypted communication
- Regular security updates

## FAQ

### Do I need a Crawlaco account?

Yes, you need a Crawlaco account to use this plugin. Visit [Crawlaco](https://crawlaco.com) to create one.

### Is WooCommerce required?

WooCommerce is required only if you want to sync and manage WooCommerce products. The plugin can work with regular WordPress posts and pages without WooCommerce.

### Can I use this plugin with a multilingual site?

Yes, the plugin supports RTL languages and is translation-ready.

## Support

Need help? Contact our support team through:

- [Crawlaco Support](https://crawlaco.com/support)
- [GitHub Issues](https://github.com/Coilaco/crawlaco-wp-plugin/issues)

## Privacy

The Crawlaco plugin connects your WordPress site with the Crawlaco dashboard service. It collects and transmits the following data:

- Website URL and WebsiteKey
- WooCommerce product data (if WooCommerce is installed)
- WordPress post data
- Product attributes and categories

For more information about how we handle your data, please visit our [Privacy Policy](https://crawlaco.com/privacy).

## License

This project is licensed under the GPLv2 or later - see the [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html) file for details.

## Changelog

### 1.1.2

- Update API base URL

### 1.1.1

* Fix SEO plugins issue in custom-functions.php
* Improved display of error messages in forms
* Improved settings page

### 1.1.0

- Improved plugin deactivation logic

### 1.0.0

- Initial release
- Basic setup wizard
- API key management
- Data synchronization
- Product attribute mapping
- RTL support
