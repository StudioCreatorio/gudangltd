<?php

class WP_Shifty_Query {

      public static function guess(){
            // CLI
            if (defined('WP_CLI') || isset($_GET['wpshifty-cli'])){
                  return array(
                        'type' => 'cli',
                  );
            }

            // Fallback if request uri is not set, but it is not a CLI request
            if (!isset($_SERVER['REQUEST_URI'])){
                  return array(
                        'type' => 'headless'
                  );
            }

            $current_url = preg_replace('~(\?|&)wpshifty-preview=(\d+)~','',(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
            parse_str(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY), $query_string);

            // CRON
            if (defined('DOING_CRON')){
                  return array(
                        'type' => 'cron',
                        'url' => $current_url,
                        'query_string' => $query_string
                  );
            }

            // AJAX requests
            if (strpos(set_url_scheme($current_url, 'https'), set_url_scheme(admin_url('admin-ajax.php'), 'https')) === 0){
                  return array(
                        'type' => 'ajax',
                        'action' => (isset($_REQUEST['action']) ? $_REQUEST['action'] : '__error__'),
                        'url' => $current_url,
                        'query_string' => $query_string
                  );
            }

            // Admin pages
            if (strpos(set_url_scheme($current_url, 'https'), set_url_scheme(admin_url(), 'https')) === 0){
                  return array(
                        'type' => 'admin',
                        'screen' => basename(parse_url($current_url, PHP_URL_PATH)),
                        'page' => (isset($_GET['page']) ? $_GET['page'] : ''),
                        'url' => $current_url,
                        'query_string' => $query_string
                  );
            }

            // Special pages
            // WooCommerce shop page
            $woocommerce_shop_permalink = self::get_permalink(get_option('woocommerce_shop_page_id'));
            if (!empty($woocommerce_shop_permalink) && strpos($current_url, $woocommerce_shop_permalink) === 0){
                  return array(
                        'type' => 'single',
                        'post_type' => 'page',
                        'id'   => get_option('woocommerce_shop_page_id'),
                        'url' => $current_url,
                        'query_string' => $query_string
                  );
            }

            // Post Archive
            $page_for_posts = get_option('page_for_posts');
            if (!empty($page_for_posts) && (strpos($current_url, self::get_permalink($page_for_posts)) === 0)){
                  return array(
                        'type'      => 'archive',
                        'post_type' => 'post',
                        'url' => $current_url,
                        'query_string' => $query_string
                  );
            }

            // Check rewrites
            $rewrites = get_option('rewrite_rules');
            if (!empty($rewrites)){
                  $path             = parse_url($current_url, PHP_URL_PATH);
                  $found_match      = '';
                  foreach ((array)$rewrites as $regex => $match){
                        if (preg_match('#' . $regex . '#', $path, $matches)){
                              $found_match = str_replace('index.php?', '', $match);

                              parse_str($found_match, $matched_params);

                              if (!empty($matched_params)){
                                    // Author
                                    if (isset($matched_params['author_name'])){
                                          $author_id = WP_Shifty::$wpdb->get_var(WP_Shifty::$wpdb->prepare("SELECT ID FROM " . WP_Shifty::$wpdb->prefix . "users WHERE user_login = %s", $matches[1]));
                                          if (!empty($author_id)){
                                                return array(
                                                      'type' => 'author',
                                                      'id'   => $author_id,
                                                      'url' => $current_url,
                                                      'query_string' => $query_string
                                                );
                                          }
                                          else {
                                                return array(
                                                      'type' => '404',
                                                      'url' => $current_url,
                                                      'query_string' => $query_string
                                                );
                                          }
                                    }

                                    // Post category
                                    if (isset($matched_params['category_name']) && self::is_term_exists($matches[1])){
                                          return array(
                                                'type'      => 'category',
                                                'id'        => $matches[1],
                                                'post_type' => 'post',
                                                'url' => $current_url,
                                                'query_string' => $query_string
                                          );
                                    }

                                    // Post tag
                                    if (isset($matched_params['tag'])){
                                          return array(
                                                'type' => 'post_tag',
                                                'id'   => $matches[1],
                                                'post_type' => 'post',
                                                'url' => $current_url,
                                                'query_string' => $query_string
                                          );
                                    }

                                    // Search
                                    if (isset($matched_params['s']) || isset($_GET['s'])){
                                          return array(
                                                'type'      => 'search',
                                                'query'     => $matches[1],
                                                'url' => $current_url,
                                                'query_string' => $query_string
                                          );
                                    }

                                    // WooCommerce Product Single
                                    if ($found_match == 'product=$matches[1]'){
                                            return array(
                                                  'type' => 'single',
                                                  'post_type' => 'product',
                                                  'url' => $current_url,
                                                  'query_string' => $query_string
                                            );
                                    }

                                    // CPT single
                                    if (preg_match('~^([^=]*)=\$matches\[1\]&page=\$matches\[2\]$~', $found_match, $_matches)){
                                          if (!in_array($_matches[1], array('pagename', 'name'))){
                                                return array(
                                                      'type' => 'single',
                                                      'post_type' => $_matches[1],
                                                      'url' => $current_url,
                                                      'query_string' => $query_string
                                                );
                                          }
                                    }

                                    // CPT Archive
                                    if (preg_match('~^post_type=([^&]*)$~', $found_match, $_matches)){
                                          return array(
                                                'type' => 'archive',
                                                'post_type' => $_matches[1],
                                                'url' => $current_url,
                                                'query_string' => $query_string
                                          );
                                    }
                              }
                        }
                  }
            }

            // Page
            $maybe_id = self::url_to_postid($current_url);
            if (!empty($maybe_id)){
                  return array(
                        'type'      => 'single',
                        'post_type' => get_post_type($maybe_id),
                        'id'        => $maybe_id,
                        'url' => $current_url,
                        'query_string' => $query_string
                  );
            }

            return array(
                  'type' => '404',
                  'url' => $current_url,
                  'query_string' => $query_string
            );
      }

      public static function url_to_postid($url) {
            $url = urldecode($url);

      	// First, check to see if there is a 'p=N' or 'page_id=N' to match against.
      	if ( preg_match( '#[?&](p|page_id|attachment_id)=(\d+)#', $url, $values ) ) {
      		$id = absint( $values[2] );
      		if ( $id ) {
      			return $id;
      		}
      	}

            if ( trim( preg_replace('~\?(.*)$~', '' ,$url), '/' ) === home_url() && 'page' === get_option( 'show_on_front' ) ) {
                  $page_on_front = get_option( 'page_on_front' );
                  return (int) $page_on_front;
            }

            // Parse path
            $path = explode('/',trim(parse_url($url, PHP_URL_PATH), '/'));

            $post_name = sanitize_title(array_pop($path));

            $page_id = WP_Shifty::$wpdb->get_var("SELECT ID FROM " . WP_Shifty::$wpdb->prefix . "posts WHERE post_name = '{$post_name}' AND post_type IN ('page', 'post')");

            if (!empty($page_id)){
                  return $page_id;
            }

      	return 0;
      }

      public static function get_permalink($post_id){
            // Homepage
            if ('page' === get_option( 'show_on_front' ) && $post_id == get_option( 'page_on_front' )) {
                  return home_url();
            }

            $post_name = WP_Shifty::$wpdb->get_var(WP_Shifty::$wpdb->prepare("SELECT post_name FROM " . WP_Shifty::$wpdb->prefix . "posts WHERE ID = %d", $post_id));
            if (!empty($post_name)){
                  return home_url($post_name);
            }
            return false;
      }

      public static function current_user_role(){
            $cookie_key = 'wordpress_logged_in_' . (defined('COOKIEHASH') ? COOKIEHASH : md5(site_url()));
            if (!isset($_COOKIE[$cookie_key])){
                  return 'not-logged-in';
            }

            $cookie_parts = explode('|', $_COOKIE[$cookie_key]);
            if ( count( $cookie_parts ) !== 4 ) {
                return false;
            }

            $roles = maybe_unserialize(WP_Shifty::$wpdb->get_var(WP_Shifty::$wpdb->prepare("SELECT " . WP_Shifty::$wpdb->usermeta . ".meta_value FROM ". WP_Shifty::$wpdb->users . " LEFT JOIN " . WP_Shifty::$wpdb->usermeta . " ON " . WP_Shifty::$wpdb->users . ".ID = " . WP_Shifty::$wpdb->usermeta . ".user_id WHERE " . WP_Shifty::$wpdb->users . ".user_login = %s AND meta_key = '" . WP_Shifty::$wpdb->prefix . "capabilities'", $cookie_parts[0])));
            if (!empty($roles)){
                  $keys = array_keys($roles);
                  return apply_filters('wp_shifty_current_user_role', array_shift($keys));
            }

            return false;
      }

      public static function is_term_exists($slug){
            return ((int)WP_Shifty::$wpdb->get_var(WP_Shifty::$wpdb->prepare("SELECT COUNT(*) FROM " . WP_Shifty::$wpdb->terms . " WHERE slug = %s", $slug)) > 0);
      }

      public static function is_json(){
		$headers = headers_list();
		foreach ($headers as $header){
			if (preg_match('~Content-Type: application/json~',$header)){
				return true;
			}
		}
            return false;
      }

}