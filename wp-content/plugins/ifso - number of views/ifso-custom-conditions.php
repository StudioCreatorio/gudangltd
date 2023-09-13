<?php
/*
Plugin Name: If-So number of views
Description: Adds number of views conditions to your if-so installation, unaffected by the main plugin's updates
Version: 1.0
Author: If So Plugin
Author URI: http://www.if-so.com/
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

@author: Nick Martianov
*/

if(!defined('WPINC')){
    die();
}

function activate_custom_cond_plugin(){
    if(!defined('IFSO_PLUGIN_BASE_DIR')){
        die('Please activate the If-So plugin first to use this extension!');
    }
}

register_activation_hook( __FILE__, 'activate_custom_cond_plugin' );


define('IFSO_CUSTOM_CONDITIONS_ON',true);
define('IFSO_CUSTOM_CONDITIONS_DIR',__DIR__);

add_action( 'plugins_loaded', function(){
    if(defined('IFSO_PLUGIN_BASE_DIR') && IFSO_CUSTOM_CONDITIONS_ON){
        require_once(__DIR__ . '/ifso-custom-conditions-initializer.class.php');
        $init = new IfsoCustomConditionsInitializer();  //Hooks into the relevant actions and initializes everything for the plugin to function
    }
} );


