<?php

class WP_Shifty_Preview {

      public static $preview_id;

      public static $observer = array(
            'disabled' => array(
                  'plugin' => array(),
                  'resource' => array(),
            ),
            'overwritten' => array(
                  'css' => array(),
                  'js' => array(),
            ),
            'preloaded' => array(
                  'css' => array(),
                  'js' => array(),
                  'font' => array(),
                  'image' => array(),
            ),
            'lazyloaded' => array(
                  'resource' => array(),
            ),
            'injected' => array(
                  'css' => array(),
                  'js' => array(),
                  'html' => array()
            ),
            'resources' => array(
                  'plugin' => array(),
                  'theme' => array(),
                  'core' => array(),
                  'unknown' => array(),
            )
      );

      public static $plugins = array();

      public static function init($preview_id){
            self::$preview_id = $preview_id;
            self::observe();

            @ini_set( 'display_errors', 0 );

            add_action('wp_head', function(){
                  //JS Error reporting
                  echo "<script data-shifty>window.addEventListener('error', function(event) {
                        var error = '';
                        error += event.message.toString();
                        error += '<span> in ' + event.filename.toString();
                        error += ' line: ' + event.lineno.toString();
                        error += ' col: ' + event.colno.toString() + '</span>';
                        parent.postMessage({
                              jserror: error
                        });
              });</script>";

            }, -PHP_INT_MAX);

            // Add messenger
            add_action('plugins_loaded', array(__CLASS__, 'messenger'),9);

            add_action('wpshifty_collect_resource', array(__CLASS__, 'collect_resource'), 10, 3);
            add_action('wpshifty_overwrite_resource', array(__CLASS__, 'overwrite_resource'), 10, 3);
            add_action('wpshifty_disable_resource', array(__CLASS__, 'disable_resource'), 10, 2);
            add_action('wpshifty_lazyload_resource', array(__CLASS__, 'lazyload_resource'), 10, 2);

            add_action('wpshifty_plugin_disabled', array(__CLASS__, 'plugin_disabled'));

            header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
      }

      public static function collect_resource($type, $source, $resource = null){
            if (isset($resource->{'data-shifty'})){
                  return;
            }

            $rel = WP_Shifty_Resource::measure_relation($type, $source, $resource);
            $key = hash('crc32', $source);

            switch ($rel[1]){
                  case 'core':
                        self::$observer['resources']['core'][$type][$key] = array(
                              'url' => $source,
                              'type' => $rel[0],
                              'id' => WP_Shifty_Resource::get_id($source)
                        );
                        break;
                  case 'theme':
                        if (!isset(self::$observer['resources']['theme'][$rel[2]])){
                              self::$observer['resources']['theme'][$rel[2]] = array(
                                    'name' => wp_get_theme($rel[2])->name,
                                    'files' => array()
                              );
                        }
                        self::$observer['resources']['theme'][$rel[2]]['files'][$type][$key] = array(
                              'url' => $source,
                              'type' => $rel[0],
                              'id' => WP_Shifty_Resource::get_id($source)
                        );
                        break;
                  case 'plugin':
                        if (!isset(self::$observer['resources']['plugin'][$rel[2]])){
                              self::$observer['resources']['plugin'][$rel[2]] = array(
                                    'name' => $rel[3],
                                    'files' => array()
                              );
                        }
                        self::$observer['resources']['plugin'][$rel[2]]['files'][$type][$key] = array(
                              'url' => $source,
                              'type' => $rel[0],
                              'id' => WP_Shifty_Resource::get_id($source)
                        );
                        break;
                  case 'third_party':
                        if (!isset(self::$observer['resources']['third_party'][$rel[2]])){
                              self::$observer['resources']['third_party'][$rel[2]] = array(
                                    'name' => $rel[3],
                                    'files' => array(),
                                    'icon' => $rel[4]
                              );
                        }
                        self::$observer['resources']['third_party'][$rel[2]]['files'][$type][$key] = array(
                              'url' => $source,
                              'type' => $rel[0],
                              'id' => WP_Shifty_Resource::get_id($source)
                        );
                        break;
                  case 'data':
                  default:
                        self::$observer['resources']['unknown'][$type][$key] = array(
                              'url' => $source,
                              'type' => $rel[0],
                              'id' => WP_Shifty_Resource::get_id($source)
                        );
                        break;
            }
      }

      public static function overwrite_resource($type, $original, $new){
            self::$observer['overwritten']['resource'][$original] = $new;
      }

      public static function disable_resource($type, $resource){
            self::$observer['disabled']['resource'][] = $resource;
      }

      public static function lazyload_resource($type, $resource){
            self::$observer['lazyloaded']['resource'][] = $resource;
      }

      public static function plugin_disabled($plugin){
            self::$observer['disabled']['plugin'][] = $plugin;
      }

      public static function observe(){
            $plugins = array();

            if( !function_exists('get_plugin_data') ){
                require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            }

            $active_plugins = maybe_unserialize(WP_Shifty::$wpdb->get_var("SELECT option_value FROM " . WP_Shifty::$wpdb->prefix . "options WHERE option_name = 'active_plugins'"));
            foreach ((array)$active_plugins as $plugin){
                  // Skip Shifty
                  if (strpos($plugin, basename(WP_SHIFTY_DIR)) === 0){
                        continue;
                  }
                  $data = get_plugin_data(trailingslashit(WP_PLUGIN_DIR) . $plugin);
                  $slug = preg_replace('~([^\/]+)/([^\.]+)\.php~', "$1", $plugin);

                  $plugins[] = array(
                        'name'            => $data['Name'],
                        'slug'            => $slug,
                        'file'            => $plugin,
                  );

                  self::$plugins[$slug] = array(
                        'dir'       => str_replace(ABSPATH, '', dirname(trailingslashit(WP_PLUGIN_DIR) . $plugin)),
                        'slug'      => $slug,
                        'name'      => $data['Name']
                  );
            }

            WP_Shifty_Preview::$observer['active_plugins'] = $plugins;
      }

      public static function messenger(){
            ob_start(function($buffer){
                  if (!WP_Shifty_Helper::auth()){
                        return $buffer;
                  }

                  // Sort plugins
                  if (is_array(self::$observer['resources']['plugin']) && !empty(self::$observer['resources']['plugin'])){
                        uksort(self::$observer['resources']['plugin'], function($a, $b) {
                           return strcasecmp($a, $b);
                        });
                  }

                  self::$observer['match']      = WP_Shifty_Core::$match;
                  self::$observer['redirect']   = isset($_GET['wpshifty-redirected']);

                  $browser_data = array();
                  if (!empty(WP_Shifty_Core::$not_matched_condition)){
                        foreach ((array)WP_Shifty_Core::$not_matched_condition as $condition){
                              switch($condition['type']){
                                    case 'user':
                                          $browser_data[] = array(
                                                'selector'  => '[name="browser[user_role]"]',
                                                'value'     => array_shift($condition['apply'])
                                          );
                                          break;
                                    case 'post-data':
                                          $browser_data[] = array(
                                                'selector'  => '[name="browser[postdata]"]',
                                                'value'     => $condition['apply']
                                          );
                                          break;
                                    case 'header':
                                    case 'cookie':
                                          switch($condition['apply']['key-match']){
                                                case 'exact':
                                                      $key = $condition['apply']['key'];
                                                      break;
                                                case 'partial':
                                                      $key = 'test-' . $condition['apply']['key'];
                                                      break;
                                                case 'regex':
                                                      $key = WP_Shifty_Regex::reverse($condition['apply']['key']);
                                                      break;
                                          }

                                          switch($condition['apply']['value-match']){
                                                case 'exact':
                                                      $value = $condition['apply']['value'];
                                                      break;
                                                case 'partial':
                                                      $value = 'test-' . $condition['apply']['value'];
                                                      break;
                                                case 'regex':
                                                      $value = WP_Shifty_Regex::reverse($condition['apply']['value']);
                                                      break;
                                          }
                                          if ($condition['type'] == 'header'){
                                                $browser_data[] = array(
                                                      'selector'  => '[name="browser[headers]"]',
                                                      'value'     => "{$key}: {$value}"
                                                );
                                          }
                                          else if ($condition['type'] == 'cookie'){
                                                $browser_data[] = array(
                                                      'selector'  => '[name="browser[cookies]"]',
                                                      'value'     => "{$key}={$value}"
                                                );
                                          }
                                          break;
                                    case 'useragent':
                                          switch($condition['apply']['match']){
                                                case 'exact':
                                                      $value = $condition['apply']['value'];
                                                      break;
                                                case 'partial':
                                                      $value = 'test-' . $condition['apply']['value'];
                                                      break;
                                                case 'regex':
                                                      $value = WP_Shifty_Regex::reverse($condition['apply']['value']);
                                                      break;
                                          }
                                          $browser_data[] = array(
                                                'selector'  => '[name="browser[useragent]"]',
                                                'value'     => $value
                                          );
                                          break;
                                    case 'device':
                                          $selector = '[name="browser[device]"]';
                                          foreach($condition['apply'] as $device){
                                                if ($device == 'desktop'){
                                                      $value = 'desktop';
                                                      break;
                                                }
                                                else if ($device == 'phone'){
                                                      $value = 'iphone12';
                                                      break;
                                                }
                                                else if ($device == 'tablet'){
                                                      $value = 'ipad102';
                                                      break;
                                                }
                                                else if ($device == 'android'){
                                                      $value = 'galaxys7plus';
                                                      break;
                                                }
                                                else if ($device == 'ios'){
                                                      $value = 'ipad102';
                                                      break;
                                                }
                                                else if ($device == 'bot'){
                                                      $selector = '[name="browser[useragent]"]';
                                                      $value = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';
                                                      break;
                                                }
                                          }
                                          $browser_data[] = array(
                                                'selector'  => $selector,
                                                'value'     => $value,
                                                'reload'    => false
                                          );
                                          break;
                              }
                        }
                  }
                  self::$observer['browser_data'] = $browser_data;

                  $messenger = WP_Shifty_Helper::get_script_tag(file_get_contents(WP_SHIFTY_DIR . 'templates/scripts/messenger-child.tpl.js'));
                  $messenger = str_replace('SWO_OBSERVER_PLACEHOLDER', json_encode(self::$observer), $messenger);
                  $messenger = str_replace('SWO_OBSERVER_PREVIEW_ID', self::$preview_id, $messenger);
                  if ((defined('DOING_AJAX') || defined('DOING_CRON')) && isset($_GET['wpshifty-preview'])){
                        header('Content-type: text/html');
                        $buffer .= $messenger;
                  }
                  else {
                        $buffer = str_replace('</body>',$messenger . '</body>', $buffer);
                  }

                  return $buffer;
            });
      }

}

?>