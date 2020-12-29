<?php

namespace Everexpert_Woocommerce_Publishers;

defined('ABSPATH') or die('No script kiddies please!');

class EWP_Importer_Support{

    function __construct(){
      add_filter( 'woocommerce_csv_product_import_mapping_options', array( $this, 'add_column_to_importer' ) );
      add_filter( 'woocommerce_csv_product_import_mapping_default_columns', array( $this, 'add_column_to_mapping_screen' ) );
      add_action( 'woocommerce_product_import_inserted_product_object', array( $this, 'process_import' ), 10, 2 );
    }

    /**
     * Register the 'Custom Column' column in the importer.
     *
     * @param array $options
     * @return array $options
     */
    public function add_column_to_importer( $options ) {
      $options['ewp-publisher'] = esc_html__('Publisher', 'everexpert-woocommerce-publishers');
    	return $options;
    }

    /**
     * Add automatic mapping support for 'Custom Column'.
     *
     * @param array $columns
     * @return array $columns
     */
    public function add_column_to_mapping_screen( $columns ) {
      $columns[esc_html__('Publisher', 'everexpert-woocommerce-publishers')] = 'ewp-publisher';
    	return $columns;
    }

    /**
     * Process the data read from the CSV file.
     *
     * @param WC_Product $object - Product being imported or updated.
     * @param array $data - CSV data read for the product.
     * @return WC_Product $object
     */
    public function process_import( $object, $data ) {
      if( isset( $data['ewp-publisher'] ) ){
        wp_delete_object_term_relationships( $object->get_id(), 'ewp-publisher' );
        $publishers = explode( ',', $data['ewp-publisher'] );
        foreach( $publishers as $publisher ) wp_set_object_terms( $object->get_id(), $publisher, 'ewp-publisher', true );
      }
    	return $object;
    }

}
