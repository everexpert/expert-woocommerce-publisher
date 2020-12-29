<?php
namespace Everexpert_Woocommerce_Publishers\Shortcodes;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class EWP_Publisher_Shortcode{

  public static function publisher_shortcode( $atts ) {
    $atts = shortcode_atts( array(
      'product_id' => null,
      'as_link'    => false,
      'image_size' => 'thumbnail',
    ), $atts, 'ewp-publisher' );

    if( !$atts['product_id'] && is_singular('product') ) $atts['product_id'] = get_the_ID();

    $publishers = wp_get_post_terms( $atts['product_id'], 'ewp-publisher');

    foreach( $publishers as $key => $publisher ){
      $publishers[$key]->term_link  = get_term_link ( $publisher->term_id, 'ewp-publisher' );
      $publishers[$key]->image = wp_get_attachment_image( get_term_meta( $publisher->term_id, 'ewp_publisher_image', 1 ), $atts['image_size'] );
    }

    return \Everexpert_Woocommerce_Publishers\Everexpert_Woocommerce_Publishers::render_template(
      'publisher',
      'shortcodes',
      array( 'publishers' => $publishers, 'as_link' => $atts['as_link'] ),
      false
    );

  }

}
