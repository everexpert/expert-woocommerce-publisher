<?php
namespace Everexpert_Woocommerce_Publishers\Widgets;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class EWP_List_Widget extends \WP_Widget {

  function __construct(){
    $params = array(
      'description' => __( 'Adds a publishers list to your site', 'everexpert-woocommerce-publishers' ),
      'name'        => __( 'Publishers list', 'everexpert-woocommerce-publishers' )
    );
    parent::__construct('EWP_List_Widget', '', $params);
  }

  public function form($instance){
    extract($instance);

    $title = ( isset( $instance[ 'title' ] ) ) ? $instance[ 'title' ] : esc_html__('Publishers', 'everexpert-woocommerce-publishers');
    if( !isset( $display_as ) ) $display_as = 'publisher_logo';
    if( !isset( $columns ) ) $columns = '2';
    $hide_empty = ( isset( $hide_empty ) && $hide_empty == 'on' ) ? true : false;
    $only_featured = ( isset( $only_featured ) && $only_featured == 'on' ) ? true : false;
    $randomize = ( isset( $randomize ) && $randomize == 'on' ) ? true : false;
    ?>

    <p>
      <label for="<?php echo esc_attr( $this->get_field_id('title') ); ?>"><?php echo __( 'Title:', 'everexpert-woocommerce-publishers' );?></label>
      <input
      class="widefat"
      type="text"
      id="<?php echo esc_attr( $this->get_field_id('title') ); ?>"
      name="<?php echo esc_attr( $this->get_field_name('title') ); ?>"
      value="<?php if(isset($title)) echo esc_attr($title); ?>">
    </p>
    <p>
      <label for="<?php echo esc_attr( $this->get_field_id('display_as') ); ?>"><?php echo __( 'Display as:', 'everexpert-woocommerce-publishers' );?></label>
      <select
        class="widefat ewp-select-display-as"
        id="<?php echo esc_attr( $this->get_field_id('display_as') ); ?>"
        name="<?php echo esc_attr( $this->get_field_name('display_as') ); ?>">
        <option value="publisher_name" <?php selected( $display_as, 'publisher_name' ); ?>><?php _e( 'Publisher name', 'everexpert-woocommerce-publishers' );?></option>
        <option value="publisher_logo" <?php selected( $display_as, 'publisher_logo' ); ?>><?php _e( 'Publisher logo', 'everexpert-woocommerce-publishers' );?></option>
      </select>
    </p>
    <p class="ewp-display-as-logo<?php echo ($display_as=='publisher_logo') ? ' show' : '' ;?>">
      <label for="<?php echo esc_attr( $this->get_field_id('columns') ); ?>"><?php echo __( 'Columns:', 'everexpert-woocommerce-publishers' );?></label>
      <select
        class="widefat"
        id="<?php echo esc_attr( $this->get_field_id('columns') ); ?>"
        name="<?php echo esc_attr( $this->get_field_name('columns') ); ?>">
        <option value="1" <?php selected( $columns, '1' ); ?>>1</option>
        <option value="2" <?php selected( $columns, '2' ); ?>>2</option>
        <option value="3" <?php selected( $columns, '3' ); ?>>3</option>
        <option value="4" <?php selected( $columns, '4' ); ?>>4</option>
        <option value="5" <?php selected( $columns, '5' ); ?>>5</option>
        <option value="6" <?php selected( $columns, '6' ); ?>>6</option>
      </select>
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
    <p class="ewp-display-as-logo<?php echo ($display_as=='publisher_logo') ? ' show' : '' ;?>">
      <input
      type="checkbox"
      id="<?php echo esc_attr( $this->get_field_id('randomize') ); ?>"
      name="<?php echo esc_attr( $this->get_field_name('randomize') ); ?>"
      <?php checked( $randomize ); ?>>
      <label for="<?php echo esc_attr( $this->get_field_id('randomize') ); ?>">
        <?php echo __( 'Randomize', 'everexpert-woocommerce-publishers' );?>
      </label>
    </p>

  <?php
  }

  public function widget( $args, $instance ){
    extract( $args );
    extract( $instance );

    $hide_empty    = ( isset( $hide_empty ) && $hide_empty == 'on' ) ? true : false;
    $only_featured = ( isset( $only_featured ) && $only_featured == 'on' ) ? true : false;
    $randomize     = ( isset( $randomize ) && $randomize == 'on' ) ? true : false;
    $publishers = \Everexpert_Woocommerce_Publishers\Everexpert_Woocommerce_Publishers::get_publishers(
      $hide_empty, 'name', 'ASC', $only_featured, true
    );
    if( isset( $randomize ) && $randomize == 'on' && $display_as == 'publisher_logo' ) shuffle( $publishers );

    if( is_array( $publishers ) && count( $publishers ) > 0 ){

      echo $before_widget;

        if( !empty( $title ) ) echo $before_title . $title . $after_title;

        if( !isset( $display_as ) ) $display_as = 'publisher_logo';
        if( !isset( $columns ) ) $columns = '2';
        $li_class = ( $display_as == 'publisher_logo' ) ? "ewp-columns ewp-columns-".$columns : "";

        echo \Everexpert_Woocommerce_Publishers\Everexpert_Woocommerce_Publishers::render_template(
          ( $display_as == 'publisher_logo' ) ? 'list-logo' : 'list',
          'widgets',
          array( 'publishers' => $publishers, 'li_class' => $li_class, 'title_prefix' => __( 'Go to', 'everexpert-woocommerce-publishers' ) ),
          false
        );

      echo $after_widget;

    }

  }

}
