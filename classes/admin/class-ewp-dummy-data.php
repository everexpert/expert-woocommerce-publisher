<?php
  namespace Everexpert_Woocommerce_Publishers\Admin;

  defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

  class EWP_Dummy_Data {

    function __construct(){
      add_action( 'wp_ajax_ewp_admin_dummy_data', array( $this, 'dummy_data' ) );
    }

    private static function get_attachment_id_from_src($image_src){
      global $wpdb;
      $query = "SELECT ID FROM {$wpdb->posts} WHERE guid='$image_src'";
      $id = $wpdb->get_var($query);
      return $id;
    }

    private static function retrieve_img_src( $img ) {
      if (preg_match('/<img(\s+?)([^>]*?)src=(\"|\')([^>\\3]*?)\\3([^>]*?)>/is', $img, $m) && isset($m[4]))
        return $m[4];
      return false;
    }

    private static function upload_image( $post_id, $img_url ){

      require_once ABSPATH . "wp-admin" . '/includes/image.php';
      require_once ABSPATH . "wp-admin" . '/includes/file.php';
      require_once ABSPATH . "wp-admin" . '/includes/media.php';

      //solves media_sideload_image bug with spaces in filenames
      $parsed_file = parse_url($img_url);
      $path = $parsed_file['path'];
      $file_name = basename($path);
      $encoded_file_name = rawurlencode($file_name);
      $path = str_replace($file_name, $encoded_file_name, $path);
      $img_url = $parsed_file['scheme'] . "://" . $parsed_file['host'] . $path;
      $image = '';

      preg_match('/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $img_url, $file_matches);
      if(isset($file_matches[0])){
         $image = media_sideload_image($img_url, $post_id );
      }

      //media_sideload_image returns a html image
      //extract the src value for get the attachment id
      $image_src = self::retrieve_img_src( $image );
      return self::get_attachment_id_from_src( $image_src );

    }

    private function build_description(){
      $desc = 'lorem ipsum dolor <strong>sit</strong> amet consectetur adipiscing elit etiam mollis faucibus aliquet';
      $desc.= 'sed risus turpis dapibus vel <strong>rhoncus</strong> a vestibulum sed lectus in hac habitasse platea dictumst';
      $desc.= 'suspendisse non luctus felis <strong>morbi</strong> id volutpat ligula quisque rutrum arcu at erat lobortis';
      $exploded_desc = explode( ' ', $desc );
      shuffle( $exploded_desc );
      $desc = implode( ' ', $exploded_desc );
      return ucfirst( $desc );
    }

    public function dummy_data(){

      for( $i=1; $i<11; $i++ ) {
        $term_desc = $this->build_description();
        $publisher_name = 'publisher'.$i;
        $attachment_id = self::upload_image( false, EWP_PLUGIN_URL . '/assets/img/dummy-data/'.$publisher_name.'.png' );
        $inserted_publisher = wp_insert_term( ucfirst( $publisher_name ), 'ewp-publisher', array( "description" => $term_desc ) );
        if( !is_wp_error( $inserted_publisher ) && isset( $inserted_publisher['term_id'] ) ){
          add_term_meta( $inserted_publisher['term_id'], 'ewp_publisher_image', $attachment_id );
        }
      }

      $this->set_publishers_randomly();

      wp_die();

    }

    public function set_publishers_randomly(){

      $publishers = \Everexpert_Woocommerce_Publishers\Everexpert_Woocommerce_Publishers::get_publishers_array();

      $the_query = new \WP_Query( array( 'posts_per_page' => -1 , 'post_type' => 'product' ) );

      while ( $the_query->have_posts() ) {
      	$the_query->the_post();
        wp_set_object_terms( get_the_ID(), array_rand( $publishers ), 'ewp-publisher' );
      }
      wp_reset_postdata();

    }

  }
