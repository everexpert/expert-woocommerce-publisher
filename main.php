<?php

if ($active_plugins = get_option('active_plugins', array())) {
  foreach ($active_plugins as $key => $active_plugin) {
    if ($active_plugin == 'everexpert-woocommerce-publishers/main.php') {
      $active_plugins[$key] = str_replace('/main.php', '/everexpert-woocommerce-publishers.php', $active_plugin);
    }
  }
  update_option('active_plugins', $active_plugins);
}