<?php

/**
 * Plugin Name: WP Shifty Loader
 */


function wp_shifty_loader(){
	wp_cookie_constants();
	$plugins = get_option('active_plugins');
	$plugin_file = '%PLUGIN_DIR%wp-shifty.php';
	if (in_array('%PLUGIN_SLUG%', (array)$plugins)){
		if (!class_exists('WP_Shifty') && file_exists($plugin_file)){
			include_once $plugin_file;
		}
	}
}
wp_shifty_loader();
?>
