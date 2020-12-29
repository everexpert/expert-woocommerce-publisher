<?php

namespace Everexpert_Woocommerce_Publishers;

defined('ABSPATH') or die('No script kiddies please!');

class EWP_Exporter_Support{

    function __construct(){
      add_filter( 'woocommerce_product_export_column_names', array( $this, 'add_export_column' ) );
      add_filter( 'woocommerce_product_export_product_default_columns',  array( $this, 'add_export_column' ) );
      add_filter( 'woocommerce_product_export_product_column_ewp-publisher', array( $this, 'add_export_data' ), 10, 2 );
    }

    /**
     * Add the custom column to the exporter and the exporter column menu.
     *
     * @param array $columns
     * @return array $columns
     */
    public function add_export_column( $columns ) {
    	$columns['ewp-publisher'] = esc_html__('Publisher', 'everexpert-woocommerce-publishers');
    	return $columns;
    }

    /**
     * Provide the data to be exported for one item in the column.
     *
     * @param mixed $value (default: '')
     * @param WC_Product $product
     * @return mixed $value - Should be in a format that can be output into a text file (string, numeric, etc).
     */
    public function add_export_data( $value, $product ) {
      $publishers = wp_get_post_terms( $product->get_id(), 'ewp-publisher' );
      $publisher_names = array();
      foreach( $publishers as $publisher ) $publisher_names[] = $publisher->name;
    	return implode( ',', $publisher_names );
    }

}
