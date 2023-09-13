<?php

class WP_Shifty_Helper {

      public static function maybe_trailingslashit($string){
            if (preg_match('~/$~',get_option('permalink_structure'))){
                  return trailingslashit($string);
            }
            return $string;
      }

      public static function get_public_post_types($exclude = array()){
            $post_types = array_diff(array_merge(array('page', 'post'), (array)get_post_types(array('publicly_queryable' => true, 'public' => true, 'rewrite' => true))), (array)$exclude);
            asort($post_types);
            return $post_types;
      }

      public static function get_taxonomies(){
            $taxonomies = get_taxonomies(array('public' => true), 'objects');
            usort($taxonomies, function ($a, $b) {
                return strcasecmp($a->name, $b->name);
            });
            return $taxonomies;
      }

      public static function get_resource_path($url){
            $upload_dir = wp_upload_dir();
            return str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $url);
      }

      public static function upload_dir(){
            $upload_dir   = wp_upload_dir();

            return (object) array (
                  'basedir' => trailingslashit($upload_dir['basedir']),
                  'dir' => trailingslashit($upload_dir['basedir']) . 'wp-shifty',
                  'url' => trailingslashit($upload_dir['baseurl']) . 'wp-shifty'
            );
      }

      public static function get_related_files($id){
            $files = array();


            $scenario = json_decode(WP_Shifty::$wpdb->get_var(WP_Shifty::$wpdb->prepare("SELECT settings FROM " . WP_SHIFTY_TABLE . " WHERE id = %d", $id)));

            if (isset($scenario->elements->overwrite)){
                  foreach ((array)$scenario->elements->overwrite as $rule){
                        $files[] = $rule->overwrite;
                  }
            }

            if (isset($scenario->elements->inject)){
                  foreach ((array)$scenario->elements->inject as $rule){
                        $files[] = $rule->inject;
                  }
            }

            return $files;
      }

      public static function get_menu(){
            $_menu = array();
            global $menu, $submenu;

            require_once(ABSPATH . 'wp-admin/menu.php');
            if (!did_action('admin_menu')){
                  do_action('admin_menu');
            }

            foreach ($menu as $menu_element){
                  $title = trim(preg_replace('~<([^>]+)>.*</([^>]+)>~', '', $menu_element[0]));
                  if (empty($title)){
                        continue;
                  }

                  $screen = html_entity_decode($menu_element[2]);
                  if (strpos($screen, '.php') === false){
                        $screen = 'admin.php?' . $screen;
                  }

                  $_menu[$menu_element[2]] = array(
                        'title'     => $title,
                        'screen'    => $screen,
                        'submenu'   => array()
                  );


            }

            foreach ($submenu as $key => $_submenu){
                  foreach ($_submenu as $index =>$submenu_element){
                        $title = trim(preg_replace('~<([^>]+)>.*</([^>]+)>~', '', $submenu_element[0]));
                        $screen = html_entity_decode($submenu_element[2]);
                        if (strpos($screen, '.php') === false){
                              $screen = 'admin.php?' . $screen;
                        }

                        if (isset($_menu[$key]) && $index === 0){
                              $_menu[$key]['screen']= $screen;
                        }
                        if (isset($_menu[$key]) && !empty($title)){
                              $_menu[$key]['submenu'][] = array(
                                    'title'     => $title,
                                    'screen'    => $screen
                              );
                        }
                  }

            }

            return $_menu;
      }

      public static function get_ajax_actions(){
            global $wp_filter;
            $ajax_actions = array();

            foreach ($wp_filter as $filter => $value) {
                  if (preg_match('~^wp_ajax_~', $filter)){
                        $ajax_actions[] = preg_replace('~^wp_ajax_~', '', $filter);
                  }
            }

            sort($ajax_actions);

            return $ajax_actions;
      }

      public static function get_shop_pages(){
            return (array)WP_Shifty::$wpdb->get_col("SELECT option_value as page_id FROM " . WP_Shifty::$wpdb->options . " WHERE option_name LIKE 'woocommerce_%_page_id' AND option_value != ''");
      }

      public static function get_devices(){
            return array(
                  'desktop'   => esc_html__('Desktop', 'wp-shifty'),
                  'phone'     => esc_html__('Phone', 'wp-shifty'),
                  'tablet'    => esc_html__('Tablet', 'wp-shifty'),
                  'android'   => esc_html__('Android', 'wp-shifty'),
                  'ios'       => esc_html__('iOS', 'wp-shifty'),
                  'bot'       => esc_html__('Bot', 'wp-shifty')
            );
      }

      public static function get_script_tag($script){
            return '<script>' . $script . '</script>';
      }

      public static function parse_header($data){
            $parsed = array();
            foreach (explode("\n", $data) as $line){
                  list($key, $value) = explode(':', $line);
                  $parsed[trim($key)] = trim($value);
            }

            return $parsed;
      }

      public static function normalize_url($url){
            if (strpos($url, '//') === 0){
                  return 'https:' . $url;
            }
            else if (strpos($url, '/') === 0){
                  return home_url($url);
            }
            return $url;
      }

      public static function format_bytes($bytes, $decimals = 2) {
          if (empty($bytes)){
                 return 'unknown Bytes';
          }

          if ($bytes === 0){
                 return '0 Bytes';
          }

          $k = 1024;
          $dm = ($bytes < 1024 ? 0 : $decimals);
          $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

          $bytes = abs($bytes);

          $i = floor(log($bytes)/log($k));

          return number_format((float)($bytes / pow($k, $i)), $dm) . ' ' . $sizes[$i];
      }

      public static function is_localhost(){
            return (!isset($_SERVER['REMOTE_ADDR']) || in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1','::1')));
      }

      public static function random_id(){
            return hash('crc32', mt_rand(0,PHP_INT_MAX));
      }

      public static function auth(){
            if (isset($GLOBALS['__COOKIE'][LOGGED_IN_COOKIE])){
                  $user_id = wp_validate_auth_cookie($GLOBALS['__COOKIE'][LOGGED_IN_COOKIE], 'logged_in');
                  return user_can($user_id, 'manage_options');
            }

            return false;
      }
}