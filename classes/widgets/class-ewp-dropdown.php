<?php
namespace Everexpert_Woocommerce_Publishers\Widgets;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class EWP_Dropdown_Widget extends \WP_Widget {

  function __construct(){
    $params = array(
      'description' => __( 'Adds a publishers dropdown to your site', 'everexpert-woocommerce-publishers' ),
      'name'        => __( 'Publishers dropdown', 'everexpert-woocommerce-publishers' )
    );
    parent::__construct('EWP_Dropdown_Widget', '', $params);
  }

  public function form($instance){
    extract($instance);

    $title = ( isset( $instance[ 'title' ] ) ) ? $instance[ 'title' ] : esc_html__('Publishers', 'everexpert-woocommerce-publishers');
    $hide_empty = ( isset( $hide_empty ) && $hide_empty == 'on' ) ? true : false;
    $only_featured = ( isset( $only_featured ) && $only_featured == 'on' ) ? true : false;
    ?>

    <p>
      <label for="<?php echo esc_attr( $this->get_field_id('title') ); ?>">
        <?php echo __( 'Title', 'everexpert-woocommerce-publishers' );?>
      </label>
      <input
      class="widefat"
      type="text"
      id="<?php echo esc_attr( $this->get_field_id('title') ); ?>"
      name="<?php echo esc_attr( $this->get_field_name('title') ); ?>"
      value="<?php if(isset($title)) echo esc_attr($title); ?>">
    </p>

    <p>
      <input
      type="checkbox"
      id="<?php echo esc_attr( $this->get_field_id('hide_empty') ); ?>"
      name="<?php echo esc_attr( $this->get_field_name('hide_empty') ); ?>"
      <?php checked( $hide_empty ); ?>>
      <label for="<?php echo esc_attr( $this->get_field_id('hide_empty') ); ?>">
        <?php echo __( 'Hide empty', 'everexpert-woocommerce-publishers' );?>
      </label>
    </p>

    <p>
      <input
      type="checkbox"
      id="<?php echo esc_attr( $this->get_field_id('only_featured') ); ?>"
      name="<?php echo esc_attr( $this->get_field_name('only_featured') ); ?>"
      <?php checked( $only_featured ); ?>>
      <label for="<?php echo esc_attr( $this->get_field_id('only_featured') ); ?>">
        <?php echo __( 'Only favorite publishers', 'everexpert-woocommerce-publishers' );?>
      </label>
    </p>

    <?php
  }

  public function widget( $args, $instance ){
    extract($args);
    extract($instance);

    $queried_obj = get_queried_object();
    $queried_publisher_id = ( isset( $queried_obj->term_id ) ) ? $queried_obj->term_id : false;

    $hide_empty = ( isset( $hide_empty ) && $hide_empty == 'on' ) ? true : false;
    $only_featured = ( isset( $only_featured ) && $only_featured == 'on' ) ? true : false;
    $publishers = \Everexpert_Woocommerce_Publishers\Everexpert_Woocommerce_Publishers::get_publishers(
      $hide_empty, 'name', 'ASC', $only_featured, true
    );

    if( is_array( $publishers ) && count( $publishers ) > 0 ){

      echo $before_widget;

        if( !empty( $title ) ) echo $before_title . $title . $after_title;

        echo \Everexpert_Woocommerce_Publishers\Everexpert_Woocommerce_Publishers::render_template(
          'dropdown',
          'widgets',
          array( 'publishers' => $publishers, 'selected' => $queried_publisher_id ),
          false
        );

      echo $after_widget;

    }

  }

}
