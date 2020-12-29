<?php
namespace Everexpert_Woocommerce_Publishers\Shortcodes;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class EWP_Carousel_Shortcode{

  private static $atts;

  public static function carousel_shortcode( $atts ) {

    self::$atts = shortcode_atts( array(
        'items'             => "10",
        'items_to_show'     => "5",
        'items_to_scroll'   => "1",
        'image_size'        => "thumbnail",
        'autoplay'          => "false",
        'arrows'            => "false",
        'hide_empty'        => false
    ), $atts, 'ewp-carousel' );

    //enqueue deps
    if( !wp_style_is('ewp-lib-slick') ) wp_enqueue_style('ewp-lib-slick');
    if( !wp_script_is('ewp-lib-slick') ) wp_enqueue_script('ewp-lib-slick');

    return \Everexpert_Woocommerce_Publishers\Everexpert_Woocommerce_Publishers::render_template(
      'carousel',
      'shortcodes',
      array( 'slick_settings' => self::slick_settings(), 'publishers' => self::publishers_data() ),
      false
    );

  }

  private static function slick_settings(){

    $slick_settings = array(
      'slidesToShow'   => (int)self::$atts['items_to_show'],
      'slidesToScroll' => (int)self::$atts['items_to_scroll'],
      'autoplay'       => ( self::$atts['autoplay'] === 'true' ) ? true: false,
      'arrows'         => ( self::$atts['arrows'] === 'true' ) ? true: false
    );
    return htmlspecialchars( json_encode( $slick_settings ), ENT_QUOTES, 'UTF-8' );

  }

  private static function publishers_data(){

    $publishers = array();
    $foreach_i = 0;
    if( self::$atts['items'] == 'featured' ){
      $publishers_array = \Everexpert_Woocommerce_Publishers\Everexpert_Woocommerce_Publishers::get_publishers( self::$atts['items'], 'name', 'ASC', true );
    }else{
      $publishers_array = \Everexpert_Woocommerce_Publishers\Everexpert_Woocommerce_Publishers::get_publishers( self::$atts['items'] );
    }
    foreach( $publishers_array as $publisher ){
        if( self::$atts['items'] != 'featured' && $foreach_i >= (int)self::$atts['items'] ) break;

        $publisher_id = $publisher->term_id;
        $publisher_link = get_term_link($publisher_id);
        $attachment_id = get_term_meta( $publisher_id, 'ewp_publisher_image', 1 );
        $attachment_html = $publisher->name;
        if($attachment_id!='') $attachment_html = wp_get_attachment_image( $attachment_id, self::$atts['image_size'] );

        $publishers[] = array( 'link' => $publisher_link, 'attachment_html' => $attachment_html, 'name' => $publisher->name );

        $foreach_i++;
    }

    return $publishers;

  }

}
