<?php

namespace Everexpert_Woocommerce_Publishers\Shortcodes;

defined('ABSPATH') or die('No script kiddies please!');

class EWP_All_Publishers_Shortcode
{

  public static function all_publishers_shortcode($atts)
  {

    $atts = shortcode_atts(array(
      'per_page'       => "10",
      'image_size'     => "thumbnail",
      'hide_empty'     => false,
      'order_by'       => 'name',
      'order'          => 'ASC',
      'title_position' => 'before'
    ), $atts, 'ewp-all-publishers');

    $hide_empty = ($atts['hide_empty'] != 'true') ? false : true;

    ob_start();

    $publishers = array();
    if ($atts['order_by'] == 'rand') {
      $publishers = \Everexpert_Woocommerce_Publishers\Everexpert_Woocommerce_Publishers::get_publishers($hide_empty);
      shuffle($publishers);
    } else {
      $publishers = \Everexpert_Woocommerce_Publishers\Everexpert_Woocommerce_Publishers::get_publishers($hide_empty, $atts['order_by'], $atts['order']);
    }

    //remove residual empty publishers
    foreach ($publishers as $key => $publisher) {

      $count = self::count_visible_products($publisher->term_id);

      if (!$count && $hide_empty) {
        unset($publishers[$key]);
      } else {
        $publishers[$key]->count_ewp = $count;
      }
    }

?>
    <div class="ewp-all-publishers">
      <?php static::pagination($publishers, $atts['per_page'], $atts['image_size'], $atts['title_position']); ?>
    </div>
    <?php

    return ob_get_clean();
  }

  /**
   *  WP_Term->count property don´t care about hidden products
   *  Counts the products in a specific publisher
   */
  public static function count_visible_products($publisher_id)
  {

    $args = array(
      'posts_per_page' => -1,
      'post_type'      => 'product',
      'tax_query'      => array(
        array(
          'taxonomy'  => 'ewp-publisher',
          'field'     => 'term_id',
          'terms'     => $publisher_id
        ),
        array(
          'taxonomy' => 'product_visibility',
          'field'    => 'name',
          'terms'    => 'exclude-from-catalog',
          'operator' => 'NOT IN',
        )
      )
    );
    $wc_query = new \WP_Query($args);

    return $wc_query->found_posts;
  }

  public static function pagination($display_array, $show_per_page, $image_size, $title_position)
  {
    $page = 1;

    if (isset($_GET['ewp-page']) && filter_var($_GET['ewp-page'], FILTER_VALIDATE_INT) == true) {
      $page = $_GET['ewp-page'];
    }

    $page = $page < 1 ? 1 : $page;

    // start position in the $display_array
    // +1 is to account for total values.
    $start = ($page - 1) * ($show_per_page);
    $offset = $show_per_page;

    $outArray = array_slice($display_array, $start, $offset);

    //pagination links
    $total_elements = count($display_array);
    $pages = ((int)$total_elements / (int)$show_per_page);
    $pages = ceil($pages);
    if ($pages >= 1 && $page <= $pages) {

    ?>
      <div class="ewp-publishers-cols-outer">
        <?php
        foreach ($outArray as $publisher) {

          $publisher_id   = $publisher->term_id;
          $publisher_name = $publisher->name;
          $publisher_link = get_term_link($publisher_id);

          $attachment_id = get_term_meta($publisher_id, 'ewp_publisher_image', 1);
          $attachment_html = $publisher_name;
          if ($attachment_id != '') {
            $attachment_html = wp_get_attachment_image($attachment_id, $image_size);
          }

        ?>
          <div class="ewp-publishers-col3">

            <?php if ($title_position != 'none' && $title_position != 'after') : ?>
              <p>
                <a href="<?php echo esc_url($publisher_link); ?>">
                  <?php echo esc_html($publisher_name); ?>
                </a>
                <small>(<?php echo esc_html($publisher->count_ewp); ?>)</small>
              </p>
            <?php endif; ?>

            <div>
              <a href="<?php echo esc_url($publisher_link); ?>" title="<?php echo esc_html($publisher_name); ?>">
                <?php echo wp_kses_post($attachment_html); ?>
              </a>
            </div>

            <?php if ($title_position != 'none' && $title_position == 'after') : ?>
              <p>
                <a href="<?php echo esc_html($publisher_link); ?>">
                  <?php echo wp_kses_post($publisher_name); ?>
                </a>
                <small>(<?php echo esc_html($publisher->count_ewp); ?>)</small>
              </p>
            <?php endif; ?>

          </div>
        <?php
        }
        ?>
      </div>
<?php
      $next = $page + 1;
      $prev = $page - 1;

      echo '<div class="ewp-pagination-wrapper">';
      if ($prev > 1) {
        echo '<a href="' . get_the_permalink() . '" class="ewp-pagination prev" title="' . esc_html__('First page', 'everexpert-woocommerce-publishers') . '">&laquo;</a>';
      }
      if ($prev > 0) {
        echo '<a href="' . get_the_permalink() . '?ewp-page=' . $prev . '" class="ewp-pagination last" title="' . esc_html__('Previous page', 'everexpert-woocommerce-publishers') . '">&lsaquo;</a>';
      }

      if ($next <= $pages) {
        echo '<a href="' . get_the_permalink() . '?ewp-page=' . $next . '" class="ewp-pagination first" title="' . esc_html__('Next page', 'everexpert-woocommerce-publishers') . '">&rsaquo;</a>';
      }
      if ($next < $pages) {
        echo '<a href="' . get_the_permalink() . '?ewp-page=' . $pages . '" class="ewp-pagination next" title="' . esc_html__('Last page', 'everexpert-woocommerce-publishers') . '">&raquo;</a>';
      }
      echo '</div>';
    } else {
      echo esc_html__('No results', 'everexpert-woocommerce-publishers');
    }
  }
}
