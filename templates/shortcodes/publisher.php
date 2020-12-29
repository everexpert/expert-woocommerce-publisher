<?php

/**
 * The template for displaying the "ewp-publisher" shortcode
 * @version 1.0.0
 */

defined('ABSPATH') or die('No script kiddies please!');
?>

<?php if (!empty($publishers)) : ?>

  <div class="ewp-publisher-shortcode">

    <?php foreach ($publishers as $publisher) : ?>

      <a href="<?php echo esc_url($publisher->term_link); ?>" title="<?php _e('View publisher', 'everexpert-woocommerce-publishers'); ?>">

        <?php if (!$as_link && !empty($publisher->image)) : ?>

          <?php echo wp_kses_post($publisher->image); ?>

        <?php else : ?>

          <?php echo esc_html($publisher->name); ?>

        <?php endif; ?>

      </a>

    <?php endforeach; ?>

  </div>

<?php endif; ?>