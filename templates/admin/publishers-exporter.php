<?php
/**
 * The template for displaying the edit-tags.php exporter/importer
 * @version 1.0.0
 */

 defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
?>

<div class="ewp-publishers-exporter ewp-clearfix">
  <button class="button ewp-publishers-export"><?php esc_html_e('Export publishers', 'everexpert-woocommerce-publishers');?></button>
  <button class="button ewp-publishers-import"><?php esc_html_e('Import publishers', 'everexpert-woocommerce-publishers');?></button>
  <input type="file" class="ewp-publishers-import-file" accept="application/json">
  <p><?php _e( 'This tool allows you to export and import the publishers between different sites using EWP.', 'everexpert-woocommerce-publishers' );?></p>
</div>
