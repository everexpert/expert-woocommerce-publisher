<?php

namespace Everexpert_Woocommerce_Publishers\Widgets;

use WP_Query;

defined('ABSPATH') or die('No script kiddies please!');

class EWP_Filter_By_Publisher_Widget extends \WP_Widget
{

	function __construct()
	{
		$params = array(
			'description' => __('Recommended for product categories or shop page', 'everexpert-woocommerce-publishers'),
			'name'        => __('Filter products by publisher', 'everexpert-woocommerce-publishers')
		);
		parent::__construct('EWP_Filter_By_Publisher_Widget', '', $params);
	}

	public function form($instance)
	{
		extract($instance);

		$title = (isset($instance['title'])) ? $instance['title'] : esc_html__('Publishers', 'everexpert-woocommerce-publishers');
		$limit = (isset($instance['limit'])) ? $instance['limit'] : 20;
		$hide_submit_btn         = (isset($hide_submit_btn) && $hide_submit_btn == 'on') ? true : false;
		$only_first_level_publishers = (isset($only_first_level_publishers) && $only_first_level_publishers == 'on') ? true : false;
?>

		<p>
			<label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Title:'); ?></label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_html($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
		</p>

		<p>
			<label for="<?php echo esc_attr($this->get_field_id('limit')); ?>">
				<?php echo __('Max number of publishers', 'everexpert-woocommerce-publishers'); ?>
			</label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('limit')); ?>" name="<?php echo esc_html($this->get_field_name('limit')); ?>" type="text" value="<?php echo esc_attr($limit); ?>" />
		</p>

		<p>
			<input type="checkbox" id="<?php echo esc_attr($this->get_field_id('hide_submit_btn')); ?>" name="<?php echo esc_attr($this->get_field_name('hide_submit_btn')); ?>" <?php checked($hide_submit_btn); ?>>
			<label for="<?php echo esc_attr($this->get_field_id('hide_submit_btn')); ?>">
				<?php echo __('Hide filter button', 'everexpert-woocommerce-publishers'); ?>
			</label>
		</p>

		<p>
			<input type="checkbox" id="<?php echo esc_attr($this->get_field_id('only_first_level_publishers')); ?>" name="<?php echo esc_attr($this->get_field_name('only_first_level_publishers')); ?>" <?php checked($only_first_level_publishers); ?>>
			<label for="<?php echo esc_attr($this->get_field_id('only_first_level_publishers')); ?>">
				<?php echo __('Show only first level publishers', 'everexpert-woocommerce-publishers'); ?>
			</label>
		</p>

<?php
	}

	public function update($new_instance, $old_instance)
	{
		$limit = trim(strip_tags($new_instance['limit']));
		$limit = filter_var($limit, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

		$instance = array();
		$instance['title']      		 = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
		$instance['limit']      		 = ($limit != false) ? $limit : $old_instance['limit'];
		$instance['hide_submit_btn'] = (isset($new_instance['hide_submit_btn'])) ? $new_instance['hide_submit_btn'] : '';
		$instance['only_first_level_publishers'] = (isset($new_instance['only_first_level_publishers'])) ? $new_instance['only_first_level_publishers'] : '';
		return $instance;
	}

	public function widget($args, $instance)
	{
		extract($args);
		extract($instance);

		if (!is_tax('ewp-publisher') && !is_product()) {

			$hide_submit_btn = (isset($hide_submit_btn) && $hide_submit_btn == 'on') ? true : false;
			$only_first_level_publishers = (isset($only_first_level_publishers) && $only_first_level_publishers == 'on') ? true : false;

			$show_widget = true;
			$current_products = false;
			if (is_product_taxonomy() || is_shop()) {
				$current_products = $this->current_products_query();
				if (empty($current_products)) $show_widget = false;
			}

			if ($show_widget) {

				$title = (isset($instance['title'])) ? $instance['title'] : esc_html__('Publishers', 'everexpert-woocommerce-publishers');
				$title = apply_filters('widget_title', $title);
				$limit = (isset($instance['limit'])) ? $instance['limit'] : 20;

				echo $args['before_widget'];
				if (!empty($title)) echo $args['before_title'] . $title . $args['after_title'];
				$this->render_widget($current_products, $limit, $hide_submit_btn, $only_first_level_publishers);
				echo $args['after_widget'];
			}
		}
	}

	public function render_widget($current_products, $limit, $hide_submit_btn, $only_first_level_publishers)
	{

		$result_publishers = array();

		if (is_product_taxonomy() || is_shop()) {

			//obtains publishers ids
			if (!empty($current_products)) $result_publishers = $this->get_products_publishers($current_products);

			//excludes the child publishers if needed
			if ($only_first_level_publishers) {
				$result_publishers = $this->exclude_child_publishers($result_publishers);
			}

			if (is_shop()) {
				$cate_url = get_permalink(wc_get_page_id('shop'));
			} else {
				$cate = get_queried_object();
				$cateID = $cate->term_id;
				$cate_url = get_term_link($cateID);
			}
		} else {
			//no product category
			$cate_url = get_permalink(wc_get_page_id('shop'));
			$result_publishers =  get_terms('ewp-publisher', array('hide_empty' => true, 'fields' => 'ids'));
		}

		if ($limit > 0) $result_publishers = array_slice($result_publishers, 0, $limit);

		global $wp;
		$current_url = home_url(add_query_arg(array(), $wp->request));

		if (!empty($result_publishers)) {

			$result_publishers_ordered = array();
			foreach ($result_publishers as $publisher) {
				$publisher = get_term($publisher);
				$result_publishers_ordered[$publisher->name] = $publisher;
			}
			ksort($result_publishers_ordered);

			$result_publishers_ordered = apply_filters('ewp_widget_publisher_filter', $result_publishers_ordered);

			echo \Everexpert_Woocommerce_Publishers\Everexpert_Woocommerce_Publishers::render_template(
				'filter-by-publisher',
				'widgets',
				array('cate_url' => $cate_url, 'publishers' => $result_publishers_ordered, 'hide_submit_btn' => $hide_submit_btn),
				false
			);
		}
	}

	private function exclude_child_publishers($publishers)
	{

		//gets parent for all publishers
		foreach ($publishers as $publisher_key => $publisher) {

			$publisher_o = get_term($publisher, 'ewp-publisher');

			if ($publisher_o->parent) {

				//exclude this child publisher and include the parent
				unset($publishers[$publisher_key]);
				if (!in_array($publisher_o->parent, $publishers)) $publishers[$publisher_key] = $publisher_o->parent;
			}
		}

		//reset keys
		$publishers = array_values($publishers);


		return $publishers;
	}

	private function current_products_query()
	{

		$args = array(
			'posts_per_page' => -1,
			'post_type' => 'product',
			'tax_query' => array(
				array(
					'taxonomy' => 'ewp-publisher',
					'operator' => 'EXISTS'
				)
			),
			'fields' => 'ids',
		);

		$cat = get_queried_object();
		if (is_a($cat, 'WP_Term')) {
			$cat_id 				= $cat->term_id;
			$cat_id_array 	= get_term_children($cat_id, $cat->taxonomy);
			$cat_id_array[] = $cat_id;
			$args['tax_query'][] = array(
				'taxonomy' => $cat->taxonomy,
				'field'    => 'term_id',
				'terms'    => $cat_id_array
			);
		}

		if (get_option('woocommerce_hide_out_of_stock_items') === 'yes') {
			$args['meta_query'] = array(
				array(
					'key'     => '_stock_status',
					'value'   => 'outofstock',
					'compare' => 'NOT IN'
				)
			);
		}

		$wp_query = new WP_Query($args);
		wp_reset_postdata();

		return $wp_query->posts;
	}

	private function get_products_publishers($product_ids)
	{

		$product_ids = implode(',', array_map('intval', $product_ids));

		global $wpdb;

		$publisher_ids = $wpdb->get_col("SELECT DISTINCT t.term_id
			FROM {$wpdb->prefix}terms AS t
			INNER JOIN {$wpdb->prefix}term_taxonomy AS tt
			ON t.term_id = tt.term_id
			INNER JOIN {$wpdb->prefix}term_relationships AS tr
			ON tr.term_taxonomy_id = tt.term_taxonomy_id
			WHERE tt.taxonomy = 'ewp-publisher'
			AND tr.object_id IN ($product_ids)
		");

		return ($publisher_ids) ? $publisher_ids : false;
	}
}
