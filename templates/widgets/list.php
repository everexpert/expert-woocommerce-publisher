<?php

/**
 * The template for displaying the list widget
 * @version 1.0.0
 */

defined('ABSPATH') or die('No script kiddies please!');
?>

<ul class="ewp-row">

  <?php foreach ($data['publishers'] as $publisher) : ?>

    <li>
      <a href="<?php echo esc_html($publisher->get('link')); ?>" title="<?php echo esc_html($data['title_prefix'] . ' ' . $publisher->get('name')); ?>">
        <?php echo esc_html($publisher->get('name')); ?>
      </a>
    </li>

  <?php endforeach; ?>

</ul>