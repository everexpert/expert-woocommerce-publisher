<?php

/**
 * The template for displaying the carousels
 * @version 1.0.0
 */

defined('ABSPATH') or die('No script kiddies please!');
?>

<div class="ewp-carousel" data-slick="<?php echo $slick_settings; ?>">

  <?php foreach ($publishers as $publisher) : ?>
    <div class="ewp-slick-slide">
      <a href="<?php echo esc_url($publisher['link']); ?>" title="<?php echo esc_html($publisher['name']); ?>">
        <?php echo wp_kses_post($publisher['attachment_html']); ?>
      </a>
    </div>
  <?php endforeach; ?>

  <div class="ewp-carousel-loader"><?php esc_html_e('Loading', 'everexpert-woocommerce-publishers'); ?>...</div>

</div>