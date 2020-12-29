<?php
  namespace Everexpert_Woocommerce_Publishers\Admin;

  defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

  class EWP_System_Status{

    function __construct(){
      add_action( 'wp_ajax_ewp_system_status', array( $this, 'ewp_system_status' ) );
    }

    public function ewp_system_status(){
      print_r(array(
        'home_url'                  => get_option( 'home' ),
        'site_url'                  => get_option( 'siteurl' ),
        'version'                   => WC()->version,
        'wp_version'                => get_bloginfo( 'version' ),
        'wp_multisite'              => is_multisite(),
        'wp_memory_limit'           => WP_MEMORY_LIMIT,
        'wp_debug_mode'             => ( defined( 'WP_DEBUG' ) && WP_DEBUG ),
        'wp_cron'                   => !( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ),
        'language'                  => get_locale(),
        'server_info'               => $_SERVER['SERVER_SOFTWARE'],
        'php_version'               => phpversion(),
        'php_post_max_size'         => ini_get( 'post_max_size' ),
        'php_max_execution_time'    => ini_get( 'max_execution_time' ),
        'php_max_input_vars'        => ini_get( 'max_input_vars' ),
        'max_upload_size'           => wp_max_upload_size(),
        'default_timezone'          => date_default_timezone_get(),
        'theme'                     => $this->theme_info(),
        'active_plugins'            => get_option( 'active_plugins' ),
        'ewp_options'               => $this->ewp_options()
      ));
      wp_die();
    }

    private function theme_info(){
      $current_theme = wp_get_theme();
      return array(
        'name'          => $current_theme->__get('name'),
        'version'       => $current_theme->__get('version'),
        'parent_theme'  => $current_theme->__get('parent_theme')
      );
    }

    private function ewp_options(){
      return array(
        'version'                                   => EWP_PLUGIN_VERSION,
        'wc_ewp_admin_tab_publisher_single_position'    => get_option( 'wc_ewp_admin_tab_publisher_single_position' ),
        'old_wc_ewp_admin_tab_slug'                 => get_option( 'old_wc_ewp_admin_tab_slug' ),
        'wc_ewp_notice_plugin_review'               => get_option( 'wc_ewp_notice_plugin_review' ),
        'wc_ewp_admin_tab_slug'                     => get_option( 'wc_ewp_admin_tab_slug' ),
        'wc_ewp_admin_tab_publisher_desc'               => get_option( 'wc_ewp_admin_tab_publisher_desc' ),
        'wc_ewp_admin_tab_publisher_single_product_tab' => get_option( 'wc_ewp_admin_tab_publisher_single_product_tab' ),
        'wc_ewp_admin_tab_publishers_in_loop'           => get_option( 'wc_ewp_admin_tab_publishers_in_loop' ),
        'wc_ewp_admin_tab_publishers_in_single'         => get_option( 'wc_ewp_admin_tab_publishers_in_single' ),
        'wc_ewp_admin_tab_publisher_logo_size'          => get_option( 'wc_ewp_admin_tab_publisher_logo_size' )
      );
    }

  }
