<?php

namespace Everexpert_Woocommerce_Publishers;

use WP_Error, WP_REST_Server;

defined('ABSPATH') or die('No script kiddies please!');

class EWP_API_Support
{

  private $namespaces = array("wc/v1", "wc/v2", "wc/v3");
  private $base = 'publishers';

  function __construct()
  {
    add_action('rest_api_init', array($this, 'register_endpoints'));
    add_action('rest_api_init', array($this, 'register_fields'));
  }

  /**
   * Registers the endpoint for all possible $namespaces
   */
  public function register_endpoints()
  {
    foreach ($this->namespaces as $namespace) {
      register_rest_route($namespace, '/' . $this->base, array(
        array(
          'methods'  => WP_REST_Server::READABLE,
          'callback' => function () {
            return rest_ensure_response(
              Everexpert_Woocommerce_Publishers::get_publishers()
            );
          },
          'permission_callback' => '__return_true'
        ),
        array(
          'methods'  => WP_REST_Server::CREATABLE,
          'callback'  => array($this, 'create_publisher'),
          'permission_callback' => function () {
            return current_user_can('manage_options');
          }

        ),
        array(
          'methods'   => WP_REST_Server::DELETABLE,
          'callback'  => array($this, 'delete_publisher'),
          'permission_callback' => function () {
            return current_user_can('manage_options');
          }
        )
      ));
    }
  }

  public function delete_publisher($request)
  {
    foreach ($request['publishers'] as $publisher) {
      $delete_result = wp_delete_term($publisher, 'ewp-publisher');
      if (is_wp_error($delete_result)) return $delete_result;
    }
    return true;
  }

  public function create_publisher($request)
  {
    $new_publisher = wp_insert_term($request['name'], 'ewp-publisher', array('slug' => $request['slug'], 'description' => $request['description']));
    if (!is_wp_error($new_publisher)) {
      return array('id' => $new_publisher['term_id'], 'name' => $request['name'], 'slug' => $request['slug'], 'description' => $request['description']);
    } else {
      return $new_publisher;
    }
  }

  /**
   * Entry point for all rest field settings
   */
  public function register_fields()
  {
    register_rest_field('product', 'publishers', array(
      'get_callback'    => array($this, "get_callback"),
      'update_callback' => array($this, "update_callback"),
      'schema'          => $this->get_schema(),
    ));
  }

  /**
   * Returns the schema of the "publishers" field on the /product route
   * To attach a publisher to a product just append a "publishers" key containing an array of publisher id's
   * An empty array wil detach all publishers.
   * @return array
   */
  public function get_schema()
  {
    return array(
      'description' => __('Product publishers', 'everexpert-woocommerce-publishers'),
      'type' => 'array',
      'items' => array(
        "type" => "integer"
      ),
      'context' => array("view", "edit")
    );
  }

  /**
   * Returns all attached publishers to a GET request to /products(/id)
   * @param $product
   * @return array|\WP_Error
   */
  public function get_callback($product)
  {
    $publishers = wp_get_post_terms($product['id'], 'ewp-publisher');

    $result_publishers_array = array();
    foreach ($publishers as $publisher) {
      $result_publishers_array[] = array(
        'id'   => $publisher->term_id,
        'name' => $publisher->name,
        'slug' => $publisher->slug
      );
    }

    return $result_publishers_array;
  }

  /**
   * Entry point for an update call
   * @param $publishers
   * @param $product
   */
  public function update_callback($publishers, $product)
  {
    $this->remove_publishers($product);
    $this->add_publishers($publishers, $product);
  }


  /**
   * Detaches all publishers from a product
   * @param \WC_Product $product
   */
  private function remove_publishers($product)
  {
    $publishers = wp_get_post_terms($product->get_id(), 'ewp-publisher');
    if (!empty($publishers)) {
      wp_set_post_terms($product->get_id(), array(), 'ewp-publisher');
    }
  }

  /**
   * Attaches the given publishers to a product. Earlier attached publishers, not in this array, will be removed
   * @param array $publishers
   * @param \WC_Product $product
   */
  private function add_publishers($publishers, $product)
  {
    wp_set_post_terms($product->get_id(), $publishers, "ewp-publisher");
  }
}
