<?php
  namespace Everexpert_Woocommerce_Publishers;

  defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

  delete_option('wc_ewp_admin_tab_section_title');
  delete_option('wc_ewp_admin_tab_slug');
  delete_option('wc_ewp_admin_tab_publisher_logo_size');
  delete_option('wc_ewp_admin_tab_publisher_single_position');
  delete_option('wc_ewp_admin_tab_publisher_single_product_tab');
  delete_option('wc_ewp_admin_tab_publisher_desc');
  delete_option('wc_ewp_admin_tab_section_end');
  delete_option('wc_ewp_notice_plugin_review');

  //remove exported publishers if exists
  unlink( WP_CONTENT_DIR . '/uploads/ewp-export.json' );

  //update permalinks and clean cache
  flush_rewrite_rules();
  wp_cache_flush();
