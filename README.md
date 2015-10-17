# mobilPay Card Gateway for WooCommerce
Extends WooCommerce payment options by adding the mobilPay Card Gateway.

## Installation
* Download zip file from GitHub repository
* Extract download zip on `wp-content/plugins` folder of your WordPress install
* Add you `private.key` and `public.cer` files from your mobilPay merchant account into `certificates` folder of the plugin
* Activate the plugin from WordPress plugins panel

## Screenshots
[![mobilPay Card Gateway for WooCommerce - Backend](https://raw.githubusercontent.com/santiagointeractive/mobilpay-card-gateway-for-woocommerce/master/screenshot-1.jpg "Backend")](https://github.com/santiagointeractive/mobilpay-card-gateway-for-woocommerce/blob/master/screenshot-1.jpg)
[![mobilPay Card Gateway for WooCommerce - Frontend](https://raw.githubusercontent.com/santiagointeractive/mobilpay-card-gateway-for-woocommerce/master/screenshot-2.jpg "Frontend")](https://github.com/santiagointeractive/mobilpay-card-gateway-for-woocommerce/blob/master/screenshot-2.jpg)

## Merchant certificates

### Sandbox:
* If you want to test the plugin under sandbox enviroment, upload your testing `.key` and `.cer` files into `certificates` folder of the plugin.
* These certificates should look like this: `sandbox.XXXX-XXXX-XXXX-XXXX-XXXXprivate.key` and `sandbox.XXXX-XXXX-XXXX-XXXX-XXXX.public.cer`.

### Live:
* Upload your live `.key` and `.cer` files into `certificates` folder of the plugin.
* These certificates should look like this: `live.XXXX-XXXX-XXXX-XXXX-XXXXprivate.key` and `live.XXXX-XXXX-XXXX-XXXX-XXXX.public.cer`.

### Note:
* Don't rename `.key` and `.cer` files and make shure that `XXXX-XXXX-XXXX-XXXX-XXXX` matches your Merchant ID.

## License
mobilPay Card Gateway for WooCommerce is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 2 of the License, or any later version.
 
mobilPay Card Gateway for WooCommerce is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License along with mobilPay Card Gateway for WooCommerce. If not, see http://www.gnu.org/licenses/gpl-2.0.txt.

## Credits
Some of the classes that make all the payment process possible are developed by NETOPIA @mobilPay.

## Changelog
#### v 1.0
- Initial release

## Donate
I put some of my free time into developing and maintaining this plugin.
If helped you in your projects and you are happy with it, you can buy me a coffee.

[![Donate](https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=R8LYPEDYY8EZE)
