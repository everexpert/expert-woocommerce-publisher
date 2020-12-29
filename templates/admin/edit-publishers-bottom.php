<?php

/**
 * The template for displaying the edit-tags.php table footer
 * @version 1.0.0
 */

defined('ABSPATH') or die('No script kiddies please!');
?>

<div class="ewp-edit-publishers-bottom ewp-clearfix">
  <span class="dashicons dashicons-admin-collapse"></span>
  <p class="ewp-featured-count">
    <span><?php echo esc_html($data['featured_count']); ?></span> <?php echo esc_html($data['text_featured']); ?>
  </p>
</div>