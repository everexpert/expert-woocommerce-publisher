<?php
  namespace Everexpert_Woocommerce_Publishers\Admin;

  defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

  class EWP_Coupon{

    function __construct(){
      add_action( 'woocommerce_coupon_options_usage_restriction', array( $this, 'coupon_restriction' ) );
      add_action( 'woocommerce_coupon_options_save',  array( $this, 'coupon_save' ) );
      add_filter( 'woocommerce_coupon_is_valid', array( $this, 'is_valid_coupon' ), 10, 2 );
      add_filter( 'woocommerce_coupon_is_valid_for_product', array( $this, 'is_valid_for_product_publisher' ), 10, 4 );
    }

    public function coupon_restriction() {
        global $thepostid, $post;
        $thepostid = empty( $thepostid ) ? $post->get_ID() : $thepostid;

        $selected_publishers = get_post_meta( $thepostid, '_ewp_coupon_restriction', true );
        if( $selected_publishers == '' ) $selected_publishers = array();

        ob_start();
        ?>
        <p class="form-field"><label for="_ewp_coupon_restriction"><?php _e( 'Publishers restriction', 'everexpert-woocommerce-publishers' ); ?></label>
				<select id="_ewp_coupon_restriction" name="_ewp_coupon_restriction[]" style="width: 50%;"  class="wc-enhanced-select" multiple="multiple" data-placeholder="<?php esc_attr_e( 'Any publisher', 'everexpert-woocommerce-publishers' ); ?>">
					<?php
						$categories   = get_terms( 'ewp-publisher', 'orderby=name&hide_empty=0' );
						if ( $categories ) {
							foreach ( $categories as $cat ) {
								echo '<option value="' . esc_attr( $cat->term_id ) . '"' . selected( in_array( $cat->term_id, $selected_publishers ), true, false ) . '>' . esc_html( $cat->name ) . '</option>';
							}
						}
					?>
				</select> <?php echo wc_help_tip( __( 'Coupon will be valid if there are at least one product of this publishers in the cart', 'everexpert-woocommerce-publishers' ) ); ?></p>
        <?php
        echo ob_get_clean();

    }

    public function coupon_save( $post_id ){
      $_ewp_coupon_restriction = isset( $_POST['_ewp_coupon_restriction'] ) ? $_POST['_ewp_coupon_restriction'] : '';
      update_post_meta( $post_id, '_ewp_coupon_restriction', $_ewp_coupon_restriction );
    }

    public function is_valid_coupon( $availability, $coupon ){
      $selected_publishers = get_post_meta( $coupon->get_ID(), '_ewp_coupon_restriction', true );
      if( !empty( $selected_publishers ) ){
        global $woocommerce;
        $products = $woocommerce->cart->get_cart();
        foreach( $products as $product ) {
          $product_publishers = wp_get_post_terms( $product['product_id'], 'ewp-publisher', array( 'fields' => 'ids' ) );
          $valid_publishers = array_intersect( $selected_publishers, $product_publishers );
          if( !empty( $valid_publishers ) ) return true;
        }
        return false;
      }
      return true;
    }

    public function is_valid_for_product_publisher( $valid, $product, $coupon, $values ){
      if ( !$valid ) return false;

      $coupon_id = is_callable( array( $coupon, 'get_id' ) ) ?  $coupon->get_id() : $coupon->id;
      $selected_publishers = get_post_meta( $coupon_id, '_ewp_coupon_restriction', true );
      if ( empty( $selected_publishers ) ) return $valid;

      if( $product->is_type( 'variation' ) ){
        $product_id = $product->get_parent_id();
      }else{
        $product_id = is_callable( array( $product, 'get_id' ) ) ?  $product->get_id() : $product->id;
      }
      $product_publishers = wp_get_post_terms( $product_id, 'ewp-publisher', array( 'fields' => 'ids' ) );
      $valid_publishers = array_intersect( $selected_publishers, $product_publishers );
      return !empty( $valid_publishers );
    }

  }
