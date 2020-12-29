<?php

/**
 * The template for displaying the a-z Listing
 * @version 1.0.1
 */

defined('ABSPATH') or die('No script kiddies please!');
?>

<?php if (!empty($grouped_publishers)) : ?>

  <div class="ewp-az-listing">

    <div class="ewp-az-listing-header">

      <ul class="ewp-clearfix">

        <?php foreach ($grouped_publishers as $letter => $publisher_group) : ?>
          <li><a href="#ewp-az-listing-<?php echo esc_attr($letter); ?>"><?php echo esc_html($letter); ?></a></li>
        <?php endforeach; ?>

      </ul>

    </div>

    <div class="ewp-az-listing-content">

      <?php foreach ($grouped_publishers as $letter => $publisher_group) : ?>

        <div id="ewp-az-listing-<?php echo esc_attr($letter); ?>" class="ewp-az-listing-row ewp-clearfix">
          <p class="ewp-az-listing-title"><?php echo esc_attr($letter); ?></p>
          <div class="ewp-az-listing-row-in">

            <?php foreach ($publisher_group as $publisher) : ?>

              <div class="ewp-az-listing-col">
                <a href="<?php echo get_term_link($publisher['publisher_term']->term_id); ?>">
                  <?php echo esc_html($publisher['publisher_term']->name); ?>
                </a>
              </div>

            <?php endforeach; ?>

          </div>
        </div>

      <?php endforeach; ?>

    </div>

  </div>

<?php endif; ?>