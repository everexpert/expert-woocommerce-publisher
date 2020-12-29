<?php

/**
 * The template for displaying the list logo widget
 * @version 1.0.0
 */

defined('ABSPATH') or die('No script kiddies please!');
?>

<ul class="ewp-row">

  <?php foreach ($data['publishers'] as $publisher) : ?>

    <li class="<?php echo esc_attr($data['li_class']); ?>">

      <a href="<?php echo esc_url($publisher->get('link')); ?>" title="<?php echo esc_html($data['title_prefix'] . ' ' . $publisher->get('name')); ?>">

        <?php if (!empty(html_entity_decode($publisher->get('image')))) : ?>
          <?php echo html_entity_decode($publisher->get('image')); ?>
        <?php endif; ?>

      </a>

    </li>

  <?php endforeach; ?>

</ul>