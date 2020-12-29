<?php

namespace Everexpert_Woocommerce_Publishers;

defined('ABSPATH') or die('No script kiddies please!');

class EWP_Product_Tab
{

  function __construct()
  {
    add_filter('woocommerce_product_tabs', array($this, 'product_tab'));
  }

  public function product_tab($tabs)
  {

    global $product;

    if (isset($product)) {
      $publishers = wp_get_object_terms($product->get_id(), 'ewp-publisher');

      if (!empty($publishers)) {
        $show_publisher_tab = get_option('wc_ewp_admin_tab_publisher_single_product_tab');
        if ($show_publisher_tab == 'yes' || !$show_publisher_tab) {
          $tabs['ewp_tab'] = array(
            'title'     => __('Publisher', 'everexpert-woocommerce-publishers'),
            'priority'   => 20,
            'callback'   => array($this, 'product_tab_content')
          );
        }
      }
    }

    return $tabs;
  }

  public function product_tab_content()
  {

    global $product;
    $publishers = wp_get_object_terms($product->get_id(), 'ewp-publisher');

    ob_start();
?>

    <h2><?php echo apply_filters('woocommerce_product_publisher_heading', esc_html__('Publisher', 'everexpert-woocommerce-publishers')); ?></h2>
    <?php foreach ($publishers as $publisher) : ?>

      <?php
      $image_size = get_option('wc_ewp_admin_tab_publisher_logo_size', 'thumbnail');
      $publisher_logo = get_term_meta($publisher->term_id, 'ewp_publisher_image', true);
      $publisher_logo = wp_get_attachment_image($publisher_logo, apply_filters('ewp_product_tab_publisher_logo_size', $image_size));
      ?>

      <div id="tab-ewp_tab-content">
        <h3><?php echo esc_html($publisher->name); ?></h3>
        <?php if (!empty($publisher->description)) echo '<div>' . do_shortcode($publisher->description) . '</div>'; ?>
        <?php if (!empty($publisher_logo)) echo '<span>' . $publisher_logo . '</span>'; ?>
      </div>

    <?php endforeach; ?>

<?php
    echo ob_get_clean();
  }
}
