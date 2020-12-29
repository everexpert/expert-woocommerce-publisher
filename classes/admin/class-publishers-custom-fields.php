<?php

namespace Everexpert_Woocommerce_Publishers\Admin;

defined('ABSPATH') or die('No script kiddies please!');

class Publishers_Custom_Fields
{

  function __construct()
  {
    add_action('ewp-publisher_add_form_fields', array($this, 'add_publishers_metafields_form'));
    add_action('ewp-publisher_edit_form_fields', array($this, 'add_publishers_metafields_form_edit'));
    add_action('edit_ewp-publisher', array($this, 'add_publishers_metafields_save'));
    add_action('create_ewp-publisher', array($this, 'add_publishers_metafields_save'));
  }

  public function add_publishers_metafields_form()
  {
    ob_start();
?>

    <div class="form-field ewp_publisher_cont">
      <label for="ewp_publisher_desc"><?php _e('Description'); ?></label>
      <textarea id="ewp_publisher_description_field" name="ewp_publisher_description_field" rows="5" cols="40"></textarea>
      <p id="publisher-description-help-text"><?php _e('Publisher description for the archive pages. You can include some html markup and shortcodes.', 'everexpert-woocommerce-publishers'); ?></p>
    </div>

    <div class="form-field ewp_publisher_cont">
      <label for="ewp_publisher_image"><?php _e('Publisher logo', 'everexpert-woocommerce-publishers'); ?></label>
      <input type="text" name="ewp_publisher_image" id="ewp_publisher_image" value="">
      <a href="#" id="ewp_publisher_image_select" class="button"><?php esc_html_e('Select image', 'everexpert-woocommerce-publishers'); ?></a>
    </div>

    <div class="form-field ewp_publisher_cont">
      <label for="ewp_publisher_banner"><?php _e('Publisher banner', 'everexpert-woocommerce-publishers'); ?></label>
      <input type="text" name="ewp_publisher_banner" id="ewp_publisher_banner" value="">
      <a href="#" id="ewp_publisher_banner_select" class="button"><?php esc_html_e('Select image', 'everexpert-woocommerce-publishers'); ?></a>
      <p><?php _e('This image will be shown on publisher page', 'everexpert-woocommerce-publishers'); ?></p>
    </div>

    <div class="form-field ewp_publisher_cont">
      <label for="ewp_publisher_banner_link"><?php _e('Publisher banner link', 'everexpert-woocommerce-publishers'); ?></label>
      <input type="text" name="ewp_publisher_banner_link" id="ewp_publisher_banner_link" value="">
      <p><?php _e('This link should be relative to site url. Example: product/product-name', 'everexpert-woocommerce-publishers'); ?></p>
    </div>

    <?php wp_nonce_field(basename(__FILE__), 'ewp_nonce'); ?>

  <?php
    echo ob_get_clean();
  }

  public function add_publishers_metafields_form_edit($term)
  {
    $term_value_image = get_term_meta($term->term_id, 'ewp_publisher_image', true);
    $term_value_banner = get_term_meta($term->term_id, 'ewp_publisher_banner', true);
    $term_value_banner_link = get_term_meta($term->term_id, 'ewp_publisher_banner_link', true);
    ob_start();
  ?>
    <table class="form-table ewp_publisher_cont">
      <tr class="form-field">
        <th>
          <label for="ewp_publisher_desc"><?php _e('Description'); ?></label>
        </th>
        <td>
          <?php wp_editor(html_entity_decode($term->description), 'ewp_publisher_description_field', array('editor_height' => 120)); ?>
          <p id="publisher-description-help-text"><?php _e('Publisher description for the archive pages. You can include some html markup and shortcodes.', 'everexpert-woocommerce-publishers'); ?></p>
        </td>
      </tr>
      <tr class="form-field">
        <th>
          <label for="ewp_publisher_image"><?php _e('Publisher logo', 'everexpert-woocommerce-publishers'); ?></label>
        </th>
        <td>
          <input type="text" name="ewp_publisher_image" id="ewp_publisher_image" value="<?php echo esc_attr($term_value_image); ?>">
          <a href="#" id="ewp_publisher_image_select" class="button"><?php esc_html_e('Select image', 'everexpert-woocommerce-publishers'); ?></a>

          <?php $current_image = wp_get_attachment_image($term_value_image, array('90', '90'), false); ?>
          <?php if (!empty($current_image)) : ?>
            <div class="ewp_publisher_image_selected">
              <span>
                <?php echo wp_kses_post($current_image); ?>
                <a href="#" class="ewp_publisher_image_selected_remove">X</a>
              </span>
            </div>
          <?php endif; ?>

        </td>
      </tr>
      <tr class="form-field">
        <th>
          <label for="ewp_publisher_banner"><?php _e('Publisher banner', 'everexpert-woocommerce-publishers'); ?></label>
        </th>
        <td>
          <input type="text" name="ewp_publisher_banner" id="ewp_publisher_banner" value="<?php echo esc_html($term_value_banner); ?>">
          <a href="#" id="ewp_publisher_banner_select" class="button"><?php esc_html_e('Select image', 'everexpert-woocommerce-publishers'); ?></a>

          <?php $current_image = wp_get_attachment_image($term_value_banner, array('90', '90'), false); ?>
          <?php if (!empty($current_image)) : ?>
            <div class="ewp_publisher_image_selected">
              <span>
                <?php echo wp_kses_post($current_image); ?>
                <a href="#" class="ewp_publisher_image_selected_remove">X</a>
              </span>
            </div>
          <?php endif; ?>

        </td>
      </tr>
      <tr class="form-field">
        <th>
          <label for="ewp_publisher_banner_link"><?php _e('Publisher banner link', 'everexpert-woocommerce-publishers'); ?></label>
        </th>
        <td>
          <input type="text" name="ewp_publisher_banner_link" id="ewp_publisher_banner_link" value="<?php echo esc_html($term_value_banner_link); ?>">
          <p class="description"><?php _e('This link should be relative to site url. Example: product/product-name', 'everexpert-woocommerce-publishers'); ?></p>
          <div id="ewp_publisher_banner_link_result"><?php echo wp_get_attachment_image($term_value_banner_link, array('90', '90'), false); ?></div>
        </td>
      </tr>
    </table>

    <?php wp_nonce_field(basename(__FILE__), 'ewp_nonce'); ?>

<?php
    echo ob_get_clean();
  }

  public function add_publishers_metafields_save($term_id)
  {

    if (!isset($_POST['ewp_nonce']) || !wp_verify_nonce($_POST['ewp_nonce'], basename(__FILE__)))
      return;

    /* ·············· Publisher image ·············· */
    $old_img = get_term_meta($term_id, 'ewp_publisher_image', true);
    $new_img = isset($_POST['ewp_publisher_image']) ? $_POST['ewp_publisher_image'] : '';

    if ($old_img && '' === $new_img)
      delete_term_meta($term_id, 'ewp_publisher_image');

    else if ($old_img !== $new_img)
      update_term_meta($term_id, 'ewp_publisher_image', $new_img);
    /* ·············· /Publisher image ·············· */

    /* ·············· Publisher banner ·············· */
    $old_img = get_term_meta($term_id, 'ewp_publisher_banner', true);
    $new_img = isset($_POST['ewp_publisher_banner']) ? $_POST['ewp_publisher_banner'] : '';

    if ($old_img && '' === $new_img)
      delete_term_meta($term_id, 'ewp_publisher_banner');

    else if ($old_img !== $new_img)
      update_term_meta($term_id, 'ewp_publisher_banner', $new_img);
    /* ·············· /Publisher banner ·············· */

    /* ·············· Publisher banner link ·············· */
    $old_img = get_term_meta($term_id, 'ewp_publisher_banner_link', true);
    $new_img = isset($_POST['ewp_publisher_banner_link']) ? $_POST['ewp_publisher_banner_link'] : '';

    if ($old_img && '' === $new_img)
      delete_term_meta($term_id, 'ewp_publisher_banner_link');

    else if ($old_img !== $new_img)
      update_term_meta($term_id, 'ewp_publisher_banner_link', $new_img);
    /* ·············· /Publisher banner link ·············· */

    /* ·············· Publisher desc ·············· */
    if (isset($_POST['ewp_publisher_description_field'])) {
      $allowed_tags = apply_filters(
        'ewp_description_allowed_tags',
        '<p><span><a><ul><ol><li><h1><h2><h3><h4><h5><h6><pre><strong><em><blockquote><del><ins><img><code><hr>'
      );
      $desc = strip_tags(wp_unslash($_POST['ewp_publisher_description_field']), $allowed_tags);
      global $wpdb;
      $wpdb->update($wpdb->term_taxonomy, ['description' => $desc], ['term_id' => $term_id]);
    }
    /* ·············· /Publisher desc ·············· */
  }
}
