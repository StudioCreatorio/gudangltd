<?php

class WP_Shifty_I18n {

      public static function localize_script(){
            return array(
                  'Core' => esc_html__('Core', 'wp-shifty'),
                  'Unknown' => esc_html__('Unknown', 'wp-shifty'),
                  'Edit' => esc_html__('Edit', 'wp-shifty'),
                  'Restore' => esc_html__('Restore', 'wp-shifty'),
                  'Your changes will be lost if you don’t save them.' => esc_html__('Your changes will be lost if you don’t save them.', 'wp-shifty'),
                  'Unknown error occured. Please refresh the page and try again.' => esc_html__('Unknown error occured. Please refresh the page and try again.', 'wp-shifty'),
                  'Are you sure you want to delete this item?' => esc_html__('Are you sure you want to delete this item?', 'wp-shifty'),
                  'Would you like to disconnect license?' => esc_html__('Would you like to disconnect license?', 'wp-shifty'),
                  'Would you like to keep settings after uninstall WP Shifty?' => esc_html__('Would you like to keep settings after uninstall WP Shifty?', 'wp-shifty'),
            );
      }

}