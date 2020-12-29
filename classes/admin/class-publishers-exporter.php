<?php
  namespace Everexpert_Woocommerce_Publishers\Admin;

  defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

  class Publishers_Exporter {

    function __construct(){
      add_action( 'after-ewp-publisher-table', array( $this, 'exporter_button' ) );
      add_action( 'wp_ajax_ewp_publishers_export', array( $this, 'export_publishers' ) );
      add_action( 'wp_ajax_ewp_publishers_import', array( $this, 'import_publishers' ) );
    }

    public function exporter_button(){
      echo \Everexpert_Woocommerce_Publishers\Everexpert_Woocommerce_Publishers::render_template(
        'publishers-exporter', 'admin', array( 'ok' => 'va' )
      );
    }

    public function export_publishers(){
      $this->get_publishers();
      wp_die();
    }

    private function get_publishers(){

      $publishers_data = array();

      $publishers = get_terms( 'ewp-publisher',array( 'hide_empty' => false ) );
      foreach( $publishers as $publisher ){

        $current_publisher = array(
          'slug'        =>  $publisher->slug,
          'name'        =>  $publisher->name,
          'banner_link' =>  get_term_meta( $publisher->term_id, 'ewp_publisher_banner_link', true ),
          'desc'        =>  htmlentities( $publisher->description )
        );

        $image = get_term_meta( $publisher->term_id, 'ewp_publisher_image', true );
        $image = wp_get_attachment_image_src( $image, 'full' );
        if( $image ) $current_publisher['image'] = $image[0];

        $banner = get_term_meta( $publisher->term_id, 'ewp_publisher_banner', true );
        $banner = wp_get_attachment_image_src( $banner, 'full' );
        if( $banner ) $current_publisher['banner'] = $banner[0];

        $publishers_data[] = $current_publisher;

      }

      $export_file = fopen( WP_CONTENT_DIR . '/uploads/ewp-export.json', 'w' );
      fwrite( $export_file, json_encode( $publishers_data ) );
      fclose( $export_file );

      $result = array( 'export_file_url' => WP_CONTENT_URL . '/uploads/ewp-export.json' );

      wp_send_json_success( $result );

    }

    public function import_publishers(){

      if( isset( $_FILES['file'] ) ){
        $file = $_FILES['file'];

        $file_content = json_decode( file_get_contents( $file['tmp_name'] ), true );

        if( is_array( $file_content ) ){

          foreach( $file_content as $publisher ){

            $new_publisher = wp_insert_term( $publisher['name'], 'ewp-publisher', array(
              'slug'        => $publisher['slug'],
              'description' => html_entity_decode( $publisher['desc'] )
            ));

            if( !is_wp_error( $new_publisher ) ){

              if( !empty( $publisher['image'] ) )
                $this->upload_remote_image_and_attach( $publisher['image'], $new_publisher['term_id'], 'ewp_publisher_image' );
              if( !empty( $publisher['banner'] ) )
                $this->upload_remote_image_and_attach( $publisher['banner'], $new_publisher['term_id'], 'ewp_publisher_banner' );
              if( !empty( $publisher['banner_link'] ) )
                update_term_meta( $new_publisher['term_id'], 'ewp_publisher_banner_link', $publisher['banner_link'], true );

            }

          }

          wp_send_json_success();

        }else{
          wp_send_json_error();
        }



      }else{
        wp_send_json_error();
      }

      wp_die();
    }

    private function upload_remote_image_and_attach( $image_url, $term_id, $meta_key ){

      $get  = wp_remote_get( $image_url );
      $type = wp_remote_retrieve_header( $get, 'content-type' );

      if( !$type ) return false;

      $mirror = wp_upload_bits( basename( $image_url ), '', wp_remote_retrieve_body( $get ) );

      $attachment = array(
        'post_title'     => basename( $image_url ),
        'post_mime_type' => $type
      );

      $attach_id = wp_insert_attachment( $attachment, $mirror['file'] );
      require_once ABSPATH . 'wp-admin/includes/image.php';
      $attach_data = wp_generate_attachment_metadata( $attach_id, $mirror['file'] );
      wp_update_attachment_metadata( $attach_id, $attach_data );

      update_term_meta( $term_id, $meta_key, $attach_id, true );

    }

  }
