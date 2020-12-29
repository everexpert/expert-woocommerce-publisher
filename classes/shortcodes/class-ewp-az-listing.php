<?php
namespace Everexpert_Woocommerce_Publishers\Shortcodes;
use WP_Query;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class EWP_AZ_Listing_Shortcode {

  public static function shortcode( $atts ) {

    $grouped_publishers = get_transient('ewp_az_listing_cache');

    if ( ! $grouped_publishers ) {

      $atts = shortcode_atts( array(
        'only_parents' => false,
      ), $atts, 'ewp-az-listing' );

      $only_parents = filter_var( $atts['only_parents'], FILTER_VALIDATE_BOOLEAN );

      $publishers         = \Everexpert_Woocommerce_Publishers\Everexpert_Woocommerce_Publishers::get_publishers( true, 'name', 'ASC', false, false, $only_parents );
      $grouped_publishers = array();

      foreach ( $publishers as $publisher ) {

        if ( self::has_products( $publisher->term_id ) ) {

          $letter = mb_substr( htmlspecialchars_decode( $publisher->name ), 0, 1 );
          $letter = strtolower( $letter );
          $grouped_publishers[$letter][] = [ 'publisher_term' => $publisher ];

        }

      }

      set_transient( 'ewp_az_listing_cache', $grouped_publishers, 43200 );//12 hours

    }

    return \Everexpert_Woocommerce_Publishers\Everexpert_Woocommerce_Publishers::render_template(
      'az-listing',
      'shortcodes',
      array( 'grouped_publishers' => $grouped_publishers ),
      false
    );

  }

  private static function has_products( $publisher_id ){

    $args = array(
      'posts_per_page' => -1,
      'post_type'      => 'product',
      'tax_query'      => array(
        array(
          'taxonomy' => 'ewp-publisher',
          'field'    => 'term_id',
          'terms'    => array( $publisher_id )
        )
      ),
      'fields' => 'ids'
    );

    if( get_option('woocommerce_hide_out_of_stock_items') === 'yes' ){
      $args['meta_query'] = array(
        array(
          'key'     => '_stock_status',
          'value'   => 'outofstock',
          'compare' => 'NOT IN'
        )
      );
    }

    $wp_query = new WP_Query($args);
    wp_reset_postdata();
    return $wp_query->posts;

  }

}
