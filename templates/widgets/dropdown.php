<?php

/**
 * The template for displaying the dropdown widget
 * @version 1.0.0
 */

defined('ABSPATH') or die('No script kiddies please!');
?>

<select class="ewp-dropdown-widget">
  <option selected="true" disabled="disabled">
    <?php echo apply_filters('ewp_dropdown_placeholder', __('Publishers', 'everexpert-woocommerce-publishers')); ?>
  </option>
  <?php foreach ($publishers as $publisher) : ?>
    <option value="<?php echo esc_url($publisher->get('link')); ?>" <?php selected($data['selected'], $publisher->get('id')); ?>>
      <?php echo esc_html($publisher->get('name')); ?>
    </option>
  <?php endforeach; ?>
</select>