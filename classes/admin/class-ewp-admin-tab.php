<?php

namespace Everexpert_Woocommerce_Publishers\Admin;

use WC_Admin_Settings,
  WC_Settings_Page;

defined('ABSPATH') or die('No script kiddies please!');

class Pwb_Admin_Tab
{

  public function __construct()
  {

    $this->id = 'ewp_admin_tab';
    $this->label = __('Publishers', 'everexpert-woocommerce-publishers');

    add_filter('woocommerce_settings_tabs_array', [$this, 'add_tab'], 200);
    add_action('woocommerce_settings_' . $this->id, [$this, 'output']);
    add_action('woocommerce_sections_' . $this->id, [$this, 'output_sections']);
    add_action('woocommerce_settings_save_' . $this->id, [$this, 'save']);
  }

  public function add_tab($settings_tabs)
  {

    $settings_tabs[$this->id] = $this->label;

    return $settings_tabs;
  }

  public function get_sections()
  {

    $sections = array(
      '' => __('General', 'everexpert-woocommerce-publishers'),
      'publisher-pages' => __('Archives', 'everexpert-woocommerce-publishers'),
      'single-product' => __('Products', 'everexpert-woocommerce-publishers'),
      'tools' => __('Tools', 'everexpert-woocommerce-publishers'),
    );

    return apply_filters('woocommerce_get_sections_' . $this->id, $sections);
  }

  public function output_sections()
  {
    global $current_section;

    $sections = $this->get_sections();

    if (empty($sections) || 1 === sizeof($sections)) {
      return;
    }

    echo '<ul class="subsubsub">';

    $array_keys = array_keys($sections);

    foreach ($sections as $id => $label) {
      echo '<li><a href="' . admin_url('admin.php?page=wc-settings&tab=' . $this->id . '&section=' . sanitize_title($id)) . '" class="' . ($current_section == $id ? 'current' : '') . '">' . $label . '</a> ' . (end($array_keys) == $id ? '' : '|') . ' </li>';
    }

    echo ' | <li><a target="_blank" href="' . admin_url('edit-tags.php?taxonomy=ewp-publisher&post_type=product') . '">' . __('Publishers', 'everexpert-woocommerce-publishers') . '</a></li>';
    echo ' | <li><a target="_blank" href="' . admin_url('admin.php?page=ewp_suggestions') . '">' . __('Suggestions', 'everexpert-woocommerce-publishers') . '</a></li>';
    echo ' | <li><a target="_blank" href="' . EWP_DOCUMENTATION_URL . '">' . __('Documentation', 'everexpert-woocommerce-publishers') . '</a></li>';

    echo '</ul><br class="clear" />';
  }

  public function get_settings($current_section = '')
  {

    $available_image_sizes_adapted = array();
    $available_image_sizes = get_intermediate_image_sizes();
    foreach ($available_image_sizes as $image_size)
      $available_image_sizes_adapted[$image_size] = $image_size;
    $available_image_sizes_adapted['full'] = 'full';

    $pages_select_adapted = array('-' => '-');
    $pages_select = get_pages();
    foreach ($pages_select as $page)
      $pages_select_adapted[$page->ID] = $page->post_title;

    if ('single-product' == $current_section) {

      $settings = apply_filters('wc_ewp_admin_tab_settings', array(
        'section_title' => array(
          'name' => __('Products', 'everexpert-woocommerce-publishers'),
          'type' => 'title',
          'desc' => '',
          'id' => 'wc_ewp_admin_tab_section_title'
        ),
        'publisher_single_product_tab' => array(
          'name' => __('Products tab', 'everexpert-woocommerce-publishers'),
          'type' => 'checkbox',
          'default' => 'yes',
          'desc' => __('Show publisher tab in single product page', 'everexpert-woocommerce-publishers'),
          'id' => 'wc_ewp_admin_tab_publisher_single_product_tab'
        ),
        'show_publisher_in_single' => array(
          'name' => __('Show publishers in single product', 'everexpert-woocommerce-publishers'),
          'type' => 'select',
          'class' => 'ewp-admin-tab-field',
          'desc' => __('Show publisher logo (or name) in single product', 'everexpert-woocommerce-publishers'),
          'default' => 'publisher_image',
          'id' => 'wc_ewp_admin_tab_publishers_in_single',
          'options' => array(
            'no' => __('No', 'everexpert-woocommerce-publishers'),
            'publisher_link' => __('Show publisher link', 'everexpert-woocommerce-publishers'),
            'publisher_image' => __('Show publisher image (if is set)', 'everexpert-woocommerce-publishers')
          )
        ),
        'publisher_single_position' => array(
          'name' => __('Publisher position', 'everexpert-woocommerce-publishers'),
          'type' => 'select',
          'class' => 'ewp-admin-tab-field',
          'desc' => __('For single product', 'everexpert-woocommerce-publishers'),
          'id' => 'wc_ewp_admin_tab_publisher_single_position',
          'options' => array(
            'before_title' => __('Before title', 'everexpert-woocommerce-publishers'),
            'after_title' => __('After title', 'everexpert-woocommerce-publishers'),
            'after_price' => __('After price', 'everexpert-woocommerce-publishers'),
            'after_excerpt' => __('After excerpt', 'everexpert-woocommerce-publishers'),
            'after_add_to_cart' => __('After add to cart', 'everexpert-woocommerce-publishers'),
            'meta' => __('In meta', 'everexpert-woocommerce-publishers'),
            'after_meta' => __('After meta', 'everexpert-woocommerce-publishers'),
            'after_sharing' => __('After sharing', 'everexpert-woocommerce-publishers')
          )
        ),
        'section_end' => array(
          'type' => 'sectionend',
          'id' => 'wc_ewp_admin_tab_section_end'
        )
      ));
    } elseif ('publisher-pages' == $current_section) {

      $settings = apply_filters('wc_ewp_admin_tab_publisher_pages_settings', array(
        'section_title' => array(
          'name' => __('Archives', 'everexpert-woocommerce-publishers'),
          'type' => 'title',
          'desc' => '',
          'id' => 'wc_ewp_admin_tab_section_title'
        ),
        'publisher_description' => array(
          'name' => __('Show publisher description', 'everexpert-woocommerce-publishers'),
          'type' => 'select',
          'class' => 'ewp-admin-tab-field',
          'default' => 'yes',
          'desc' => __('Show publisher description (if is set) on publisher archive page', 'everexpert-woocommerce-publishers'),
          'id' => 'wc_ewp_admin_tab_publisher_desc',
          'options' => array(
            'yes' => __('Yes, before product loop', 'everexpert-woocommerce-publishers'),
            'yes_after_loop' => __('Yes, after product loop', 'everexpert-woocommerce-publishers'),
            'no' => __('No, hide description', 'everexpert-woocommerce-publishers')
          )
        ),
        'publisher_banner' => array(
          'name' => __('Show publisher banner', 'everexpert-woocommerce-publishers'),
          'type' => 'select',
          'class' => 'ewp-admin-tab-field',
          'default' => 'yes',
          'desc' => __('Show publisher banner (if is set) on publisher archive page', 'everexpert-woocommerce-publishers'),
          'id' => 'wc_ewp_admin_tab_publisher_banner',
          'options' => array(
            'yes' => __('Yes, before product loop', 'everexpert-woocommerce-publishers'),
            'yes_after_loop' => __('Yes, after product loop', 'everexpert-woocommerce-publishers'),
            'no' => __('No, hide banner', 'everexpert-woocommerce-publishers')
          )
        ),
        'show_publisher_on_loop' => array(
          'name' => __('Show publishers in loop', 'everexpert-woocommerce-publishers'),
          'type' => 'select',
          'class' => 'ewp-admin-tab-field',
          'desc' => __('Show publisher logo (or name) in product loop', 'everexpert-woocommerce-publishers'),
          'id' => 'wc_ewp_admin_tab_publishers_in_loop',
          'options' => array(
            'no' => __('No', 'everexpert-woocommerce-publishers'),
            'publisher_link' => __('Show publisher link', 'everexpert-woocommerce-publishers'),
            'publisher_image' => __('Show publisher image (if is set)', 'everexpert-woocommerce-publishers')
          )
        ),
        'section_end' => array(
          'type' => 'sectionend',
          'id' => 'wc_ewp_admin_tab_section_end'
        )
      ));
    } elseif ('tools' == $current_section) {

      $settings = apply_filters('wc_ewp_admin_tab_tools_settings', array(
        'section_title' => array(
          'name' => __('Tools', 'everexpert-woocommerce-publishers'),
          'type' => 'title',
          'desc' => '',
          'id' => 'wc_ewp_admin_tab_section_tools_title'
        ),
        'publisher_import' => array(
          'name' => __('Import publishers', 'everexpert-woocommerce-publishers'),
          'type' => 'select',
          'class' => 'ewp-admin-tab-field',
          'desc' => sprintf(
            __('Import publishers from other publisher plugin. <a href="%s" target="_blank">Click here for more details</a>', 'everexpert-woocommerce-publishers'),
            str_replace('/?', '/publishers/?', EWP_DOCUMENTATION_URL)
          ),
          'id' => 'wc_ewp_admin_tab_tools_migrate',
          'options' => array(
            '-' => __('-', 'everexpert-woocommerce-publishers'),
            'yith' => __('YITH WooCommerce Publishers Add-On', 'everexpert-woocommerce-publishers'),
            'ultimate' => __('Ultimate WooCommerce Publishers', 'everexpert-woocommerce-publishers'),
            'woopublishers' => __('Offical WooCommerce Publishers', 'everexpert-woocommerce-publishers')
          )
        ),
        'publisher_dummy_data' => array(
          'name' => __('Dummy data', 'everexpert-woocommerce-publishers'),
          'type' => 'select',
          'class' => 'ewp-admin-tab-field',
          'desc' => __('Import generic publishers and assign it to products randomly', 'everexpert-woocommerce-publishers'),
          'id' => 'wc_ewp_admin_tab_tools_dummy_data',
          'options' => array(
            '-' => __('-', 'everexpert-woocommerce-publishers'),
            'start_import' => __('Start import', 'everexpert-woocommerce-publishers')
          )
        ),
        'publishers_system_status' => array(
          'name' => __('System status', 'everexpert-woocommerce-publishers'),
          'type' => 'textarea',
          'desc' => __('Show system status', 'everexpert-woocommerce-publishers'),
          'id' => 'wc_ewp_admin_tab_tools_system_status'
        ),
        'section_end' => array(
          'type' => 'sectionend',
          'id' => 'wc_ewp_admin_tab_section_tools_end'
        )
      ));
    } else {

      $publishers_url = get_option('wc_ewp_admin_tab_slug', __('publishers', 'everexpert-woocommerce-publishers')) . '/' . __('publisher-name', 'everexpert-woocommerce-publishers') . '/';

      $settings = apply_filters('wc_ewp_admin_tab_product_settings', array(
        'section_title' => array(
          'name' => __('General', 'everexpert-woocommerce-publishers'),
          'type' => 'title',
          'desc' => '',
          'id' => 'wc_ewp_admin_tab_section_title'
        ),
        'slug' => array(
          'name' => __('Slug', 'everexpert-woocommerce-publishers'),
          'type' => 'text',
          'class' => 'ewp-admin-tab-field',
          'desc' => __('Publishers taxonomy slug', 'everexpert-woocommerce-publishers'),
          'desc_tip' => sprintf(
            __('Your publishers URLs will look like "%s"', 'everexpert-woocommerce-publishers'),
            'https://site.com/' . $publishers_url
          ),
          'id' => 'wc_ewp_admin_tab_slug',
          'placeholder' => get_taxonomy('ewp-publisher')->rewrite['slug']
        ),
        'publisher_logo_size' => array(
          'name' => __('Publisher logo size', 'everexpert-woocommerce-publishers'),
          'type' => 'select',
          'class' => 'ewp-admin-tab-field',
          'desc' => __('Select the size for the publisher logo image around the site', 'everexpert-woocommerce-publishers'),
          'desc_tip' => __('The default image sizes can be configured under "Settings > Media". You can also define your own image sizes', 'everexpert-woocommerce-publishers'),
          'id' => 'wc_ewp_admin_tab_publisher_logo_size',
          'options' => $available_image_sizes_adapted
        ),
        'publishers_page_id' => array(
          'name' => __('Publishers page', 'everexpert-woocommerce-publishers'),
          'type' => 'select',
          'class' => 'ewp-admin-tab-field ewp-admin-selectwoo',
          'desc' => __('For linking breadcrumbs', 'everexpert-woocommerce-publishers'),
          'desc_tip' => __('Select your "Publishers" page (if you have one), it will be linked in the breadcrumbs.', 'everexpert-woocommerce-publishers'),
          'id' => 'wc_ewp_admin_tab_publishers_page_id',
          'options' => $pages_select_adapted
        ),
        'section_end' => array(
          'type' => 'sectionend',
          'id' => 'wc_ewp_admin_tab_section_end'
        )
      ));
    }

    return apply_filters('woocommerce_get_settings_' . $this->id, $settings, $current_section);
  }

  public function output()
  {

    global $current_section;

    $settings = $this->get_settings($current_section);
    WC_Admin_Settings::output_fields($settings);
  }

  public function save()
  {

    update_option('old_wc_ewp_admin_tab_slug', get_taxonomy('ewp-publisher')->rewrite['slug']);
    if (isset($_POST['wc_ewp_admin_tab_slug'])) {
      $_POST['wc_ewp_admin_tab_slug'] = sanitize_title($_POST['wc_ewp_admin_tab_slug']);
    }

    global $current_section;

    $settings = $this->get_settings($current_section);
    WC_Admin_Settings::save_fields($settings);
  }
}

return new Pwb_Admin_Tab();
