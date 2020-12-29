<?php

/**
 *  Plugin Name: Everexpert Publishers for WooCommerce
 *  Plugin URI: https://everexpert.com
 *  Description: Everexpert WooCommerce Publishers allows you to show product publishers in your WooCommerce based store.
 *  Version: 1.0.0
 *  Author: Naeem Hasan
 *  Author URI: https://everexpert.com
 *  Text Domain: everexpert-woocommerce-publishers
 *  Domain Path: /lang
 *  License: GPLv3
 *      Everexpert WooCommerce Publishers version 1.8.5, Copyright (C) 2019 EverExpert
 *      Everexpert WooCommerce Publishers is free software: you can redistribute it and/or modify
 *      it under the terms of the GNU General Public License as published by
 *      the Free Software Foundation, either version 3 of the License, or
 *      (at your option) any later version.
 *
 *      Everexpert WooCommerce Publishers is distributed in the hope that it will be useful,
 *      but WITHOUT ANY WARRANTY; without even the implied warranty of
 *      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *      GNU General Public License for more details.
 *      You should have received a copy of the GNU General Public License
 *      along with Everexpert WooCommerce Publishers.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  WC requires at least: 3.1.0
 *  WC tested up to: 4.6.3
 */

namespace Everexpert_Woocommerce_Publishers;

defined('ABSPATH') or die('No script kiddies please!');

//plugin constants
define('EWP_PLUGIN_FILE', __FILE__);
define('EWP_PLUGIN_URL', plugins_url('', __FILE__));
define('EWP_PLUGIN_DIR', __DIR__ . DIRECTORY_SEPARATOR);
define('EWP_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('EWP_PLUGIN_VERSION', '1.8.5');
define('EWP_PLUGIN_NAME', 'Everexpert WooCommerce Publishers');
define('EWP_PREFIX', 'ewp');
define('EWP_REVIEW_URL', 'https://everexpert.com');
define('EWP_DEMO_URL', 'https://everexpert.com');
define('EWP_PURCHASE_URL', EWP_DEMO_URL);
define('EWP_SUPPORT_URL', 'https://everexpert.com');
define('EWP_DOCUMENTATION_URL', 'https://everexpert.com');
define('EWP_GITHUB_URL', 'https://everexpert.com');
define('EWP_GROUP_URL', 'https://everexpert.com');

register_activation_hook(__FILE__, function() {
  update_option('ewp_activate_on', time());
});

//clean publishers slug on plugin deactivation
register_deactivation_hook(__FILE__, function() {
  update_option('old_wc_ewp_admin_tab_slug', 'null');
});

//loads textdomain for the translations
add_action('plugins_loaded', function() {
  load_plugin_textdomain('everexpert-woocommerce-publishers', false, EWP_PLUGIN_DIR . '/lang');
});

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if (is_plugin_active('woocommerce/woocommerce.php')) {

  require 'classes/class-ewp-term.php';
  require 'classes/widgets/class-ewp-dropdown.php';
  require 'classes/widgets/class-ewp-list.php';
  require 'classes/widgets/class-ewp-filter-by-publisher.php';
  require 'classes/shortcodes/class-ewp-product-carousel.php';
  require 'classes/shortcodes/class-ewp-carousel.php';
  require 'classes/shortcodes/class-ewp-all-publishers.php';
  require 'classes/shortcodes/class-ewp-az-listing.php';
  require 'classes/shortcodes/class-ewp-publisher.php';
  require 'classes/class-everexpert-woocommerce-publishers.php';
  require 'classes/class-ewp-api-support.php';
  new EWP_API_Support();
  require 'classes/admin/class-ewp-coupon.php';
  new Admin\EWP_Coupon();

  if (is_admin()) {
    require 'classes/admin/class-ewp-suggestions.php';
    new Admin\EWP_Suggestions();
    require 'classes/admin/class-ewp-notices.php';
    new Admin\EWP_Notices();
    require 'classes/admin/class-ewp-system-status.php';
    new Admin\EWP_System_Status();
    require 'classes/admin/class-ewp-admin-tab.php';
    require 'classes/admin/class-ewp-migrate.php';
    new Admin\EWP_Migrate();
    require 'classes/admin/class-ewp-dummy-data.php';
    new Admin\EWP_Dummy_Data();
    require 'classes/admin/class-edit-publishers-page.php';
    new Admin\Edit_Publishers_Page();
    require 'classes/admin/class-publishers-custom-fields.php';
    new Admin\Publishers_Custom_Fields();
    require 'classes/admin/class-publishers-exporter.php';
    new Admin\Publishers_Exporter();
    require 'classes/admin/class-ewp-importer-support.php';
    new EWP_Importer_Support();
    require 'classes/admin/class-ewp-exporter-support.php';
    new EWP_Exporter_Support();
  } else {
    include_once 'classes/class-ewp-product-tab.php';
    new EWP_Product_Tab();
  }

  new \Everexpert_Woocommerce_Publishers\Everexpert_Woocommerce_Publishers();
} elseif (is_admin()) {

  add_action('admin_notices', function() {
    $message = esc_html__('Everexpert WooCommerce Publishers needs WooCommerce to run. Please, install and active WooCommerce plugin.', 'everexpert-woocommerce-publishers');
    printf('<div class="%1$s"><p>%2$s</p></div>', 'notice notice-error', $message);
  });
}
