<?php

class WP_Shifty_Scenario {

      public static function get_list(){
            $scenarios = WP_Shifty::$wpdb->get_results("SELECT id, settings, status FROM " . WP_SHIFTY_TABLE . " ORDER BY id DESC");
            foreach ($scenarios as $key => $value) {
                  $scenarios[$key]->settings = json_decode($value->settings, true);
            }

            return $scenarios;
      }

      public static function get_condition_summary($conditions = array(), $context = 'editor'){
            global $wp_roles;
            $summary    = '';

            $condition_array = array(
                  'apply'     => array(),
                  'if'        => array(),
                  'except'    => array(),
            );

            $urls = array(
                  'apply' => array(),
                  'except' => array()
            );

            foreach ((array)$conditions as $condition_id => $condition){
                  switch ($condition['type']){
                        case 'page':
                              $kind = (isset($condition['is-exception']) && $condition['is-exception'] == 'on' ? 'except' : 'apply');
                              if (isset($condition['pages']) && !empty($condition['pages'])){
                                    $condition_labels = array();
                                    foreach ((array)$condition['pages'] as $page_id){
                                          $condition_labels[] = '<span class="wpshifty-condition-tag wpshifty-condition-'. $kind .'" data-condition-editor-id="' . $condition_id . '">' . sprintf(esc_html__('on %s', 'wp-shifty'), esc_html(get_the_title($page_id))) . '</span>';
                                          $urls[$kind][] = get_permalink($page_id);
                                    }
                                    if (count($condition['pages']) < 3) {
                                          $condition_array[$kind][] = implode(esc_html__(' OR ', 'wp-shifty'), (array)$condition_labels);
                                    }
                                    else {
                                          $condition_array[$kind][] = '<span class="wpshifty-condition-tag wpshifty-condition-'. $kind .'" data-condition-editor-id="' . $condition_id . '">' . sprintf(esc_html__('on %d pages', 'wp-shifty'), count($condition['pages'])) . '</span>';
                                    }
                              }
                              else {
                                    $condition_array[$kind][] = '<span class="wpshifty-condition-tag wpshifty-condition-empty" data-condition-editor-id="' . $condition_id . '">' . esc_html__('empty rule', 'wp-shifty') . '</span>';
                              }
                              break;
                        case 'post-type':
                              $kind = (isset($condition['is-exception']) && $condition['is-exception'] == 'on' ? 'except' : 'apply');
                              $limit = ($kind == 'except' ? '' : ' LIMIT 5');
                              if (isset($condition['post-types']) && !empty($condition['post-types'])){
                                    $condition_labels = array();
                                    foreach ((array)$condition['post-types'] as $post_type){
                                          $condition_labels[] = '<span class="wpshifty-condition-tag wpshifty-condition-'. $kind .'" data-condition-editor-id="' . $condition_id . '">' . sprintf(esc_html__('on %s pages', 'wp-shifty'), esc_html(get_post_type_object($post_type)->labels->singular_name)) . '</span>';
                                          $samples = WP_Shifty::$wpdb->get_col(WP_Shifty::$wpdb->prepare("SELECT ID FROM " . WP_Shifty::$wpdb->prefix . "posts WHERE post_type = %s AND post_status = 'publish' {$limit}", $post_type));
                                          foreach ($samples as $sample){
                                                $urls[$kind][] = get_permalink($sample);
                                          }
                                    }
                                    if (count($condition['post-types']) < 3) {
                                          $condition_array[$kind][] = implode(esc_html__(' OR ', 'wp-shifty'), (array)$condition_labels);
                                    }
                                    else {
                                          $condition_array[$kind][] = '<span class="wpshifty-condition-tag wpshifty-condition-'. $kind .'" data-condition-editor-id="' . $condition_id . '">' . sprintf(esc_html__('on %d post types', 'wp-shifty'), count($condition['post-types'])) . '</span>';
                                    }
                              }
                              else {
                                    $condition_array[$kind][] = '<span class="wpshifty-condition-tag wpshifty-condition-empty" data-condition-editor-id="' . $condition_id . '">' . esc_html__('empty rule', 'wp-shifty') . '</span>';
                              }
                              break;
                        case 'archive':
                              $kind = (isset($condition['is-exception']) && $condition['is-exception'] == 'on' ? 'except' : 'apply');
                              if (isset($condition['archive']) && !empty($condition['archive'])){
                                    $condition_labels = array();
                                    foreach ((array)$condition['archive'] as $tax_name){
                                          $tax = get_taxonomy($tax_name);
                                          $condition_labels[] = '<span class="wpshifty-condition-tag wpshifty-condition-'. $kind .'" data-condition-editor-id="' . $condition_id . '">' . sprintf(esc_html__('on %s archive', 'wp-shifty'), esc_html($tax->label)) . '</span>';

                                          foreach (get_terms($tax_name) as $term){
                                                $urls[$kind][] = get_term_link($term);
                                          }
                                    }
                                    if (count($condition['archive']) < 3) {
                                          $condition_array[$kind][] = implode(esc_html__(' OR ', 'wp-shifty'), (array)$condition_labels);
                                    }
                                    else {
                                          $condition_array[$kind][] = '<span class="wpshifty-condition-tag wpshifty-condition-'. $kind .'" data-condition-editor-id="' . $condition_id . '">' . sprintf(esc_html__('on %d archive', 'wp-shifty'), count($condition['archive'])) . '</span>';
                                    }
                              }
                              else {
                                    $condition_array[$kind][] = '<span class="wpshifty-condition-tag wpshifty-condition-empty" data-condition-editor-id="' . $condition_id . '">' . esc_html__('empty rule', 'wp-shifty') . '</span>';
                              }
                              break;
                        case 'author':
                              $kind = (isset($condition['is-exception']) && $condition['is-exception'] == 'on' ? 'except' : 'apply');
                              $condition_array[$kind][] = '<span class="wpshifty-condition-tag wpshifty-condition-' . $kind . '" data-condition-editor-id="' . $condition_id . '">' . esc_html__('on author archive', 'wp-shifty') . '</span>';
                              $authors = WP_Shifty::$wpdb->get_col("SELECT post_author FROM " . WP_Shifty::$wpdb->posts . " GROUP BY post_author ORDER BY COUNT(ID)");
                              foreach ($authors as $author_id) {
                                    $urls[$kind][] = get_author_posts_url($author_id);
                              }
                              break;
                        case 'search':
                              $kind = (isset($condition['is-exception']) && $condition['is-exception'] == 'on' ? 'except' : 'apply');
                              $condition_array[$kind][] = '<span class="wpshifty-condition-tag wpshifty-condition-' . $kind . '" data-condition-editor-id="' . $condition_id . '">' . esc_html__('on search pages', 'wp-shifty') . '</span>';
                              $titles = WP_Shifty::$wpdb->get_var("SELECT GROUP_CONCAT(post_title SEPARATOR ' ') FROM " . WP_Shifty::$wpdb->posts . " WHERE post_status = 'publish' LIMIT 100");
                              $words = array_count_values(array_map(strtolower, explode(' ',$titles)));
                              asort($words);
                              $urls[$kind][] = add_query_arg(array('s' => array_pop(array_keys($words))), home_url());
                              break;
                        case 'everywhere':
                              $condition_array['apply'][] = '<span class="wpshifty-condition-tag wpshifty-condition-apply" data-condition-editor-id="' . $condition_id . '">' . esc_html__('everywhere', 'wp-shifty') . '</span>';
                              $limit = ' LIMIT 5';
                              foreach (WP_Shifty_Helper::get_public_post_types() as $post_type){
                                    $samples = WP_Shifty::$wpdb->get_col(WP_Shifty::$wpdb->prepare("SELECT ID FROM " . WP_Shifty::$wpdb->prefix . "posts WHERE post_type = %s AND post_status = 'publish' {$limit}", $post_type));
                                    foreach ($samples as $sample){
                                          $urls['apply'][] = get_permalink($sample);
                                    }
                              }

                              array_unshift($urls['apply'], WP_Shifty_Helper::maybe_trailingslashit(home_url()));
                              break;
                        case 'frontend':
                              $kind = (isset($condition['is-exception']) && $condition['is-exception'] == 'on' ? 'except' : 'apply');
                              $limit = ($kind == 'except' ? '' : ' LIMIT 5');
                              $condition_array[$kind][] = '<span class="wpshifty-condition-tag wpshifty-condition-' . $kind . '" data-condition-editor-id="' . $condition_id . '">' . esc_html__('on frontend', 'wp-shifty') . '</span>';
                              foreach (WP_Shifty_Helper::get_public_post_types() as $post_type){
                                    $samples = WP_Shifty::$wpdb->get_col(WP_Shifty::$wpdb->prepare("SELECT ID FROM " . WP_Shifty::$wpdb->prefix . "posts WHERE post_type = %s AND post_status = 'publish' {$limit}", $post_type));
                                    foreach ($samples as $sample){
                                          $urls[$kind][] = get_permalink($sample);
                                    }
                              }
                              array_unshift($urls[$kind], home_url());
                              break;
                        case 'frontpage':
                              $kind = (isset($condition['is-exception']) && $condition['is-exception'] == 'on' ? 'except' : 'apply');
                              $condition_array[$kind][] = '<span class="wpshifty-condition-tag wpshifty-condition-' . $kind . '" data-condition-editor-id="' . $condition_id . '">' . esc_html__('on frontpage', 'wp-shifty') . '</span>';
                              $urls[$kind][] = home_url();
                              break;
                        case 'url':
                              $kind = (isset($condition['is-exception']) && $condition['is-exception'] == 'on' ? 'except' : 'apply');
                              switch($condition['match']){
                                    case 'exact':
                                          if ($context == 'dashboard'){
                                                $condition_array[$kind][] = '<span class="wpshifty-condition-tag wpshifty-condition-' . $kind . '" data-condition-editor-id="' . $condition_id . '">' . sprintf(esc_html__('on %s', 'wp-shifty'), $condition['url']) . '</span>';
                                          }
                                          else {
                                                $condition_array[$kind][] = '<span class="wpshifty-condition-tag wpshifty-condition-' . $kind . '" data-condition-editor-id="' . $condition_id . '">' . esc_html__('on specific URL', 'wp-shifty') . '</span>';
                                          }
                                          if (strpos($condition['url'], home_url()) === 0){
                                                $urls[$kind][] = $condition['url'];
                                          }
                                          else {
                                                $urls[$kind][] = home_url($condition['url']);
                                          }
                                          break;
                                    case 'partial':
                                    case 'regex':
                                          $posts = get_posts(array('posts_per_page' => -1, 'post_type' => 'any'));
                                          $samples = 0;
                                          foreach ($posts as $post) {
                                                $permalink = parse_url(get_permalink($post), PHP_URL_PATH);
                                                if (($condition['match'] == 'partial' && strpos($permalink, $condition['url']) !== false) || $condition['match'] == 'regex' && preg_match('~' . str_replace('~','\~', $condition['url']) . '~i', $permalink)){
                                                      $urls[$kind][] = $permalink;
                                                      $samples++;
                                                }

                                                if ($samples > 5) {
                                                      break;
                                                }
                                          }
                                          if ($context == 'dashboard'){
                                                switch ($condition['match']){
                                                      case 'partial':
                                                            $condition_array[$kind][] = '<span class="wpshifty-condition-tag wpshifty-condition-' . $kind . '" data-condition-editor-id="' . $condition_id . '">' . sprintf(esc_html__('on URLs which contains "%s"', 'wp-shifty'), $condition['url']) . '</span>';
                                                            break;
                                                      case 'regex':
                                                            $condition_array[$kind][] = '<span class="wpshifty-condition-tag wpshifty-condition-' . $kind . '" data-condition-editor-id="' . $condition_id . '">' . sprintf(esc_html__('on URLs which matches "%s"', 'wp-shifty'), $condition['url']) . '</span>';
                                                            break;
                                                }
                                          }
                                          else {
                                                $condition_array[$kind][] = '<span class="wpshifty-condition-tag wpshifty-condition-' . $kind . '" data-condition-editor-id="' . $condition_id . '">' . esc_html__('on specific URLs', 'wp-shifty') . '</span>';
                                          }
                                          break;
                              }
                              break;
                        case 'admin':
                              $kind = (isset($condition['is-exception']) && $condition['is-exception'] == 'on' ? 'except' : 'apply');
                              if (isset($condition['pages']) && !empty($condition['pages'])){
                                    $condition_labels = array();
                                    $menu = array();
                                    foreach (WP_Shifty_Helper::get_menu() as $menu_element){
                                          $menu[$menu_element['screen']] = array(
                                                'title' => $menu_element['title'],
                                                'url' => admin_url($menu_element['screen'])
                                          );
                                          foreach ($menu_element['submenu'] as $submenu_element) {
                                                $menu[$submenu_element['screen']] = array(
                                                      'title' => $submenu_element['title'],
                                                      'url' => admin_url(strpos($submenu_element['screen'], '.php') !== false ? $submenu_element['screen'] : $menu_element['screen'] . '?page=' . $submenu_element['screen'])
                                                );

                                                 $menu_element['title'] . ' &gt; ' . $submenu_element['title'];
                                          }
                                    }

                                    foreach ((array)$condition['pages'] as $screen){
                                          $condition_labels[] = '<span class="wpshifty-condition-tag wpshifty-condition-'. $kind .'" data-condition-editor-id="' . $condition_id . '">' . sprintf(esc_html__('on %s', 'wp-shifty'), esc_html($menu[$screen]['title'])) . '</span>';
                                          $urls[$kind][] = $menu[$screen]['url'];
                                    }
                                    if (count($condition['pages']) < 3) {
                                          $condition_array[$kind][] = implode(esc_html__(' OR ', 'wp-shifty'), (array)$condition_labels);
                                    }
                                    else {
                                          $condition_array[$kind][] = '<span class="wpshifty-condition-tag wpshifty-condition-'. $kind .'" data-condition-editor-id="' . $condition_id . '">' . sprintf(esc_html__('on %d admin pages', 'wp-shifty'), count($condition['pages'])) . '</span>';
                                    }
                              }
                              else {
                                    $condition_array[$kind][] = '<span class="wpshifty-condition-tag wpshifty-condition-empty" data-condition-editor-id="' . $condition_id . '">' . esc_html__('empty rule', 'wp-shifty') . '</span>';
                              }
                              break;
                        case 'ajax':
                              $kind = (isset($condition['is-exception']) && $condition['is-exception'] == 'on' ? 'except' : 'apply');
                              switch($condition['match']){
                                    case 'exact':
                                          $condition_array[$kind][] = '<span class="wpshifty-condition-tag wpshifty-condition-' . $kind . '" data-condition-editor-id="' . $condition_id . '">' . sprintf(esc_html__('for %s AJAX', 'wp-shifty'), $condition['action']) . '</span>';
                                          $urls[$kind][] = admin_url(add_query_arg('action', $condition['action'], 'admin-ajax.php'));
                                          break;
                                    case 'partial':
                                    case 'regex':
                                          $samples = 0;
                                          foreach (WP_Shifty_Helper::get_ajax_actions() as $action) {
                                                if (($condition['match'] == 'partial' && strpos($action, $condition['action']) !== false) || $condition['match'] == 'regex' && preg_match('~' . str_replace('~','\~', $condition['action']) . '~i', $action)){
                                                      $urls[$kind][] = admin_url(add_query_arg('action', $action, 'admin-ajax.php'));
                                                      $samples++;
                                                }

                                                if ($samples > 5) {
                                                      break;
                                                }
                                          }
                                          $condition_array[$kind][] = '<span class="wpshifty-condition-tag wpshifty-condition-' . $kind . '" data-condition-editor-id="' . $condition_id . '">' . esc_html__('for specific AJAX requests', 'wp-shifty') . '</span>';
                                          break;
                              }
                              break;



                              $kind = (isset($condition['is-exception']) && $condition['is-exception'] == 'on' ? 'except' : 'apply');
                              $condition_array[$kind][] = '<span class="wpshifty-condition-tag wpshifty-condition-' . $kind . '" data-condition-editor-id="' . $condition_id . '">' . sprintf(esc_html__('for %s ajax', 'wp-shifty'), $condition['action']) . '</span>';
                              $urls[$kind][] = admin_url(add_query_arg('action', $condition['action'], 'admin-ajax.php'));
                              break;
                        case 'shop':
                              $kind = (isset($condition['is-exception']) && $condition['is-exception'] == 'on' ? 'except' : 'apply');
                              $condition_array[$kind][] = '<span class="wpshifty-condition-tag wpshifty-condition-' . $kind . '" data-condition-editor-id="' . $condition_id . '">' . esc_html__('on shop pages', 'wp-shifty') . '</span>';
                              foreach (WP_Shifty_Helper::get_shop_pages() as $page_id) {
                                    $urls[$kind][] = get_permalink($page_id);
                              }
                              break;
                        case 'user':
                              $kind = (isset($condition['is-exception']) && $condition['is-exception'] == 'on' ? 'except' : 'if');
                              if (isset($condition['roles']) && !empty($condition['roles'])){
                                    $condition_labels = array();
                                    if (count($condition['roles']) < 3) {
                                          foreach ((array)$condition['roles'] as $role) {
                                                $role_name = (isset($wp_roles->roles[$role]) ? $wp_roles->roles[$role]['name'] : __('not logged in', 'wp-shifty'));
                                                $condition_labels[] = '<span class="wpshifty-condition-tag wpshifty-condition-' . $kind . '" data-condition-editor-id="' . $condition_id . '">' . sprintf(esc_html__('user is %s', 'wp-shifty'), $role_name) . '</span>';
                                          }
                                          $condition_array[$kind][] = implode(esc_html__(' OR ', 'wp-shifty'), (array)$condition_labels);
                                    }
                                    else {
                                          $condition_array[$kind][] = '<span class="wpshifty-condition-tag wpshifty-condition-'. $kind .'" data-condition-editor-id="' . $condition_id . '">' . sprintf(esc_html__('user is ... (%d roles)', 'wp-shifty'), count($condition['roles'])) . '</span>';
                                    }
                              }
                              else {
                                    $condition_array[$kind][] = '<span class="wpshifty-condition-tag wpshifty-condition-empty" data-condition-editor-id="' . $condition_id . '">' . esc_html__('empty rule', 'wp-shifty') . '</span>';
                              }
                              $urls[$kind][] = trailingslashit(home_url());
                              break;
                        case 'query':
                              $kind = (isset($condition['is-exception']) && $condition['is-exception'] == 'on' ? 'except' : 'if');
                              if (isset($condition['key'])){
                                    $condition_array[$kind][] = '<span class="wpshifty-condition-tag wpshifty-condition-'. $kind .'" data-condition-editor-id="' . $condition_id . '">' . esc_html__('query string match', 'wp-shifty') . '</span>';
                                    switch($condition['key-match']){
                                          case 'exact':
                                                $key = $condition['key'];
                                                break;
                                          case 'partial':
                                                $key = 'test-' . $condition['key'];
                                                break;
                                          case 'regex':
                                                $key = WP_Shifty_Regex::reverse($condition['key']);
                                                break;
                                    }

                                    switch($condition['value-match']){
                                          case 'exact':
                                                $value = $condition['value'];
                                                break;
                                          case 'partial':
                                                $value = 'test-' . $condition['value'];
                                                break;
                                          case 'regex':
                                                $value = WP_Shifty_Regex::reverse($condition['value']);
                                                break;
                                    }
                              }
                              else {
                                    $condition_array[$kind][] = '<span class="wpshifty-condition-tag wpshifty-condition-empty" data-condition-editor-id="' . $condition_id . '">' . esc_html__('empty rule', 'wp-shifty') . '</span>';
                              }
                              $urls[$kind][] = add_query_arg($key, $value, site_url());
                              break;
                        case 'post-data':
                              $kind = (isset($condition['is-exception']) && $condition['is-exception'] == 'on' ? 'except' : 'if');
                              if (isset($condition['data']) && !empty($condition['data'])){
                                    parse_str($condition['data'], $test);
                                    if (empty($test)){
                                          $condition_array[$kind][] = '<span class="wpshifty-condition-tag wpshifty-condition-error" data-condition-editor-id="' . $condition_id . '">' . esc_html__('Syntax error', 'wp-shifty') . '</span>';
                                    }
                                    else {
                                          $condition_array[$kind][] = '<span class="wpshifty-condition-tag wpshifty-condition-' . $kind . '" data-condition-editor-id="' . $condition_id . '">' . esc_html__('post data match', 'wp-shifty') . '</span>';
                                    }
                              }
                              else {
                                    $condition_array[$kind][] = '<span class="wpshifty-condition-tag wpshifty-condition-empty" data-condition-editor-id="' . $condition_id . '">' . esc_html__('empty rule', 'wp-shifty') . '</span>';
                              }
                              $urls[$kind][] = site_url();
                              break;
                        case 'header':
                              $kind = (isset($condition['is-exception']) && $condition['is-exception'] == 'on' ? 'except' : 'if');
                              if (isset($condition['key']) && !empty($condition['key'])){
                                    $condition_array[$kind][] = '<span class="wpshifty-condition-tag wpshifty-condition-' . $kind . '" data-condition-editor-id="' . $condition_id . '">' . esc_html__('HTTP header match', 'wp-shifty') . '</span>';
                              }
                              else {
                                    $condition_array[$kind][] = '<span class="wpshifty-condition-tag wpshifty-condition-empty" data-condition-editor-id="' . $condition_id . '">' . esc_html__('empty rule', 'wp-shifty') . '</span>';
                              }
                              $urls[$kind][] = site_url();
                              break;
                        case 'cookie':
                              $kind = (isset($condition['is-exception']) && $condition['is-exception'] == 'on' ? 'except' : 'if');
                              if (isset($condition['key']) && !empty($condition['key'])){
                                    $condition_array[$kind][] = '<span class="wpshifty-condition-tag wpshifty-condition-' . $kind . '" data-condition-editor-id="' . $condition_id . '">' . esc_html__('Cookie match', 'wp-shifty') . '</span>';
                              }
                              else {
                                    $condition_array[$kind][] = '<span class="wpshifty-condition-tag wpshifty-condition-empty" data-condition-editor-id="' . $condition_id . '">' . esc_html__('empty rule', 'wp-shifty') . '</span>';
                              }
                              $urls[$kind][] = site_url();
                              break;
                        case 'useragent':
                              $kind = (isset($condition['is-exception']) && $condition['is-exception'] == 'on' ? 'except' : 'if');
                              if (isset($condition['value']) && !empty($condition['value'])){
                                    $condition_array[$kind][] = '<span class="wpshifty-condition-tag wpshifty-condition-' . $kind . '" data-condition-editor-id="' . $condition_id . '">' . esc_html__('User Agent match', 'wp-shifty') . '</span>';
                              }
                              else {
                                    $condition_array[$kind][] = '<span class="wpshifty-condition-tag wpshifty-condition-empty" data-condition-editor-id="' . $condition_id . '">' . esc_html__('empty rule', 'wp-shifty') . '</span>';
                              }
                              $urls[$kind][] = site_url();
                              break;
                        case 'device':
                              $kind = (isset($condition['is-exception']) && $condition['is-exception'] == 'on' ? 'except' : 'if');
                              if (isset($condition['devices']) && !empty($condition['devices'])){
                                    $devices = WP_Shifty_Helper::get_devices();
                                    $condition_labels = array();
                                    if (count($condition['devices']) < 3) {
                                          foreach ((array)$condition['devices'] as $device){
                                                $condition_labels[] = '<span class="wpshifty-condition-tag wpshifty-condition-'. $kind .'" data-condition-editor-id="' . $condition_id . '">' . sprintf(esc_html__('device is %s', 'wp-shifty'), strtolower($devices[$device])). '</span>';
                                          }
                                          $condition_array[$kind][] = implode(esc_html__(' OR ', 'wp-shifty'), (array)$condition_labels);
                                    }
                                    else {
                                          $condition_array[$kind][] = '<span class="wpshifty-condition-tag wpshifty-condition-'. $kind .'" data-condition-editor-id="' . $condition_id . '">' . sprintf(esc_html__('device is ... (%d devices)', 'wp-shifty'), count($condition['devices'])) . '</span>';
                                    }
                              }
                              else {
                                    $condition_array[$kind][] = '<span class="wpshifty-condition-tag wpshifty-condition-empty" data-condition-editor-id="' . $condition_id . '">' . esc_html__('empty rule', 'wp-shifty') . '</span>';
                              }
                              $urls[$kind][] = site_url();
                              break;
                        case 'cronjob':
                              $kind = (isset($condition['is-exception']) && $condition['is-exception'] == 'on' ? 'except' : 'apply');
                              $condition_array[$kind][] = '<span class="wpshifty-condition-tag wpshifty-condition-' . $kind . '" data-condition-editor-id="' . $condition_id . '">' . esc_html__('Cronjob', 'wp-shifty') . '</span>';

                              $urls[$kind][] = site_url('wp-cron.php?doing_wp_cron');
                              break;
                        case 'cli':
                              $kind = (isset($condition['is-exception']) && $condition['is-exception'] == 'on' ? 'except' : 'apply');
                              $condition_array[$kind][] = '<span class="wpshifty-condition-tag wpshifty-condition-' . $kind . '" data-condition-editor-id="' . $condition_id . '">' . esc_html__('CLI', 'wp-shifty') . '</span>';

                              $urls[$kind][] = site_url('?wpshifty-cli');
                              break;
                  }
            }


            if (!empty($condition_array['apply'])){
                  $summary .= esc_html__('APPLY ', 'wp-shifty') . implode(esc_html__(' AND ', 'wp-shifty'), (array)$condition_array['apply']) . ' ';
            }

            if (!empty($condition_array['if'])){
                  $summary .= (empty($summary) ? esc_html__('APPLY ', 'wp-shifty') : '') . esc_html__('IF ', 'wp-shifty');
                  $summary .= implode(esc_html__(' AND ', 'wp-shifty'), (array)$condition_array['if']);
            }

            if (!empty($condition_array['except'])){
                  $summary .= esc_html__('EXCEPT ', 'wp-shifty') . implode(esc_html__(' AND ', 'wp-shifty'), (array)$condition_array['except']);
            }

            if (empty($urls['apply']) && !empty($urls['if'])) {
                  $urls['apply'] = array_filter((array)$urls['if']);
            }
            $urls['apply'] = array_diff(array_filter((array)$urls['apply']), array_filter((array)$urls['except']));

            if ($context == 'dashboard'){
                  return $summary;
            }
            else {
                  return array(
                        $summary,
                        $urls
                  );
            }
      }

      public static function get_rule_summary($rules = array()){
            $short_summary = array();
            $summary = '';

            // Plugins
            $plugins = array();
            if (isset($rules['plugins'])){
                  foreach ((array)$rules['plugins'] as $plugin => $value) {
                        $data = get_plugin_data(trailingslashit(WP_PLUGIN_DIR) . $plugin);
                        $slug             = preg_replace('~([^\/]+)/([^\.]+)\.php~', "$1", $plugin);
                        $plugins[] = '<li><img src="' . WP_SHIFTY_API_URL . 'plugins/icon/' . $slug . '" width="30" height="30"> <strong>' . $data['Name'] . '</strong></li>';
                  }
            }

            if (!empty($plugins)){
                  $summary .= '<h3>' . esc_html__('Disabled Plugins', 'wp-shifty') . '</h3><ul class="wpshifty-what-summary-list">';
                  $summary .= implode("\n", $plugins);
                  $summary .= '</ul>';
            }

            // Disable & Overwrite
            $disable = $overwrite = array(
                  'css' => array(),
                  'css_inline' => array(),
                  'js' => array(),
                  'js_inline' => array()
            );
            if (isset($rules['disable'])){
                  foreach ((array)$rules['disable'] as $rule) {
                        if (isset($rule['summary']) && !empty($rule['summary'])){
                              $type = (preg_match('~^<script~', $rule['summary']) ? 'js_inline' : 'css_inline');
                              $disable[$type][] = '<li>' . htmlentities($rule['summary']) . '</li>';
                        }
                        else {
                              preg_match('~\.(css|js)(\?(.*))?$~', $rule['rule'], $type);
                              $disable[$type[1]][] = '<li>' . $rule['rule'] . '</li>';
                        }
                  }
            }

            if (isset($rules['overwrite'])){
                  foreach ((array)$rules['overwrite'] as $rule) {
                        if (isset($rule['summary']) && !empty($rule['summary'])){
                              $type = (preg_match('~^<script~', $rule['summary']) ? 'js_inline' : 'css_inline');
                              $overwrite[$type][] = '<li>' . htmlentities($rule['summary']) . '</li>';
                        }
                        else {
                              preg_match('~\.(css|js)(\?(.*))?$~', $rule['source'], $type);
                              $overwrite[$type[1]][] = '<li>' . $rule['source'] . '</li>';
                        }
                  }
            }

            if (!empty($disable['css']) || !empty($disable['css_inline'])){
                  $summary .= '<h3>' . esc_html__('Disabled CSS', 'wp-shifty') . '</h3><ul class="wpshifty-what-summary-list">';
                  $summary .= implode("\n", $disable['css']);
                  $summary .= implode("\n", $disable['css_inline']);
                  $summary .= '</ul>';
            }

            if (!empty($disable['js']) || !empty($disable['js_inline'])){
                  $summary .= '<h3>' . esc_html__('Disabled Scripts', 'wp-shifty') . '</h3><ul class="wpshifty-what-summary-list">';
                  $summary .= implode("\n", $disable['js']);
                  $summary .= implode("\n", $disable['js_inline']);
                  $summary .= '</ul>';
            }

            if (!empty($overwrite['css']) || !empty($overwrite['css_inline'])){
                  $summary .= '<h3>' . esc_html__('Overwritten CSS', 'wp-shifty') . '</h3><ul class="wpshifty-what-summary-list">';
                  $summary .= implode("\n", $overwrite['css']);
                  $summary .= implode("\n", $overwrite['css_inline']);
                  $summary .= '</ul>';
            }

            if (!empty($overwrite['js']) || !empty($overwrite['js_inline'])){
                  $summary .= '<h3>' . esc_html__('Overwritten Scripts', 'wp-shifty') . '</h3><ul class="wpshifty-what-summary-list">';
                  $summary .= implode("\n", $overwrite['js']);
                  $summary .= implode("\n", $overwrite['js_inline']);
                  $summary .= '</ul>';
            }

            // Preload
            $preload = array();
            if (isset($rules['preload'])){
                  foreach ((array)$rules['preload'] as $resource) {
                        $preload[] = '<li>' . $resource['source'] . ' as ' . $resource['as'] . (!empty($resource['media']) ? sprintf(__(' media: %s', 'wp-shifty'), $resource['media']) : '') . '</li>';
                  }
            }

            if (!empty($preload)){
                  $summary .= '<h3>' . esc_html__('Preloaded resources', 'wp-shifty') . '</h3><ul class="wpshifty-what-summary-list">';
                  $summary .= implode("\n", $preload);
                  $summary .= '</ul>';
            }

            // Inject
            $inject = array();
            $location_labels = array(
                  'head_beginning' => __('beginning of &lt;head&gt;', 'wp-shifty'),
                  'head_after_styles' => __('after styles in &lt;head&gt;', 'wp-shifty'),
                  'head_end' => __('end of &lt;head&gt;', 'wp-shifty'),
                  'footer_beginning' => __('beginning of footer', 'wp-shifty'),
                  'footer_end' => __('end of footer', 'wp-shifty'),

            );

            if (isset($rules['inject'])){
                  foreach ((array)$rules['inject'] as $rule) {
                        $file = WP_Shifty_Helper::get_resource_path($rule['inject']);
                        if (file_exists($file)){
                              $injected = substr(file_get_contents($file), 0, WP_SHIFTY_RESOURCE_MAX_LENGTH);
                              $inject[] = '<li>' . htmlentities($injected) . sprintf(__(' at %s', 'wp-shifty'), $location_labels[$rule['location']]) . '</li>';
                        }
                  }
            }

            if (!empty($inject)){
                  $summary .= '<h3>' . esc_html__('Injected resources', 'wp-shifty') . '</h3><ul class="wpshifty-what-summary-list">';
                  $summary .= implode("\n", $inject);
                  $summary .= '</ul>';
            }


            // Short summary disable
            if (count((array)$plugins) > 0){
                  $short_summary[] = sprintf(_n('%d plugin disabled ', '%d plugins disabled ', count($plugins), 'wp-shifty'), count($plugins));
            }

            if (count((array)$disable['css']) > 0){
                  $short_summary[] = sprintf(_n('%d CSS disabled ', '%d CSS disabled ', count($disable['css']), 'wp-shifty'), count($disable['css']));
            }

            if (count((array)$disable['css_inline']) > 0){
                  $short_summary[] = sprintf(_n('%d inline CSS disabled ', '%d inline CSS disabled ', count($disable['css_inline']), 'wp-shifty'), count($disable['css_inline']));
            }

            if (count((array)$disable['js']) > 0){
                  $short_summary[] = sprintf(_n('%d script disabled ', '%d scripts disabled ', count($disable['js']), 'wp-shifty'), count($disable['js']));
            }

            if (count((array)$disable['js_inline']) > 0){
                  $short_summary[] = sprintf(_n('%d inline script disabled ', '%d inline scripts disabled ', count($disable['js_inline']), 'wp-shifty'), count($disable['js_inline']));
            }

            // Short summary overwirte
            if (count((array)$overwrite['css']) > 0){
                  $short_summary[] = sprintf(_n('%d CSS disabled ', '%d CSS overwritten ', count($overwrite['css']), 'wp-shifty'), count($overwrite['css']));
            }

            if (count((array)$overwrite['css_inline']) > 0){
                  $short_summary[] = sprintf(_n('%d inline CSS overwritten ', '%d inline CSS overwritten ', count($overwrite['css_inline']), 'wp-shifty'), count($overwrite['css_inline']));
            }

            if (count((array)$overwrite['js']) > 0){
                  $short_summary[] = sprintf(_n('%d script overwritten ', '%d scripts overwritten ', count($overwrite['js']), 'wp-shifty'), count($overwrite['js']));
            }

            if (count((array)$overwrite['js_inline']) > 0){
                  $short_summary[] = sprintf(_n('%d inline script overwritten ', '%d inline scripts overwritten ', count($overwrite['js_inline']), 'wp-shifty'), count($overwrite['js_inline']));
            }

            // Short summary preload
            if (isset($rules['preload']) && count((array)$rules['preload']) > 0){
                  $short_summary[] = sprintf(_n('%d resource preloaded ', '%d resources preloaded ', count($rules['preload']), 'wp-shifty'), count($rules['preload']));
            }

            // Short summary inject
            if (isset($rules['inject']) && count((array)$rules['inject']) > 0){
                  $short_summary[] = sprintf(_n('%d snippet injected ', '%d snippets injected ', count($rules['inject']), 'wp-shifty'), count($rules['inject']));
            }


            return array(
                  'short' => implode(', ', $short_summary),
                  'full'  => $summary
            );
      }

      public static function get_template($scenario){
            $condition_summary = WP_Shifty_Scenario::get_condition_summary($scenario->settings['conditions'], 'dashboard');
            $rule_summary      = WP_Shifty_Scenario::get_rule_summary($scenario->settings['elements']);

            ob_start();
            include WP_SHIFTY_DIR . 'templates/scenario.tpl.php';
            return ob_get_clean();
      }

}