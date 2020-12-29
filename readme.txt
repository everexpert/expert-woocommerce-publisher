=== Everexpert Publishers for WooCommerce ===
Contributors: everexpert, Naeem Hasan
Donate link: https://everexpert.com
Tags: woocommerce, woocommerce publishers, woocommerce product, woocommerce manufacturer, woocommerce supplier, e-commerce
Requires at least: 4.7
Tested up to: 5.5.1
Requires PHP: 5.6
Stable tag: 1.8.5
WC requires at least: 3.0
WC tested up to: 4.6.3
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Everexpert Publishers for WooCommerce allows you to show product publishers in your WooCommerce based store

== Description ==

Everexpert WooCommerce Publishers is a perfect tool to organize your site, highlight the publishers you have, and also helps as a filter for your customers at UX exploration. EWP extendes the product's description and presentation at your e-commerce site.

== PRESENTATION ==

[About Us](https://everexpert.com/) | [Community](https://www.facebook.com/groups/everexpert/) | [Documentation](https://everexpert.com/documentation/everexpert-woocommerce-publishers/)

Whether we like to admit it or not, we are all influenced by publishers. Publishers are a guarantee for quality, they assure product recognition in customers.
Is essential to work with product publishers for increase sales and generate reliability on your e-commerce site.
With this extension you can add product publishers to your WooCommerce site.

= Requirements =
> * PHP 5.6 or higher (PHP7 recommended)
> * WordPress 4.7 or higher
> * WooCommerce 3.1.0 or higher

= Features =
> * Very easy to use, 100% free, no ads, no premium version exists
> * Assign publishers to products
> * Associate a banner and a link to each publisher
> * Translation-ready
> * Visual Composer support
> * Minimalist design and fully responsive
> * Very lightweight
> * Shortcode: Display all publishers
> * Shortcode: Display publishers carousel
> * Shortcode: Display product carousel by publisher
> * Shortcode: Display publishers for a specific product
> * Shortcode: A-Z Listing
> * Widget: Display publishers as dropdown
> * Widget: Display publishers as list (publisher names or publisher logos)
> * Widget: Filter products by publisher
> * Customizable publishers slug
> * Show the publishers in products loop
> * Import publishers (migrate) from other publishers plugins
> * Dummy data installer (logos by heroturko)
> * WooCommerce REST API support
> * WooCommerce built-in product importer/exporter support
> * Publisher tab for single product page
> * Favorite publishers
> * Publishers json import/export
> * Publisher structured data
> * And much more!


== Installation ==
1. Upload the plugin to the '/wp-content/plugins/' directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.


== Shortcodes ==

These are the current shortcodes available to use in posts or pages.

------- Display publishers carousel -------

Enables a slider carousel including all publishers

[ewp-carousel items="10" items_to_show="5" items_to_scroll="1" image_size="thumbnail" autoplay="true"]

Parameters:

    “items” [int / featured] Total number of publishers
    “items_to_show” [int] Number of publishers per page
    “items_to_scroll” [int] Number of publishers to scroll each time
    “image_size” [thumbnail / medium / large / full / custom size] Author image size
    “autoplay” [true / false] Carousel autoplay
    “arrows” [true / false] Show or not the navigation arrows
    “hide_empty” [true / false] It hides publishers that have no products associated

------- Display product carousel by publisher -------

Displays a carousel slider including all products of a specified publisher

[ewp-product-carousel publisher="all" products="10" products_to_show="5" products_to_scroll="1" autoplay="true"]

Parameters:

    “publisher” [all / publisher slug]
    “products” [int] Total number of products
    “products_to_show” [int] Number of products per page
    “products_to_scroll” [int] Number of products to scroll each time
    “autoplay” [true / false] Carousel autoplay
    “arrows” [true / false] Show or not the navigation arrows

------- Products of specific publishers -------

Shows shop products that are associated to one or more publishers. This shortcode extends the default products shortcode.

[products publishers="publisher-slug"]

Parameters:

    “publishers” [comma separated publisher slugs] Show products associated with these publishers
    Inherited params from the WooCommerce [products] shortcode

------- All Authors -------

A paginated list of all publishers and their logos

[ewp-all-publishers per_page="10" image_size="thumbnail" hide_empty="true" order_by="name" order="ASC" title_position="before"]

Parameters:

    “title_position” [before/ after / none]

Here you can have a look at this shortcode in action.

------- A-Z Listing -------

Order all publishers alphabetically in a list

[ewp-az-listing]

Have a look at this shortcode here.
Display publishers of a specific product

It allows you to display publishers of a specific product. If "product_id" is empty the shortcode will try to get this value by itself.

[ewp-publisher product_id="5" image_size="thumbnail"]

Parameters:

    “product_id” [int / empty] Show publishers for this product
    “as_link” [true / false] Show publisher image or a text link
    “image_size” [thumbnail / medium / large / full / custom size] Author image size



== Frequently Asked Questions ==
= Is Everexpert WooCommerce Publishers free? =
Yes, of course. This plugin is 100% free. No ads, no premium version exists.

= Where are the plugin settings? =
Go to `WooCommerce/Settings/` and click on `Publishers` tab

= 404 error on publisher pages =
Go to `Settings/Permalinks` and click on `Save Changes` button to flush the permalinks

= EWP is awesome! Can I contribute? =
Yes you can! Join in on our [GitHub repository](https://github.com/everexpert/everexpert-woocommerce-publishers) 🙂
You can also contribute [translating the plugin](https://translate.wordpress.org/projects/wp-plugins/everexpert-woocommerce-publishers/)

= Developer Documentation =
[Click here](https://github.com/everexpert/everexpert-woocommerce-publishers/wiki)

== Screenshots ==
1. Publishers carousel
2. Publishers page
3. Publishers taxonomy
4. Publishers shortcodes (with Visual Composer)
5. Product carousel by publisher
