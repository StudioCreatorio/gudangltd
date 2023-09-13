<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

if (!current_user_can('activate_plugins')){
      return;
}

include_once __DIR__ . '/wp-shifty.php';

if (is_multisite()){
      global $wpdb;
      foreach ($wpdb->get_col("SELECT blog_id FROM $wpdb->blogs") as $blog_id){
            switch_to_blog($blog_id);
            WP_Shifty::uninstall();
            restore_current_blog();
      }
}
else {
      WP_Shifty::uninstall();
}

?>
