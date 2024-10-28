=== ATOL ECOM Payment Plugin for WooCommerce  ===
Contributors: Atol
Tags: Credit card, ATOL, WooCommerce, Payment gateway, ATOL Checkout
Requires at least: 6.2
Tested up to: 6.4
Requires PHP: 7.1
Stable tag: 1.0.21
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Activate ATOL checkout on your WooCommerce store.

== Description ==

The ATOL Pay Payments plugin allows you to accept payments via ATOL payment gateway on your WordPress site easily.

### Introduction ###

### Easily Set up ATOL Checkout ###

The plugin allows you to easily set up ATOL checkout in your WooCommerce store. ATOL checkout is a prebuilt checkout page hosted by ATOL. By enabling ATOL checkout, you can relieve yourself from having to build a checkout page from scratch for your WooCommerce store.

To learn how to install the plugin, navigate to the Installation tab.


### Plugin connectors ####

[Mobile API](https://api-mobile.atolpay.ru) interaction software interface for external users

The interaction software interface for external users (Mobile API) is part of the secure loop and complies with PCI DSS requirements. The Mobile API interface is located behind the CIPF subsystems (including Anti DDOS, WAF, etc.) and contains the methods necessary to perform basic operations. The methods provide the following capabilities:

* user authentication and authorization in the system,
* checking the CVM method of bank card verification,
* making payments,
* payment cancellation,
* preparing data for printing a slip receipt,
* request for a list of operations.

As a result of authentication and authorization operations, the user confirms his credentials by entering them, receiving in return a set of permissions to work in the system.
Support is provided for several ways to verify a bank card: by PIN code, by signature, etc.
Support for a mobile payment module is also provided for interaction with the internal circuit of the system.

Depending on the operating mode, the plugin can connect to the test or production ATOL API.

Those APIs are located in different environments and have different addresses.


#### Installation ####

= Minimum Requirements =

* PHP 7.1 or higher is recommended
* WordPress 5.6 or higher is recommended

= Prerequisites =
* Create an ATOL account.
* Generate payment token at ATOL Admin page.


All Atol API requests occur in either dev, test or live mode.
Use test mode to access test data at stage Atol server, and live mode to access actual account data.
Each mode has its own set of API tokens created at Atol Admin page.
Objects in one mode aren’t accessible to the other. For instance, a test-mode product object can’t be part of a live-mode payment.


= Steps to install the plugin =

To install the plugin, follow the below steps:

Step 1: Log in to your WordPress dashboard.
Step 2: Navigate to Plugins and select Add New.
Step 3: In the search bar, type “ATOL Payment Plugin for WooCommerce” and click Search Plugins.
Step 4: Once you find the ATOL Payment plugin, click on “Install Now”.
Step 5: After installation, click “Activate” to activate the plugin.





== Changelog ==

= 1.0.21 =
* Add SANDBOX env

= 1.0.20 =
* Add shipment tax sections in plugin settings

= 1.0.19 =
* Add PaymentMethod and PaymentSubject sections in plugin settings
* Fix Atol order status logic before create order
* Update transactionId generation for ATOL integration

= 1.0.18 =
* Add HPOS support
* Add pack.sh script to prepare build

= 1.0.16 =
* Add support ATOL callback request
* Add shipment to ATOL receipt entity
* Update passed create order parameters

= 1.0 =
* ATOL Overview and settings page.
* Checkout logic.
