=== MobCard Woo Payment Gateway ===
Contributors: SantiagoInteractive
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=R8LYPEDYY8EZE
Tags: mobilpay, woocommerce, mobilpay card, mobilpay payment gateway, mobilpay for woocommerce, mobilpay romania, mobilpay card for woocommerce
Requires at least: 4.0.1
Tested up to: 4.7.2
Stable tag: 1.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

MobCard Woo Payment Gateway extends WooCommerce payment options by adding the mobilPay Card Gateway.

== Description ==

MobCard Woo Payment Gateway extends WooCommerce payment options by adding the mobilPay Card Gateway.
This plugin is meant to be used by merchants in Romania.

= Features: =

* **100% FREE TO USE** (GPLv2 license).
* Integrates mobilPay card payments service with your WordPress + WooCommerce online shop.
* Accepts payments with Visa and Mastercard credit/debit cards.
* Handles IPN responses and automatically changes order status on your shop in real time (confirmed/paid or failure messages, even refunds).
* Multilanguage support (romanian translation included).

= Donate! =

I put some of my free time into developing and maintaining this plugin. If helped you in your projects and you are happy with it, you can [buy me a coffee](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=R8LYPEDYY8EZE "Donate to this plugin").

= GitHub =

You can also find this project on [GitHub](https://github.com/santiagointeractive/mobilpay-card-gateway-for-woocommerce "mobilPay Card Gateway for WooCommerce on GitHub"). Feel free to contribute, fork or pull requests.

== Installation ==

1. Upload `mobcard-woo-payment-gateway` to the `/wp-content/plugins/` directory.

2. Place your `private.key` and `public.cer` files from your mobilPay merchant account into `certificates` folder of the plugin.

3. Activate the plugin through the `Plugins` menu in WordPress.

4. Configure your settings under `WooCommerce > Settings > Checkout > mobilPay Card` option panel.

== Frequently Asked Questions ==

= Who can use this plugin? =

Any merchant that contracted mobilPay credit/debit card payment processing services.
Basically romanian companies.

= Can I take payments from PayPal? =

No. You can only take payments from credit/debit card processed by mobilPay only.

= How can I add my merchant certificates? =

Sandbox:

If you want to test the plugin under sandbox enviroment, upload your testing `private.key` and `public.cer` files into `certificates` folder of the plugin.

These certificates should look like this: `sandbox.XXXX-XXXX-XXXX-XXXX-XXXXprivate.key` and `sandbox.XXXX-XXXX-XXXX-XXXX-XXXX.public.cer`.

Live:

Upload your live `private.key` and `public.cer` files into `certificates` folder of the plugin.

These certificates should look like this: `live.XXXX-XXXX-XXXX-XXXX-XXXXprivate.key` and `live.XXXX-XXXX-XXXX-XXXX-XXXX.public.cer`.

Note:

Don't rename `.key` and `.cer` files and make sure that `XXXX-XXXX-XXXX-XXXX-XXXX` matches your Merchant ID.

= Payment redirect is not working. Why? =

WooCommerce plugin fails to load jQuery Cookie JavaScript due to current Mod_Security ruleset on your web server. Files: `jquery.cookie.js` and `jquery.cookie.min.js`, located inside folder `/plugins/woocommerce/assets/js/jquery-cookie/` may cause some issues with "Order" button and other minor template issues if not loaded properly.

To fix this small issue folow these steps:

Step 1: Login to FTP then rename files inside folder `/plugins/woocommerce/assets/js/jquery-cookie/`:

`jquery.cookie.js` into `jquery_cookie.js`
`jquery.cookie.min.js` into `jquery_cookie.min.js`

Step 2: Inside folder `/wp-content/themes/` find theme which is in use, for example, twentyfourteen then add following lines into `functions.php`:

`add_action( 'wp_enqueue_scripts', 'custom_woo_cookie_frontend' );`

`function custom_woo_cookie_frontend() {
  global $post, $woocommerce;
  $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
  wp_deregister_script( 'jquery-cookie' );
  wp_register_script( 'jquery-cookie', $woocommerce->plugin_url() . '/assets/js/jquery-cookie/jquery_cookie' . $suffix . '.js', array( 'jquery' ), '', true );
}`

Now the JavaScript files `jquery_cookie.js` and `jquery_cookie.min.js` won't produce 404 errors due to Mod_Security module interference.

== Screenshots ==

1. Backend: WooCommerce > Settings > Checkout
`screenshot-1.jpg`

2. Frontend: Your website checkout page
`screenshot-2.jpg`

== Changelog ==

= 1.0.1 =
* Fixed empty return url issue in some cases
* Tested up to WP 4.7.2 with WooCommerce 2.6.14

= 1.0 =
* Initial release (Tested up to WP 4.7 with WooCommerce 2.6.11)
