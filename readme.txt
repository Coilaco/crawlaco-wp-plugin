=== Crawlaco | کرالاکو ===
Contributors: aminalih47
Tags: woocommerce, product-management, ecommerce, crawlaco, کرالاکو
Requires at least: 5.0
Tested up to: 6.8
Stable tag: 1.2.3
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Connect your WordPress/WooCommerce site to Crawlaco dashboard for seamless product and inventory management.

== Description ==

Crawlaco WordPress Plugin acts as a bridge between your WordPress/WooCommerce website and the Crawlaco dashboard. It simplifies communication between the two systems by handling API key generation, data synchronization, and attribute mapping.

= Key Features =

* Easy setup with WebsiteKey validation
* Automatic API key generation and management
* Seamless data synchronization for articles and products
* User-friendly interface for attribute mapping
* Secure communication between your site and Crawlaco
* Real-time status monitoring

= How It Works =

1. Enter your WebsiteKey from Crawlaco dashboard
2. Plugin automatically generates and configures API keys
3. Essential data is synchronized
4. Map your product attributes
5. Start managing your store through Crawlaco dashboard

= Requirements =

* WordPress 5.0 or higher
* PHP 7.4 or higher
* WooCommerce 3.0 or higher (for WooCommerce features)

== External Services ==

This plugin connects to the Crawlaco API service (api.crawlaco.com) to enable product and inventory management features. 
The service is essential for the plugin's core functionality.

= Data Transmission =

The plugin sends the following data to the Crawlaco API:

* Website URL and User WebsiteKey (during initial setup and all API requests)
* Website address (with each API request for identification)
* WooCommerce product data (if WooCommerce is installed)
* WordPress post data
* Product attributes and categories
* Task status and progress information

= When Data is Sent =

Data is transmitted in the following scenarios:
* During initial setup and WebsiteKey validation
* When synchronizing products and inventory
* When checking task status and progress
* When updating product information
* When mapping product attributes

= Service Information =

The Crawlaco API service is provided by Crawlaco. For more information about how we handle your data and our terms of service, please visit:
* [Crawlaco Terms of Service](https://crawlaco.com/terms)
* [Crawlaco Privacy Policy](https://crawlaco.com/privacy)

= Documentation =

For detailed documentation and setup instructions, please visit our [GitHub repository](https://github.com/Coilaco/crawlaco-wp-plugin).

= Support =

Need help? Contact our support team through:

* [Crawlaco Support](https://crawlaco.com/support)
* [GitHub Issues](https://github.com/Coilaco/crawlaco-wp-plugin/issues)

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/crawlaco` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to the Crawlaco menu item in your admin panel
4. Enter your WebsiteKey from the Crawlaco dashboard
5. Follow the setup wizard to complete the configuration

== Frequently Asked Questions ==

= Do I need a Crawlaco account? =

Yes, you need a Crawlaco account to use this plugin. Visit [Crawlaco](https://crawlaco.com) to create one.

= Is WooCommerce required? =

WooCommerce is required only if you want to sync and manage WooCommerce products. The plugin can work with regular WordPress posts and pages without WooCommerce.

= How secure is the connection? =

We use industry-standard security practices including:
* Secure API key generation and storage
* Encrypted communication
* Regular security updates

= Can I use this plugin with a multilingual site? =

Yes, the plugin supports RTL languages and is translation-ready.

== Screenshots ==

1. Plugin setup wizard
2. WebsiteKey validation screen
3. Product attribute mapping interface
4. Sync status dashboard
5. Settings page

== Changelog ==

= 1.2.0 =
* Fix attribute mapper issue in status and settings page
* Improve readme.txt and add more detail about plugin
* Remove unnecessary files
* Add crawlaco namespace to functions and classes
* Add inline styles to wp_enqueue_style
* Improve validating requests and sanitization

= 1.1.1 =
* Fix SEO plugins issue in custom-functions.php
* Improved display of error messages in forms
* Improved settings page

= 1.1.0 =
* Improved plugin deactivation logic

= 1.0.0 =
* Initial release
* Basic setup wizard
* API key management
* Data synchronization
* Product attribute mapping
* RTL support


== Privacy Policy ==

The Crawlaco plugin connects your WordPress site with the Crawlaco dashboard service. It collects and transmits the following data:

* Website URL and WebsiteKey
* WooCommerce product data (if WooCommerce is installed)
* WordPress post data
* Product attributes and categories

For more information about how we handle your data, please visit our [Privacy Policy](https://crawlaco.com/privacy). 