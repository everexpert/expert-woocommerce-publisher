<?php

namespace Everexpert_Woocommerce_Publishers;

defined('ABSPATH') or die('No script kiddies please!');

class Everexpert_Woocommerce_Publishers
{

  function __construct()
  {
    add_action('plugin_row_meta', array('\Everexpert_Woocommerce_Publishers\Everexpert_Woocommerce_Publishers', 'plugin_row_meta'), 10, 2);
    add_action('woocommerce_init', array($this, 'register_publishers_taxonomy'), 10, 0);
    add_action('init', array($this, 'add_publishers_metafields'));
    add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
    $this->publisher_logo_position();
    add_action('wp', array($this, 'publisher_desc_position'));
    add_action('woocommerce_after_shop_loop_item_title', array($this, 'show_publishers_in_loop'));
    $this->add_shortcodes();
    if (is_plugin_active('js_composer/js_composer.php') || is_plugin_active('visual_composer/js_composer.php')) {
      add_action('vc_before_init', array($this, 'vc_map_shortcodes'));
    }
    add_action('widgets_init', array($this, 'register_widgets'));
    add_filter('woocommerce_structured_data_product', array($this, 'product_microdata'), 10, 2);
    add_action('pre_get_posts', array($this, 'ewp_publisher_filter'));
    add_action('wp_ajax_dismiss_ewp_notice', array($this, 'dismiss_ewp_notice'));
    add_action('admin_notices', array($this, 'review_notice'));

    add_action('wp', function () {
      if (is_tax('ewp-publisher'))
        remove_action('woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10);
    });
    add_action('woocommerce_product_duplicate', array($this, 'product_duplicate_save'), 10, 2);

    add_filter('woocommerce_get_breadcrumb', array($this, 'breadcrumbs'));

    add_filter('shortcode_atts_products', array($this, 'extend_products_shortcode_atts'), 10, 4);
    add_filter('woocommerce_shortcode_products_query', array($this, 'extend_products_shortcode'), 10, 2);

    add_filter('manage_edit-product_sortable_columns', array($this, 'publishers_column_sortable'), 90);
    add_action('posts_clauses', array($this, 'publishers_column_sortable_posts'), 10, 2);
    add_filter('post_type_link', array($this, 'publisher_name_in_url'), 10, 2);
    add_action('pre_get_posts', array($this, 'search_by_publisher_name'));

    //clean caches
    add_action('edited_terms', array($this, 'clean_caches'), 10, 2);
    add_action('created_term', array($this, 'clean_caches_after_edit_publisher'), 10, 3);
    add_action('delete_term', array($this, 'clean_caches_after_edit_publisher'), 10, 3);
  }

  public function clean_caches($term_id, $taxonomy)
  {
    if ($taxonomy != 'ewp-publisher')
      return;
    delete_transient('ewp_az_listing_cache');
  }

  public function clean_caches_after_edit_publisher($term_id, $tt_id, $taxonomy)
  {
    if ($taxonomy != 'ewp-publisher')
      return;
    delete_transient('ewp_az_listing_cache');
  }

  /**
   * Show row meta on the plugin screen.
   *
   * @param mixed $links Plugin Row Meta.
   * @param mixed $file  Plugin Base file.
   *
   * @return array
   */
  public static function plugin_row_meta($links, $file)
  {
    if (EWP_PLUGIN_BASENAME === $file) {
      $row_meta = array(
        'docs' => '<a target="_blank" rel="noopener noferrer" href="' . EWP_DOCUMENTATION_URL . '">' . esc_html__('Documentation', 'everexpert-woocommerce-publishers') . '</a>',
      );
      return array_merge($links, $row_meta);
    }
    return (array) $links;
  }

  public function publisher_name_in_url($permalink, $post)
  {
    if ($post->post_type == 'product' && strpos($permalink, '%ewp-publisher%') !== false) {
      $term = 'product';
      $publishers = wp_get_post_terms($post->ID, 'ewp-publisher');
      if (!empty($publishers) && !is_wp_error($publishers))
        $term = current($publishers)->slug;
      $permalink = str_replace('%ewp-publisher%', $term, $permalink);
    }
    return $permalink;
  }

  public function publishers_column_sortable_posts($clauses, $wp_query)
  {
    global $wpdb;

    if (isset($wp_query->query['orderby']) && 'taxonomy-ewp-publisher' == $wp_query->query['orderby']) {

      $clauses['join'] .= "
      LEFT OUTER JOIN {$wpdb->term_relationships} ON {$wpdb->posts}.ID={$wpdb->term_relationships}.object_id
      LEFT OUTER JOIN {$wpdb->term_taxonomy} USING (term_taxonomy_id)
      LEFT OUTER JOIN {$wpdb->terms} USING (term_id)";

      $clauses['where'] .= " AND (taxonomy = 'ewp-publisher' OR taxonomy IS NULL)";
      $clauses['groupby'] = "object_id";
      $clauses['orderby'] = "GROUP_CONCAT({$wpdb->terms}.name ORDER BY name ASC) ";
      $clauses['orderby'] .= ('ASC' == strtoupper($wp_query->get('order'))) ? 'ASC' : 'DESC';
    }

    return $clauses;
  }

  public function publishers_column_sortable($columns)
  {
    $columns['taxonomy-ewp-publisher'] = 'taxonomy-ewp-publisher';
    return $columns;
  }

  public function extend_products_shortcode_atts($out, $pairs, $atts, $shortcode)
  {
    if (!empty($atts['publishers']))
      $out['publishers'] = explode(',', $atts['publishers']);
    return $out;
  }

  public function extend_products_shortcode($query_args, $atts)
  {

    if (!empty($atts['publishers'])) {
      global $wpdb;

      $terms = $atts['publishers'];
      $terms_count = count($atts['publishers']);
      $terms_adapted = '';

      $terms_i = 0;
      foreach ($terms as $publisher) {
        $terms_adapted .= '"' . $publisher . '"';
        $terms_i++;
        if ($terms_i < $terms_count)
          $terms_adapted .= ',';
      }

      $ids = $wpdb->get_col("
      SELECT DISTINCT tr.object_id
      FROM {$wpdb->prefix}term_relationships as tr
      INNER JOIN {$wpdb->prefix}term_taxonomy as tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
      INNER JOIN {$wpdb->prefix}terms as t ON tt.term_id = t.term_id
      WHERE tt.taxonomy LIKE 'ewp_publisher' AND t.slug IN ($terms_adapted)
      ");

      if (!empty($ids)) {
        if (1 === count($ids)) {
          $query_args['p'] = $ids[0];
        } else {
          $query_args['post__in'] = $ids;
        }
      }
    }

    return $query_args;
  }

  public function review_notice()
  {
    $show_notice = get_option('wc_ewp_notice_plugin_review', true);
    $activate_on = get_option('ewp_activate_on', time());
    $now = time();
    $one_week = 604800;
    $date_diff = $now - $activate_on;

    if ($show_notice && $date_diff > $one_week) {
?>
      <div class="notice notice-info ewp-notice-dismissible is-dismissible" data-notice="wc_ewp_notice_plugin_review">
        <p><?php echo esc_html__('We know that you´re in love with Everexpert WooCommerce Publishers, you can help us making it a bit better. Thanks a lot!', 'everexpert-woocommerce-publishers'); ?><span class="dashicons dashicons-heart"></span></p>
        <p>
          <a href="https://wordpress.org/support/plugin/everexpert-woocommerce-publishers/reviews/?rate=5#new-post" target="_blank"><?php esc_html_e('Leave a review', 'everexpert-woocommerce-publishers'); ?></a>
          <a href="https://translate.wordpress.org/projects/wp-plugins/everexpert-woocommerce-publishers" target="_blank"><?php esc_html_e('Translate the plugin', 'everexpert-woocommerce-publishers'); ?></a>
          <a href="<?php echo esc_url(EWP_GITHUB_URL); ?>" target="_blank"><?php esc_html_e('View on GitHub', 'everexpert-woocommerce-publishers'); ?></a>
        </p>
      </div>
<?php
    }
  }

  public function dismiss_ewp_notice()
  {
    $notice_name_whitelist = array('wc_ewp_notice_plugin_review');
    if (isset($_POST['notice_name']) && in_array($_POST['notice_name'], $notice_name_whitelist)) {
      update_option($_POST['notice_name'], 0);
      echo 'ok';
    } else {
      echo 'error';
    }
    wp_die();
  }

  public function ewp_publisher_filter($query)
  {

    if (!empty($_GET['ewp-publisher-filter'])) {

      $terms_array = explode(',', $_GET['ewp-publisher-filter']);

      //remove invalid terms (security)
      for ($i = 0; $i < count($terms_array); $i++) {
        if (!term_exists($terms_array[$i], 'ewp-publisher'))
          unset($terms_array[$i]);
      }

      $filterable_product = false;
      if (is_product_taxonomy() || is_post_type_archive('product'))
        $filterable_product = true;

      if ($filterable_product && $query->is_main_query()) {

        $query->set('tax_query', array(
          array(
            'taxonomy' => 'ewp-publisher',
            'field' => 'slug',
            'terms' => $terms_array
          )
        ));
      }
    }
  }

  /*
   *   Adds microdata (publishers) to single products
   */

  public function product_microdata($markup, $product)
  {

    $new_markup = array();
    $publishers = wp_get_post_terms($product->get_id(), 'ewp-publisher');
    foreach ($publishers as $publisher) {
      $new_markup['publisher'][] = $publisher->name;
    }

    return array_merge($markup, $new_markup);
  }

  public function add_shortcodes()
  {
    add_shortcode('ewp-carousel', array(
      '\Everexpert_Woocommerce_Publishers\Shortcodes\EWP_Carousel_Shortcode',
      'carousel_shortcode'
    ));
    add_shortcode('ewp-product-carousel', array(
      '\Everexpert_Woocommerce_Publishers\Shortcodes\EWP_Product_Carousel_Shortcode',
      'product_carousel_shortcode'
    ));
    add_shortcode('ewp-all-publishers', array(
      '\Everexpert_Woocommerce_Publishers\Shortcodes\EWP_All_publishers_Shortcode',
      'all_publishers_shortcode'
    ));
    add_shortcode('ewp-az-listing', array(
      '\Everexpert_Woocommerce_Publishers\Shortcodes\EWP_AZ_Listing_Shortcode',
      'shortcode'
    ));
    add_shortcode('ewp-publisher', array(
      '\Everexpert_Woocommerce_Publishers\Shortcodes\EWP_publisher_Shortcode',
      'publisher_shortcode'
    ));
  }

  public function register_widgets()
  {
    register_widget('\Everexpert_Woocommerce_Publishers\Widgets\EWP_List_Widget');
    register_widget('\Everexpert_Woocommerce_Publishers\Widgets\EWP_Dropdown_Widget');
    register_widget('\Everexpert_Woocommerce_Publishers\Widgets\EWP_Filter_By_publisher_Widget');
  }

  public function show_publishers_in_loop()
  {

    $publishers_in_loop = get_option('wc_ewp_admin_tab_publishers_in_loop');
    $image_size_selected = get_option('wc_ewp_admin_tab_publisher_logo_size', 'thumbnail');

    if ($publishers_in_loop == 'publisher_link' || $publishers_in_loop == 'publisher_image') {

      global $product;
      $product_id = $product->get_id();
      $product_publishers = wp_get_post_terms($product_id, 'ewp-publisher');
      if (!empty($product_publishers)) {
        echo '<div class="ewp-publishers-in-loop">';
        foreach ($product_publishers as $publisher) {

          echo '<span>';
          $publisher_link = get_term_link($publisher->term_id, 'ewp-publisher');
          $attachment_id = get_term_meta($publisher->term_id, 'ewp_publisher_image', 1);

          $attachment_html = wp_get_attachment_image($attachment_id, $image_size_selected);
          if (!empty($attachment_html) && $publishers_in_loop == 'publisher_image') {
            echo '<a href="' . $publisher_link . '">' . $attachment_html . '</a>';
          } else {
            echo '<a href="' . $publisher_link . '">' . $publisher->name . '</a>';
          }
          echo '</span>';
        }
        echo '</div>';
      }
    }
  }

  /**
   * woocommerce_single_product_summary hook.
   *
   * @hooked woocommerce_template_single_title - 5
   * @hooked woocommerce_template_single_rating - 10
   * @hooked woocommerce_template_single_price - 10
   * @hooked woocommerce_template_single_excerpt - 20
   * @hooked woocommerce_template_single_add_to_cart - 30
   * @hooked woocommerce_template_single_meta - 40
   * @hooked woocommerce_template_single_sharing - 50
   */
  private function publisher_logo_position()
  {
    $position = 41;
    $position_selected = get_option('wc_ewp_admin_tab_publisher_single_position');
    if (!$position_selected) {
      update_option('wc_ewp_admin_tab_publisher_single_position', 'after_meta');
    }

    switch ($position_selected) {
      case 'before_title':
        $position = 4;
        break;
      case 'after_title':
        $position = 6;
        break;
      case 'after_price':
        $position = 11;
        break;
      case 'after_excerpt':
        $position = 21;
        break;
      case 'after_add_to_cart':
        $position = 31;
        break;
      case 'after_meta':
        $position = 41;
        break;
      case 'after_sharing':
        $position = 51;
        break;
    }

    if ($position_selected == 'meta') {
      add_action('woocommerce_product_meta_end', [$this, 'action_woocommerce_single_product_summary']);
    } else {
      add_action('woocommerce_single_product_summary', [$this, 'action_woocommerce_single_product_summary'], $position);
    }
  }

  public function publisher_desc_position()
  {

    if (is_tax('ewp-publisher') && !is_paged()) {

      $show_banner = get_option('wc_ewp_admin_tab_publisher_banner');
      $show_desc = get_option('wc_ewp_admin_tab_publisher_desc');

      if ((!$show_banner || $show_banner == 'yes') && (!$show_desc || $show_desc == 'yes')) {
        //show banner and description before loop
        add_action('woocommerce_archive_description', array($this, 'print_publisher_banner_and_desc'), 15);
      } elseif ($show_banner == 'yes_after_loop' && $show_desc == 'yes_after_loop') {
        //show banner and description after loop
        add_action('woocommerce_after_main_content', array($this, 'print_publisher_banner_and_desc'), 9);
      } else {
        //show banner and description independently

        if (!$show_banner || $show_banner == 'yes') {
          add_action('woocommerce_archive_description', array($this, 'print_publisher_banner'), 15);
        } elseif ($show_banner == 'yes_after_loop') {
          add_action('woocommerce_after_main_content', array($this, 'print_publisher_banner'), 9);
        }

        if (!$show_desc || $show_desc == 'yes') {
          add_action('woocommerce_archive_description', array($this, 'print_publisher_desc'), 15);
        } elseif ($show_desc == 'yes_after_loop') {
          add_action('woocommerce_after_main_content', array($this, 'print_publisher_desc'), 9);
        }
      }
    }
  }

  /*
   * Maps shortcode (for visual composer plugin)
   *
   * @since 1.0
   * @link https://vc.wpbakery.com/
   * @return mixed
   */

  public function vc_map_shortcodes()
  {
    $available_image_sizes_adapted = array();
    $available_image_sizes = get_intermediate_image_sizes();

    foreach ($available_image_sizes as $image_size) {
      $available_image_sizes_adapted[$image_size] = $image_size;
    }

    vc_map(array(
      "name" => __("EWP Product carousel", "everexpert-woocommerce-publishers"),
      "description" => __("Product carousel by publisher or by category", "everexpert-woocommerce-publishers"),
      "base" => "ewp-product-carousel",
      "class" => "",
      "icon" => EWP_PLUGIN_URL . '/assets/img/icon_ewp.jpg',
      "category" => "WooCommerce",
      "params" => array(
        array(
          "type" => "dropdown",
          "heading" => __("Publisher", "everexpert-woocommerce-publishers"),
          "param_name" => "publisher",
          "admin_label" => true,
          "value" => self::get_publishers_array(true)
        ),
        array(
          "type" => "textfield",
          "holder" => "div",
          "heading" => __("Products", "everexpert-woocommerce-publishers"),
          "param_name" => "products",
          "value" => "10",
          "description" => __("Number of products to load", "everexpert-woocommerce-publishers")
        ),
        array(
          "type" => "textfield",
          "holder" => "div",
          "heading" => __("Products to show", "everexpert-woocommerce-publishers"),
          "param_name" => "products_to_show",
          "value" => "5",
          "description" => __("Number of products to show", "everexpert-woocommerce-publishers")
        ),
        array(
          "type" => "textfield",
          "holder" => "div",
          "heading" => __("Products to scroll", "everexpert-woocommerce-publishers"),
          "param_name" => "products_to_scroll",
          "value" => "1",
          "description" => __("Number of products to scroll", "everexpert-woocommerce-publishers")
        ),
        array(
          "type" => "checkbox",
          "holder" => "div",
          "heading" => __("Autoplay", "everexpert-woocommerce-publishers"),
          "param_name" => "autoplay",
          "description" => __("Autoplay carousel", "everexpert-woocommerce-publishers")
        ),
        array(
          "type" => "checkbox",
          "holder" => "div",
          "heading" => __("Arrows", "everexpert-woocommerce-publishers"),
          "param_name" => "arrows",
          "description" => __("Display prev and next arrows", "everexpert-woocommerce-publishers")
        )
      )
    ));

    vc_map(array(
      "name" => __("EWP Publishers carousel", "everexpert-woocommerce-publishers"),
      "description" => __("Publishers carousel", "everexpert-woocommerce-publishers"),
      "base" => "ewp-carousel",
      "class" => "",
      "icon" => EWP_PLUGIN_URL . '/assets/img/icon_ewp.jpg',
      "category" => "WooCommerce",
      "params" => array(
        array(
          "type" => "textfield",
          "holder" => "div",
          "heading" => __("Items", "everexpert-woocommerce-publishers"),
          "param_name" => "items",
          "value" => "10",
          "description" => __("Number of items to load (or 'featured')", "everexpert-woocommerce-publishers")
        ),
        array(
          "type" => "textfield",
          "holder" => "div",
          "heading" => __("Items to show", "everexpert-woocommerce-publishers"),
          "param_name" => "items_to_show",
          "value" => "5",
          "description" => __("Number of items to show", "everexpert-woocommerce-publishers")
        ),
        array(
          "type" => "textfield",
          "holder" => "div",
          "heading" => __("Items to scroll", "everexpert-woocommerce-publishers"),
          "param_name" => "items_to_scroll",
          "value" => "1",
          "description" => __("Number of items to scroll", "everexpert-woocommerce-publishers")
        ),
        array(
          "type" => "checkbox",
          "holder" => "div",
          "heading" => __("Autoplay", "everexpert-woocommerce-publishers"),
          "param_name" => "autoplay",
          "description" => __("Autoplay carousel", "everexpert-woocommerce-publishers")
        ),
        array(
          "type" => "checkbox",
          "holder" => "div",
          "heading" => __("Arrows", "everexpert-woocommerce-publishers"),
          "param_name" => "arrows",
          "description" => __("Display prev and next arrows", "everexpert-woocommerce-publishers")
        ),
        array(
          "type" => "dropdown",
          "heading" => __("Publisher logo size", "everexpert-woocommerce-publishers"),
          "param_name" => "image_size",
          "admin_label" => true,
          "value" => $available_image_sizes_adapted
        )
      )
    ));

    vc_map(array(
      "name" => __("EWP All publishers", "everexpert-woocommerce-publishers"),
      "description" => __("Show all publishers", "everexpert-woocommerce-publishers"),
      "base" => "ewp-all-publishers",
      "class" => "",
      "icon" => EWP_PLUGIN_URL . '/assets/img/icon_ewp.jpg',
      "category" => "WooCommerce",
      "params" => array(
        array(
          "type" => "textfield",
          "holder" => "div",
          "heading" => __("Publishers per page", "everexpert-woocommerce-publishers"),
          "param_name" => "per_page",
          "value" => "10",
          "description" => __("Show x publishers per page", "everexpert-woocommerce-publishers")
        ),
        array(
          "type" => "dropdown",
          "heading" => __("Publisher logo size", "everexpert-woocommerce-publishers"),
          "param_name" => "image_size",
          "admin_label" => true,
          "value" => $available_image_sizes_adapted
        ),
        array(
          "type" => "dropdown",
          "heading" => __("Order by", "everexpert-woocommerce-publishers"),
          "param_name" => "order_by",
          "admin_label" => true,
          "value" => array(
            'name' => 'name',
            'slug' => 'slug',
            'term_id' => 'term_id',
            'id' => 'id',
            'description' => 'description',
            'rand' => 'rand'
          )
        ),
        array(
          "type" => "dropdown",
          "heading" => __("Order", "everexpert-woocommerce-publishers"),
          "param_name" => "order",
          "admin_label" => true,
          "value" => array(
            'ASC' => 'ASC',
            'DSC' => 'DSC'
          )
        ),
        array(
          "type" => "dropdown",
          "heading" => __("Title position", "everexpert-woocommerce-publishers"),
          "param_name" => "title_position",
          "admin_label" => true,
          "value" => array(
            __("Before image", "everexpert-woocommerce-publishers") => 'before',
            __("After image", "everexpert-woocommerce-publishers") => 'after',
            __("Hide", "everexpert-woocommerce-publishers") => 'none'
          )
        ),
        array(
          "type" => "checkbox",
          "holder" => "div",
          "heading" => __("Hide empty", "everexpert-woocommerce-publishers"),
          "param_name" => "hide_empty",
          "description" => __("Hide publishers that have not been assigned to any product", "everexpert-woocommerce-publishers")
        )
      )
    ));

    vc_map(array(
      "name" => __("EWP AZ Listing", "everexpert-woocommerce-publishers"),
      "description" => __("AZ Listing for publishers", "everexpert-woocommerce-publishers"),
      "base" => "ewp-az-listing",
      "class" => "",
      "icon" => EWP_PLUGIN_URL . '/assets/img/icon_ewp.jpg',
      "category" => "WooCommerce",
      "params" => array(
        array(
          "type" => "dropdown",
          "heading" => __("Only parent publishers", "everexpert-woocommerce-publishers"),
          "param_name" => "only_parents",
          "admin_label" => true,
          "value" => array(esc_html__('No') => 'no', esc_html__('Yes') => 'yes'),
        )
      )
    ));

    vc_map(array(
      "name" => __("EWP publisher", "everexpert-woocommerce-publishers"),
      "description" => __("Show publisher for a specific product", "everexpert-woocommerce-publishers"),
      "base" => "ewp-publisher",
      "class" => "",
      "icon" => EWP_PLUGIN_URL . '/assets/img/icon_ewp.jpg',
      "category" => "WooCommerce",
      "params" => array(
        array(
          "type" => "textfield",
          "holder" => "div",
          "heading" => __("Product id", "everexpert-woocommerce-publishers"),
          "param_name" => "product_id",
          "value" => null,
          "description" => __("Product id (post id)", "everexpert-woocommerce-publishers")
        ),
        array(
          "type" => "dropdown",
          "heading" => __("Publisher logo size", "everexpert-woocommerce-publishers"),
          "param_name" => "image_size",
          "admin_label" => true,
          "value" => $available_image_sizes_adapted
        )
      )
    ));
  }

  public function action_woocommerce_single_product_summary()
  {
    $publishers = wp_get_post_terms(get_the_ID(), 'ewp-publisher');

    if (!is_wp_error($publishers)) {

      if (sizeof($publishers) > 0) {

        $show_as = get_option('wc_ewp_admin_tab_publishers_in_single');

        if ($show_as != 'no') {

          do_action('ewp_before_single_product_publishers', $publishers);

          echo '<div class="ewp-single-product-publishers ewp-clearfix">';

          if ($show_as == 'publisher_link') {
            $before_publishers_links = '<span class="ewp-text-before-publishers-links">';
            $before_publishers_links .= apply_filters('ewp_text_before_publishers_links', esc_html__('Publishers', 'everexpert-woocommerce-publishers'));
            $before_publishers_links .= ':</span>';
            echo apply_filters('ewp_html_before_publishers_links', $before_publishers_links);
          }

          foreach ($publishers as $publisher) {
            $publisher_link = get_term_link($publisher->term_id, 'ewp-publisher');
            $attachment_id = get_term_meta($publisher->term_id, 'ewp_publisher_image', 1);

            $image_size = 'thumbnail';
            $image_size_selected = get_option('wc_ewp_admin_tab_publisher_logo_size', 'thumbnail');
            if ($image_size_selected != false) {
              $image_size = $image_size_selected;
            }

            $attachment_html = wp_get_attachment_image($attachment_id, $image_size);

            if (!empty($attachment_html) && $show_as == 'publisher_image' || !empty($attachment_html) && !$show_as) {
              echo '<a href="' . $publisher_link . '" title="' . $publisher->name . '">' . $attachment_html . '</a>';
            } else {
              echo '<a href="' . $publisher_link . '" title="' . esc_html__('View publisher', 'everexpert-woocommerce-publishers') . '">' . $publisher->name . '</a>';
            }
          }
          echo '</div>';

          do_action('ewp_after_single_product_publishers', $publishers);
        }
      }
    }
  }

  public function enqueue_scripts()
  {

    wp_register_script(
      'ewp-lib-slick',
      EWP_PLUGIN_URL . '/assets/lib/slick/slick.min.js',
      array('jquery'),
      '1.8.0',
      false
    );

    wp_register_style(
      'ewp-lib-slick',
      EWP_PLUGIN_URL . '/assets/lib/slick/slick.css',
      array(),
      '1.8.0',
      'all'
    );

    wp_enqueue_style(
      'ewp-styles-frontend',
      EWP_PLUGIN_URL . '/assets/css/styles-frontend.min.css',
      array(),
      EWP_PLUGIN_VERSION,
      'all'
    );

    wp_register_script(
      'ewp-functions-frontend',
      EWP_PLUGIN_URL . '/assets/js/functions-frontend.min.js',
      array('jquery'),
      EWP_PLUGIN_VERSION,
      true
    );

    wp_localize_script('ewp-functions-frontend', 'ewp_ajax_object', array(
      'carousel_prev' => apply_filters('ewp_carousel_prev', '&lt;'),
      'carousel_next' => apply_filters('ewp_carousel_next', '&gt;')
    ));

    wp_enqueue_script('ewp-functions-frontend');
  }

  public function admin_enqueue_scripts($hook)
  {
    $screen = get_current_screen();
    if ($hook == 'edit-tags.php' && $screen->taxonomy == 'ewp-publisher' || $hook == 'term.php' && $screen->taxonomy == 'ewp-publisher') {
      wp_enqueue_media();
    }

    wp_enqueue_style('ewp-styles-admin', EWP_PLUGIN_URL . '/assets/css/styles-admin.min.css', array(), EWP_PLUGIN_VERSION);

    wp_register_script('ewp-functions-admin', EWP_PLUGIN_URL . '/assets/js/functions-admin.min.js', array('jquery'), EWP_PLUGIN_VERSION, true);
    wp_localize_script('ewp-functions-admin', 'ewp_ajax_object_admin', array(
      'ajax_url' => admin_url('admin-ajax.php'),
      'site_url' => site_url(),
      'publishers_url' => admin_url('edit-tags.php?taxonomy=ewp-publisher&post_type=product'),
      'translations' => array(
        'migrate_notice' => esc_html__('¿Start migration?', 'everexpert-woocommerce-publishers'),
        'migrating' => esc_html__('We are migrating the product publishers. ¡Don´t close this window until the process is finished!', 'everexpert-woocommerce-publishers'),
        'dummy_data_notice' => esc_html__('¿Start loading dummy data?', 'everexpert-woocommerce-publishers'),
        'dummy_data' => esc_html__('We are importing the dummy data. ¡Don´t close this window until the process is finished!', 'everexpert-woocommerce-publishers')
      )
    ));
    wp_enqueue_script('ewp-functions-admin');
  }

  public function register_publishers_taxonomy()
  {
    $labels = array(
      'name' => esc_html__('Publishers', 'everexpert-woocommerce-publishers'),
      'singular_name' => esc_html__('Publisher', 'everexpert-woocommerce-publishers'),
      'menu_name' => esc_html__('Publishers', 'everexpert-woocommerce-publishers'),
      'all_items' => esc_html__('All Publishers', 'everexpert-woocommerce-publishers'),
      'edit_item' => esc_html__('Edit Publisher', 'everexpert-woocommerce-publishers'),
      'view_item' => esc_html__('View Publisher', 'everexpert-woocommerce-publishers'),
      'update_item' => esc_html__('Update Publisher', 'everexpert-woocommerce-publishers'),
      'add_new_item' => esc_html__('Add New Publisher', 'everexpert-woocommerce-publishers'),
      'new_item_name' => esc_html__('New Publisher Name', 'everexpert-woocommerce-publishers'),
      'parent_item' => esc_html__('Parent Publisher', 'everexpert-woocommerce-publishers'),
      'parent_item_colon' => esc_html__('Parent Publisher:', 'everexpert-woocommerce-publishers'),
      'search_items' => esc_html__('Search Publishers', 'everexpert-woocommerce-publishers'),
      'popular_items' => esc_html__('Popular Publishers', 'everexpert-woocommerce-publishers'),
      'separate_items_with_commas' => esc_html__('Separate publishers with commas', 'everexpert-woocommerce-publishers'),
      'add_or_remove_items' => esc_html__('Add or remove publishers', 'everexpert-woocommerce-publishers'),
      'choose_from_most_used' => esc_html__('Choose from the most used publishers', 'everexpert-woocommerce-publishers'),
      'not_found' => esc_html__('No publishers found', 'everexpert-woocommerce-publishers')
    );

    $new_slug = get_option('wc_ewp_admin_tab_slug');
    $old_slug = get_option('old_wc_ewp_admin_tab_slug');

    $new_slug = ($new_slug != false) ? $new_slug : 'publisher';
    $old_slug = ($old_slug != false) ? $old_slug : 'null';

    $args = array(
      'hierarchical' => true,
      'labels' => $labels,
      'show_ui' => true,
      'query_var' => true,
      'public' => true,
      'show_admin_column' => true,
      'rewrite' => array(
        'slug' => apply_filters('ewp_taxonomy_rewrite', $new_slug),
        'hierarchical' => true,
        'with_front' => apply_filters('ewp_taxonomy_with_front', true),
        'ep_mask' => EP_PERMALINK
      )
    );

    register_taxonomy('ewp-publisher', array('product'), $args);

    if ($new_slug != false && $old_slug != false && $new_slug != $old_slug) {
      flush_rewrite_rules();
      update_option('old_wc_ewp_admin_tab_slug', $new_slug);
    }
  }

  public function add_publishers_metafields()
  {
    register_meta('term', 'ewp_publisher_image', array($this, 'add_publishers_metafields_sanitize'));
  }

  public function add_publishers_metafields_sanitize($publisher_img)
  {
    return $publisher_img;
  }

  public static function get_publishers($hide_empty = false, $order_by = 'name', $order = 'ASC', $only_featured = false, $ewp_term = false, $only_parents = false)
  {
    $result = array();

    $publishers_args = array('hide_empty' => $hide_empty, 'orderby' => $order_by, 'order' => $order);
    if ($only_featured)
      $publishers_args['meta_query'] = array(array('key' => 'ewp_featured_publisher', 'value' => true));
    if ($only_parents)
      $publishers_args['parent'] = 0;

    $publishers = get_terms('ewp-publisher', $publishers_args);

    foreach ($publishers as $key => $publisher) {

      if ($ewp_term) {
        $publishers[$key] = new EWP_Term($publisher);
      } else {
        $publisher_image_id = get_term_meta($publisher->term_id, 'ewp_publisher_image', true);
        $publisher_banner_id = get_term_meta($publisher->term_id, 'ewp_publisher_banner', true);
        $publisher->publisher_image = wp_get_attachment_image_src($publisher_image_id);
        $publisher->publisher_banner = wp_get_attachment_image_src($publisher_banner_id);
      }
    }

    if (is_array($publishers) && count($publishers) > 0)
      $result = $publishers;

    return $result;
  }

  public static function get_publishers_array($is_select = false)
  {
    $result = array();

    //if is for select input adds default value
    if ($is_select)
      $result[0] = esc_html__('All', 'everexpert-woocommerce-publishers');

    $publishers = get_terms('ewp-publisher', array(
      'hide_empty' => false
    ));

    foreach ($publishers as $publisher) {
      $result[$publisher->term_id] = $publisher->slug;
    }

    return $result;
  }

  public function print_publisher_banner()
  {
    $queried_object = get_queried_object();
    $publisher_banner = get_term_meta($queried_object->term_id, 'ewp_publisher_banner', true);
    $publisher_banner_link = get_term_meta($queried_object->term_id, 'ewp_publisher_banner_link', true);
    $show_banner = get_option('wc_ewp_admin_tab_publisher_banner');
    $show_banner = get_option('wc_ewp_admin_tab_publisher_banner');
    $show_banner_class = (!$show_banner || $show_banner == 'yes') ? 'ewp-before-loop' : 'ewp-after-loop';

    if ($publisher_banner != '') {
      echo '<div class="ewp-publisher-banner ewp-clearfix ' . $show_banner_class . '">';
      if ($publisher_banner_link != '') {
        echo '<a href="' . site_url($publisher_banner_link) . '">' . wp_get_attachment_image($publisher_banner, 'full', false) . '</a>';
      } else {
        echo wp_get_attachment_image($publisher_banner, 'full', false);
      }
      echo '</div>';
    }
  }

  public function print_publisher_desc()
  {
    $queried_object = get_queried_object();
    $show_desc = get_option('wc_ewp_admin_tab_publisher_desc');
    $show_desc = get_option('wc_ewp_admin_tab_publisher_desc');
    $show_desc_class = (!$show_desc || $show_desc == 'yes') ? 'ewp-before-loop' : 'ewp-after-loop';

    if ($queried_object->description != '' && $show_desc !== 'no') {
      echo '<div class="ewp-publisher-description ' . $show_desc_class . '">';
      echo do_shortcode(wpautop($queried_object->description));
      echo '</div>';
    }
  }

  public function print_publisher_banner_and_desc()
  {
    $queried_object = get_queried_object();

    $show_desc = get_option('wc_ewp_admin_tab_publisher_desc');
    $show_desc_class = (!$show_desc || $show_desc == 'yes') ? 'ewp-before-loop' : 'ewp-after-loop';

    $publisher_banner = get_term_meta($queried_object->term_id, 'ewp_publisher_banner', true);
    $publisher_banner_link = get_term_meta($queried_object->term_id, 'ewp_publisher_banner_link', true);

    if ($publisher_banner != '' || $queried_object->description != '' && $show_desc !== 'no') {
      echo '<div class="ewp-publisher-banner-cont ' . $show_desc_class . '">';
      $this->print_publisher_banner();
      $this->print_publisher_desc();
      echo '</div>';
    }
  }

  public static function render_template($name, $folder = '', $data, $private = true)
  {
    //default template
    if ($folder)
      $folder = $folder . '/';
    $template_file = dirname(__DIR__) . '/templates/' . $folder . $name . '.php';

    //theme overrides
    if (!$private) {
      $theme_template_path = get_stylesheet_directory() . '/everexpert-woocommerce-publishers/';
      if (file_exists($theme_template_path . $folder . $name . '.php'))
        $template_file = $theme_template_path . $folder . $name . '.php';
    }

    extract($data);

    ob_start();
    include $template_file;
    return ob_get_clean();
  }

  public function product_duplicate_save($duplicate, $product)
  {
    $product_publishers = wp_get_object_terms($product->get_id(), 'ewp-publisher', array('fields' => 'ids'));
    wp_set_object_terms($duplicate->get_id(), $product_publishers, 'ewp-publisher');
  }

  public function breadcrumbs($crumbs)
  {

    if (is_tax('ewp-publisher')) {

      $publishers_page_id = get_option('wc_ewp_admin_tab_publishers_page_id');

      if (!empty($publishers_page_id) && $publishers_page_id != '-') {

        $cur_publisher = get_queried_object();
        $publisher_ancestors = get_ancestors($cur_publisher->term_id, 'ewp-publisher', 'taxonomy');

        $publisher_page_pos = count($crumbs) - (count($publisher_ancestors) + 2);
        if (is_paged())
          $publisher_page_pos -= 1;

        if (isset($crumbs[$publisher_page_pos][1]))
          $crumbs[$publisher_page_pos][1] = get_page_link($publishers_page_id);
      }
    }

    return $crumbs;
  }

  /**
   *  Redirect if the search matchs with a publishers name
   *  Better search experience
   */
  public function search_by_publisher_name($query)
  {

    if (wp_doing_ajax())
      return;

    if (!is_admin() && $query->is_main_query() && $query->is_search()) {

      $publishers = get_terms(array('taxonomy' => 'ewp-publisher', 'fields' => 'id=>name'));

      if ($match = array_search(strtolower(trim($query->get('s'))), array_map('strtolower', $publishers))) {

        wp_redirect(get_term_link($match));
        exit;
      }
    }
  }
}
