<?php

class WP_Shifty_Browser {

      public static function init($settings){
            // Store original cookies in global
            $GLOBALS['__COOKIE'] = $_COOKIE;

            // Handle redirects
            add_filter('wp_redirect', function($location){
                  $location =  add_query_arg('wpshifty-redirected', '1', $location);
                  if (isset($_GET['wpshifty-preview'])){
                        $location =  add_query_arg('wpshifty-preview', $_GET['wpshifty-preview'], $location);
                  }
                  return $location;
            }, PHP_INT_MAX);

            // User role
            if (isset($settings['user_role'])){
                  switch ($settings['user_role']) {
                        case 'not-logged-in':
                              add_filter('determine_current_user', '__return_false', PHP_INT_MAX);
                              $cookie_key = 'wordpress_logged_in_' . (defined('COOKIEHASH') ? COOKIEHASH : md5(site_url()));
                              unset($_COOKIE[$cookie_key]);
                              break;
                        default:
                              add_filter('user_has_cap', function() use ($settings){
                                    return get_role( strtolower($settings['user_role']) )->capabilities;
                              }, PHP_INT_MAX);
                              add_filter('wp_shifty_current_user_role', function() use ($settings){
                                    return $settings['user_role'];
                              });
                              break;
                  }
            }

            // Useragent
            if (isset($settings['useragent']) && !empty($settings['useragent'])){
                  $_SERVER['HTTP_USER_AGENT'] = $settings['useragent'];
            }

            // Custom headers
            if (isset($settings['headers']) && !empty($settings['headers'])){
                  foreach (explode("\n", $settings['headers']) as $header){
                        list($key, $value) = explode(':', $header);
                        $key        = strtoupper(preg_replace('~(-|\s)~', '_', trim($key)));
                        $value      = trim($value);
                        $_SERVER['HTTP_' . $key] = $value;
                  }
            }

            // Custom POST data
            if (isset($settings['postdata']) && !empty($settings['postdata'])){
                  parse_str($settings['postdata'], $post);
                  foreach ($post as $key => $value){
                        $_POST[$key] = $_REQUEST[$key] = $value;
                  }
            }

            // Cookies
            if (isset($settings['cookies']) && !empty($settings['cookies'])){
                  parse_str($settings['cookies'], $cookies);
                  foreach ($cookies as $key => $value){
                        $_COOKIE[$key] = $value;
                  }
            }

            // AJAX action
            if (defined('DOING_AJAX')){
                  $_POST['action'] = $_GET['action'] = $_REQUEST['action'];
            }

      }
}