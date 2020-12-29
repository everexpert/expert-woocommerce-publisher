<?php
namespace Everexpert_Woocommerce_Publishers\Admin;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class Edit_Publishers_Page {

  private static $current_user;

  function __construct(){
    add_filter( 'get_terms', array( $this, 'publisher_list_admin_filter' ), 10, 3 );
    add_filter( 'manage_edit-ewp-publisher_columns', array( $this, 'publisher_taxonomy_columns_head' ) );
    add_filter( 'manage_ewp-publisher_custom_column', array( $this, 'publisher_taxonomy_columns' ), 10, 3 );
    add_action( 'wp_ajax_ewp_admin_set_featured_publisher', array( $this, 'set_featured_publisher' ) );
    add_filter( 'screen_settings', array( $this, 'add_screen_options' ), 10, 2 );
    add_action( 'wp_ajax_ewp_admin_save_screen_settings', array( $this, 'save_screen_options' ) );
    add_action( 'plugins_loaded', function(){ \Everexpert_Woocommerce_Publishers\Admin\Edit_Publishers_Page::$current_user = wp_get_current_user(); } );
    add_action( 'after-ewp-publisher-table', array( $this, 'add_publishers_count' ) );
  }

  private static function is_edit_publishers_page(){
    global $pagenow;
    return ( $pagenow == 'edit-tags.php' && isset( $_GET['taxonomy'] ) && $_GET['taxonomy'] == 'ewp-publisher' ) ? true : false;
  }

  public function add_publishers_count( $tax_name ){
    $publishers = get_terms(
      $tax_name,
      array( 'hide_empty' => false )
    );
    $publishers_featured = get_terms(
      $tax_name,
      array( 'hide_empty' => false, 'meta_query' => array( array( 'key' => 'ewp_featured_publisher', 'value' => true ) ) )
    );

    echo \Everexpert_Woocommerce_Publishers\Everexpert_Woocommerce_Publishers::render_template(
      'edit-publishers-bottom',
      'admin',
      array( 'featured_count' => count( $publishers_featured ), 'text_featured'  => esc_html__('featured', 'everexpert-woocommerce-publishers') )
    );

  }

  public function publisher_list_admin_filter( $publishers, $taxonomies, $args ) {

    if( self::is_edit_publishers_page() ){

      $featured = get_user_option( 'ewp-first-featured-publishers', self::$current_user->ID );
      if( $featured ){
        $featured_publishers = array();
        $other_publishers    = array();
        foreach( $publishers as $publisher ) {
          if( get_term_meta( $publisher->term_id, 'ewp_featured_publisher', true ) ){
            $featured_publishers[] = $publisher;
          }else{
            $other_publishers[] = $publisher;
          }
        }
        return array_merge( $featured_publishers, $other_publishers );
      }

    }
    return $publishers;

  }

  public function publisher_taxonomy_columns_head( $columns ){
    $new_columns = array();

    if ( isset( $columns['cb'] ) ) {
      $new_columns['cb'] = $columns['cb'];
      unset( $columns['cb'] );
    }

    if( isset( $columns['description'] ) ) unset( $columns['description'] );

    $new_columns['logo'] = __( 'Logo', 'everexpert-woocommerce-publishers' );
    $columns['featured'] = '<span class="ewp-featured-col-title">'.__( 'Featured', 'everexpert-woocommerce-publishers' ).'</span>';

    return array_merge( $new_columns, $columns );
  }

  public function publisher_taxonomy_columns($c, $column_name, $term_id){
    switch( $column_name ){
      case 'logo':
        $image = wp_get_attachment_image( get_term_meta( $term_id, 'ewp_publisher_image', 1 ), array('40','40') );
        return ( $image ) ? $image : wc_placeholder_img( array('40','40') );
        break;
      case 'featured':
        $featured_class = ( $this->is_featured_publisher( $term_id ) ) ? 'dashicons-star-filled' : 'dashicons-star-empty';
        printf(
          '<span class="dashicons %1$s" title="%2$s" data-publisher-id="%3$s"></span>',
          $featured_class, esc_html__('Set as featured', 'everexpert-woocommerce-publishers'), $term_id
        );
        break;
    }
  }

  private function is_featured_publisher( $publisher_id ){
    return ( get_term_meta( $publisher_id, 'ewp_featured_publisher', true ) );
  }

  public function set_featured_publisher(){
    if( isset( $_POST['publisher'] ) ){
      $direction = 'up';
      $publisher = intval( $_POST['publisher'] );
      if( $this->is_featured_publisher( $publisher ) ){
        delete_term_meta( $publisher, 'ewp_featured_publisher', true );
        $direction = 'down';
      }else{
        update_term_meta( $publisher, 'ewp_featured_publisher', true );
      }
      wp_send_json_success( array( 'success' => true, 'direction' => $direction ) );
    }else{
      wp_send_json_error( array( 'success' => false, 'error_msg' => __( 'Error!','everexpert-woocommerce-publishers' ) ) );
    }
    wp_die();
  }

  public function add_screen_options( $status, $args ){
    if( self::is_edit_publishers_page() ){
      $featured = get_user_option( 'ewp-first-featured-publishers', self::$current_user->ID );
      ob_start();
      ?>
      <legend><?php esc_html_e('Publishers','everexpert-woocommerce-publishers');?></legend>
      <label>
        <input id="ewp-first-featured-publishers" type="checkbox" <?php checked($featured,true);?>>
        <?php esc_html_e('Show featured publishers first','everexpert-woocommerce-publishers');?>
      </label>
      <?php
      return ob_get_clean();
    }
  }

  public function save_screen_options(){
    if( isset( $_POST['new_val'] ) ){
      $new_val = ( $_POST['new_val'] == 'true' ) ? true : false;
      update_user_option( self::$current_user->ID, 'ewp-first-featured-publishers', $new_val );
    }
    wp_die();
  }

}
