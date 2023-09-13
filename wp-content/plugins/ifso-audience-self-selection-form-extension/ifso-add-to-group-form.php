<?php
/*
Plugin Name: If-So Self-Selection Form Extension
Description: Allow users to choose the content they will see using a self-selection form
Version: 1.7
Author: If So Plugin
Author URI: http://www.if-so.com/
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

@author: Nick Martianov
*/

if(!defined('WPINC')){
    die();
}

if(!defined('IFSO_ADD_TO_GROUP_FORM_ON')){
    define('IFSO_ADD_TO_GROUP_FORM_ON',true);
    define('IFSO_ADD_TO_GROUP_FORM_DIR',__DIR__);

    add_action( 'plugins_loaded', function(){
        if(defined('IFSO_PLUGIN_BASE_DIR') && IFSO_ADD_TO_GROUP_FORM_ON){
            require_once(__DIR__ . '/lib/plugin-update-checker/plugin-update-checker.php');
            try{
                $myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(      //Check for updates
                    'https://if-so.com/api/plugin-update/json/ifso-aud-self-select-form-extension.json',
                    __FILE__,
                    'ifso-aud-self-select-form-extension'
                );
            }
            catch(Exception $e){
            }

            require_once(__DIR__ . '/ifso-group-form.class.php');
            $form = new \IfSo\Addons\SelectionForm\SelectionForm();
        }
    } );
}



