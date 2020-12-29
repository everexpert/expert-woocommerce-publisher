<?php

/**
 * The template for displaying filter by publisher widget
 * @version 1.0.0
 */

defined('ABSPATH') or die('No script kiddies please!');
?>

<div class="ewp-filter-products<?php if ($hide_submit_btn) echo ' ewp-hide-submit-btn'; ?>" data-cat-url="<?php echo esc_url($cate_url); ?>">
  <ul>
    <?php foreach ($publishers as $publisher) : ?>
      <li>
        <label>
          <input type="checkbox" data-publisher="<?php echo esc_attr($publisher->term_id); ?>" value="<?php echo esc_html($publisher->slug); ?>"><?php echo esc_html($publisher->name); ?>
        </label>
      </li>
    <?php endforeach; ?>
  </ul>
  <?php if (!$hide_submit_btn) : ?>
    <button><?php esc_html_e('Apply filter', 'everexpert-woocommerce-publishers') ?></button>
  <?php endif; ?>
</div>