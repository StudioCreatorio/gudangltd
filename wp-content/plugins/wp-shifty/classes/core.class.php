<?php
class WP_Shifty_Core {

      public static $dom;

      public static $query;

      public static $rules;

      public static $elements = array();

      public static $match = false;

      public static $not_matched_condition = array();

      public static $overwrites = array();

      public static $disabled = array();

      public static $has_lazy = false;

      public static function init(){
            if (isset($_GET['noshifty'])){
                  return false;
            }

            self::$query = WP_Shifty_Query::guess();

            self::init_rules();

            $should_run = false;
            foreach ((array)self::$rules as $rid => $rule){
                  $should_apply = $should_not_apply = false;
                  foreach ((array)$rule['conditions'] as $condition){
                        if (!empty($condition['except'])){
                              if (self::test_condition($condition['except'], $condition['type'])){
                                    self::$not_matched_condition[] = $condition;
                                    $should_not_apply = true;
                              }
                        }

                        if (!empty($condition['apply'])){
                              if (self::test_condition($condition['apply'], $condition['type'])){
                                    if (!self::is_strict_condition($condition['type'])){
                                          $should_apply = true;
                                    }
                              }
                              else {
                                    if (self::is_strict_condition($condition['type'])){
                                          $should_not_apply = true;
                                    }
                                    self::$not_matched_condition[] = $condition;
                              }
                        }
                  }

                  if (!$should_not_apply && $should_apply){
                        $should_run = true;
                        self::$elements = array_merge_recursive(self::$elements, (array)$rule['elements']);
						if (self::is_preview() === $rid){
							self::$match = true;
						}
                  }

            }

            if ($should_run){
                  do_action('wp_shifty_match');

                  add_action('plugins_loaded', function(){
                        ob_start(array(__CLASS__, 'get_dom'));
                  });
                  self::add_placeholders();
            }
            // We don't need to change anything, stop here
            else {
                  return;
            }

            add_filter('option_active_plugins', array(__CLASS__, 'disable_plugins'));

            add_action('wp_print_scripts', array(__CLASS__, 'dequeue_scripts'));
            add_action('admin_print_scripts', array(__CLASS__, 'dequeue_scripts'));

            add_action('wp_print_footer_scripts', array(__CLASS__, 'dequeue_scripts'));
            add_action('admin_print_footer_scripts', array(__CLASS__, 'dequeue_scripts'));

            add_action('wp_print_styles', array(__CLASS__, 'dequeue_styles'));
            add_action('admin_print_styles', array(__CLASS__, 'dequeue_styles'));

            add_action('wpshifty_overwrite_resource', function($type, $original, $new){
                  self::$overwrites[$original] = $new;
            }, 10, 3);

            add_action('wpshifty_disable_resource', function($type, $src){
                  self::$disabled[] = $src;
            }, 10, 3);
      }

      public static function has_rule($types, $actions){
            foreach ((array)$types as $type){
                  foreach ((array)$actions as $action){
                        if (isset(self::$elements[$action][$type]) && !empty(self::$elements[$action][$type]) ){
                              return true;
                        }
                  }
            }
            return false;
      }

      public static function test_condition($data, $type){
            switch ($type) {
                  case 'page':
                        return (isset(self::$query['id']) && in_array(self::$query['id'], (array)$data));
                        break;
                  case 'post-type':
                        return (isset(self::$query['post_type']) && in_array(self::$query['post_type'], (array)$data));
                        break;
                  case 'archive':
                        return (isset(self::$query['type']) && in_array(self::$query['type'], (array)$data));
                        break;
                  case 'author':
                        return $data && (isset(self::$query['type']) && self::$query['type'] == 'author');
                        break;
                  case 'search':
                        return $data && (isset(self::$query['type']) && self::$query['type'] == 'search');
                        break;
                  case 'url':
                        if (isset(self::$query['type']) && self::$query['type'] != 'headless'){
                              $current_path = parse_url(self::$query['url'], PHP_URL_PATH);
                              $test_path = parse_url($data['url'], PHP_URL_PATH);
                              switch($data['match']){
                                    case 'exact':
                                          return $current_path == $test_path;
                                          break;
                                    case 'partial':
                                          return (strpos($current_path, $test_path) !== false);
                                          break;
                                    case 'regex':
                                          return preg_match('~' . str_replace('~','\~', $test_path) . '~i', $current_path);
                                          break;
                              }
                        }
                        return false;
                        break;
                  case 'admin':
                        if (isset($_SERVER['REQUEST_URI'])){
                              foreach ((array)$data as $admin_page){
                                    $admin_page_url = parse_url($admin_page);
                                    $current_url = parse_url(str_replace(admin_url(), '', self::$query['url']));

                                    if ($admin_page_url['path'] == $current_url['path']){
                                          if (isset($admin_page_url['query'])){
                                                parse_str($admin_page_url['query'], $admin_page_query_string);
                                                $match = true;
                                                foreach ($admin_page_query_string as $key => $value){
                                                      if (!isset(self::$query['query_string'][$key]) || self::$query['query_string'][$key] != $value){
                                                            $match = false;
                                                      }
                                                }
                                                if ($match){
                                                      return true;
                                                }
                                          }
                                          else {
                                                return true;
                                          }
                                    }
                              }
                        }
                        return false;
                        break;
                  case 'ajax':
                        if (self::$query['type'] == 'ajax'){
                              switch($data['match']){
                                    case 'exact':
                                          return self::$query['action'] == $data['action'];
                                          break;
                                    case 'partial':
                                          return (strpos(self::$query['action'], $data['action']) !== false);
                                          break;
                                    case 'regex':
                                          return preg_match('~' . str_replace('~','\~', $data['action']) . '~i', self::$query['action']);
                                          break;
                              }
                        }
                        break;
                  case 'shop':
                        $shop_pages = WP_Shifty_Helper::get_shop_pages();
                        return ($data && isset(self::$query['id']) && in_array(self::$query['id'], $shop_pages));
                        break;
                  case 'everywhere':
                        return $data;
                        break;
                  case 'frontend':
                        return (self::$query['type'] != 'admin' && $data);
                        break;
                  case 'frontpage':
                        return $data && (trim(self::$query['url'], '/') == trim(home_url(), '/'));
                        break;
                  case 'user':
                        return (in_array(WP_Shifty_Query::current_user_role(), (array)$data));
                        break;
                  case 'query':
                  case 'header':
                  case 'cookie':
                        $keys = array();
                        switch ($type){
                              case 'query':
                                    $_DATA = $_GET;
                                    break;
                              case 'header':
                                    $_DATA = $_SERVER;
                                    $data['key'] = strtoupper(str_replace('-', '_', $data['key']));
                                    break;
                              case 'cookie':
                                    $_DATA = $_COOKIE;
                                    break;
                        }

                        switch ($data['key-match']){
                              case 'exact':
                                    $keys[] = ($type == 'header' ? 'HTTP_' . $data['key'] : $data['key']);
                                    break;
                              case 'partial':
                              case 'regex':
                                    foreach (array_keys($_DATA) as $_key){
                                          if (($data['key-match'] == 'partial' && strpos($_key, $data['key']) !== false) || ($data['key-match'] == 'regex' && preg_match('~' . str_replace('~', '\~', $data['key']) . '~i', $_key))){
                                                $keys[] = $_key;
                                          }
                                    }
                                    break;
                        }

                        foreach ($keys as $key){
                              if ($data['value-match'] == 'exact' && $_DATA[$key] == $data['value']){
                                    return true;
                              }

                              if ($data['value-match'] == 'partial' && strpos($_DATA[$key], $data['value']) !== false){
                                    return true;
                              }

                              if ($data['value-match'] == 'regex' && preg_match('~' . str_replace('~', '\~', $data['value']) . '~i', $_DATA[$key])){
                                    return true;
                              }
                        }
                        return false;
                        break;
                  case 'post-data':
                        parse_str($data, $__post);
                        $match = false;
                        foreach ($__post as $key => $value){
                              if (isset($_POST[$key]) && $_POST[$key] == $value){
                                    $match = true;
                              }
                              else {
                                    $match = false;
                              }
                        }
                        return $match;
                        break;
                  case 'useragent':
                        switch ($data['match']){
                              case 'exact':
                                    return (isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT'] == $data['value']);
                                    break;
                              case 'partial':
                                    return (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], $data['value']) !== false);
                                    break;
                              case 'regex':
                                    return (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('~' . str_replace(',', '\~', $data['value'].'~'), $_SERVER['HTTP_USER_AGENT']));
                                    break;
                        }
                        break;
                  case 'device':
                        $detected = false;
                        if (!empty($data)){
                              $detect = new WP_Shifty_Mobile_Detect();
                              foreach ((array)$data as $device){
                                    if ($device == 'desktop' && !$detect->isMobile()){
                                          $detected = true;
                                    }
                                    else if ($device == 'phone' && $detect->isMobile() && !$detect->isTablet()){
                                          $detected = true;
                                    }
                                    else if ($device == 'tablet' && $detect->isTablet()){
                                          $detected = true;
                                    }
                                    else if ($device == 'android' && $detect->isAndroidOS()){
                                          $detected = true;
                                    }
                                    else if ($device == 'ios' && $detect->isIOS()){
                                          $detected = true;
                                    }
                                    else if ($device == 'bot' && $detect->isBot()){
                                          $detected = true;
                                    }
                              }
                        }
                        return $detected;
                        break;
                  case 'cronjob':
                        return (self::$query['type'] == 'cron' && $data);
                        break;
                  case 'cli':
                        return (self::$query['type'] == 'cli' && $data);
                        break;
            }
            return false;
      }

      public static function test_resource($rule, $test){
            switch ($rule['match']) {
                  case 'exact':
                        return self::sanitize_url($rule['value']) == self::sanitize_url($test) || $rule['value'] == WP_Shifty_Resource::get_hash($test);
                        break;
                  case 'partial':
                        return (strpos($test, $rule['value']) !== false);
                        break;
                  case 'regex':
                        return preg_match('~' . str_replace('~','\~',$rule['value']) . '~i', $test);
                        break;
                  }
      }

      public static function disable_plugins($active_plugins){
            foreach ($active_plugins as $key => $plugin){
                  if (isset(self::$elements['plugins'][$plugin])){
                        do_action('wpshifty_plugin_disabled', $plugin);
                        unset($active_plugins[$key]);
                  }
            }

            return $active_plugins;
      }

      public static function dequeue_scripts(){
            if (self::has_rule('js','disable')){
                  $wp_scripts = wp_scripts();
                  foreach ($wp_scripts->registered as $handle => $script){
                        foreach ((array)self::$elements['disable']['js'] as $rule){
                              $src = (preg_match('~^http~', $script->src) ? $script->src : home_url($script->src));
                              if (isset($script->ver) && !empty($script->ver)){
                                    $src = add_query_arg('ver', $script->ver, $src);
                              }
                              if (isset($wp_scripts->queue) && in_array($handle, (array)$wp_scripts->queue) && self::test_resource($rule, $src)){
                                    wp_dequeue_script($handle);
                                    do_action('wpshifty_collect_resource', 'js', $src);
                                    do_action('wpshifty_disable_resource', 'js', $src);
                              }
                        }
                  }
            }
      }

      public static function dequeue_styles(){
            if (self::has_rule('css','disable')){
                  $wp_styles = wp_styles();
                  foreach ($wp_styles->registered as $handle => $style){
                        foreach ((array)self::$elements['disable']['css'] as $rule){
                              $src = (preg_match('~^http~', $style->src) ? $style->src : home_url($style->src));
                              if (isset($style->ver) && !empty($style->ver)){
                                    $src = add_query_arg('ver', $style->ver, $src);
                              }
                              if (isset($wp_styles->queue) && in_array($handle, (array)$wp_styles->queue) && self::test_resource($rule, $src)){
                                    wp_dequeue_style($handle);
                                    do_action('wpshifty_collect_resource', 'css', $src);
                                    do_action('wpshifty_disable_resource', 'css', $src);
                              }
                        }
                  }
            }
      }

      public static function get_dom($source){
            // Don't modify JSON requests
            if (WP_Shifty_Query::is_json()){
                  return $source;
            }

            // Build DOM
            $dom = new WP_Shifty_Dom();
      	if (empty($source) || strlen($source) > PHP_INT_MAX){
      		$dom->clear();
      		return false;
      	}
      	$dom->load($source);

            // Manage CSS
            if (self::is_preview() !== false || self::has_rule(array('css', 'css_inline'), array('disable', 'overwrite', 'lazyload'))){
                  foreach ($dom->find('link[rel="stylesheet"]') as $resource){
                        // Maybe skip resource
                        if (self::maybe_skip($resource)){
                              continue;
                        }

                        // Collect resource
                        do_action('wpshifty_collect_resource', 'css', $resource->href, $resource);

                        // Overwrite
                        if (isset(self::$elements['overwrite']['css'])){
                              foreach ((array)self::$elements['overwrite']['css'] as $rule){
                                    if (self::test_resource($rule, $resource->href)){
                                          $file_path = WP_Shifty_Helper::get_resource_path($rule['overwrite']);
                                          if (file_exists($file_path)){
                                                $ver = md5_file($file_path);
                                                do_action('wpshifty_overwrite_resource', 'css', $resource->href, $rule['overwrite']);
                                                $resource->href = add_query_arg('ver', $ver, $rule['overwrite']);
                                                break;
                                          }
                                    }
                              }
                        }

                        // Disable
                        if (isset(self::$elements['disable']['css'])){
                              foreach ((array)self::$elements['disable']['css'] as $rule){
                                    if (self::test_resource($rule, $resource->href)){
                                          $resource->outertext = '';
                                          do_action('wpshifty_disable_resource', 'css', $resource->href);
                                          break;
                                    }
                              }
                        }

                        // Lazyload
                        if (isset(self::$elements['lazyload']['css'])){
                              foreach ((array)self::$elements['lazyload']['css'] as $rule){
                                    if (self::test_resource($rule, $resource->href)){
                                          self::$has_lazy = true;
                                          $resource->rel = 'shifty-lazy';
                                          do_action('wpshifty_lazyload_resource', 'css', $resource->href);
                                          break;
                                    }
                              }
                        }
                  }
            }

            if (self::is_preview() !== false || self::has_rule('css_inline', array('disable', 'overwrite', 'lazyload'))){
                  foreach ($dom->find('style') as $resource){
                        // Maybe skip resource
                        if (self::maybe_skip($resource)){
                              continue;
                        }

                        $hash = WP_Shifty_Resource::get_hash($resource->outertext);

                        // Collect resource
                        do_action('wpshifty_collect_resource', 'css', $resource->outertext, $resource);

                        // Overwrite
                        if (isset(self::$elements['overwrite']['css_inline'])){
                              foreach ((array)self::$elements['overwrite']['css_inline'] as $rule){
                                    if (self::test_resource($rule, $resource->outertext)){
                                          $resource->innertext = file_get_contents(WP_Shifty_Helper::get_resource_path($rule['overwrite']));
                                          do_action('wpshifty_overwrite_resource', 'css_inline', $hash, $rule['overwrite']);
                                          break;
                                    }
                              }
                        }

                        // Disable
                        if (isset(self::$elements['disable']['css_inline'])){
                              foreach ((array)self::$elements['disable']['css_inline'] as $rule){
                                    if (self::test_resource($rule, $resource->outertext)){
                                          do_action('wpshifty_disable_resource', 'css_inline', $resource->outertext);
                                          $resource->outertext = '';
                                          break;
                                    }
                              }
                        }


                        if (self::is_preview() !== false){
                              $resource->{'data-wpshifty-hash'} = $hash;
                        }
                  }
            }

            // Manage JS
            if (self::is_preview() !== false || self::has_rule(array('js', 'js_inline'), array('disable', 'overwrite', 'lazyload'))){
                  foreach ($dom->find('script') as $resource){
                        // Maybe skip resource
                        if (self::maybe_skip($resource)){
                              continue;
                        }

                        if (!isset($resource->type) || strpos($resource->type, 'javascript') || $resource->type == 'module'){
                              if (isset($resource->src)){
                                    // Overwrite
                                    if (isset(self::$elements['overwrite']['js'])){
                                          foreach ((array)self::$elements['overwrite']['js'] as $rule){
                                                if (self::test_resource($rule, $resource->src)){
                                                      do_action('wpshifty_overwrite_resource', 'js', $resource->src, $rule['value']);
                                                      $resource->src = $rule['source'];
                                                      break;
                                                }
                                          }
                                    }

                                    // Disable
                                    if (isset(self::$elements['disable']['js'])){
                                          foreach ((array)self::$elements['disable']['js'] as $rule){
                                                if (self::test_resource($rule, $resource->src)){
                                                      $resource->outertext = '';
                                                      do_action('wpshifty_disable_resource', 'js', $resource->src);
                                                      break;
                                                }
                                          }
                                    }

                                    // Lazyload
                                    if (isset(self::$elements['lazyload']['js'])){
                                          foreach ((array)self::$elements['lazyload']['js'] as $rule){
                                                if (self::test_resource($rule, $resource->src)){
                                                      switch($rule['load']){
                                                            case 'async':
                                                                  if (isset($resource->defer)){
                                                                        unset($resource->defer);
                                                                  }
                                                                  $resource->async = '';
                                                                  break;
                                                            case 'defer':
                                                                  if (isset($resource->async)){
                                                                        unset($resource->async);
                                                                  }
                                                                  $resource->defer = '';
                                                                  break;
                                                            case 'lazy':
                                                                  self::$has_lazy = true;
                                                                  $resource->type = 'shifty/javascript';
                                                                  $resource->{'data-src'} = $resource->src;
                                                                  $resource->src = '';
                                                                  do_action('wpshifty_lazyload_resource', 'js', $resource->src);
                                                                  break;
                                                      }
                                                      break;
                                                }
                                          }
                                    }

                                    do_action('wpshifty_collect_resource', 'js', $resource->src, $resource);
                              }
                              else {
                                    $hash = WP_Shifty_Resource::get_hash($resource->outertext);

                                    do_action('wpshifty_collect_resource', 'js', $resource->outertext, $resource);

                                    // Overwrite
                                    if (isset(self::$elements['overwrite']['js_inline'])){
                                          foreach ((array)self::$elements['overwrite']['js_inline'] as $rule){
                                                if (self::test_resource($rule, $resource->outertext)){
                                                      $resource->innertext = file_get_contents(WP_Shifty_Helper::get_resource_path($rule['overwrite']));
                                                      do_action('wpshifty_overwrite_resource', 'js_inline', $hash, $rule['value']);
                                                      break;
                                                }
                                          }
                                    }

                                    // Disable
                                    if (isset(self::$elements['disable']['js_inline'])){
                                          foreach ((array)self::$elements['disable']['js_inline'] as $rule){
                                                if (self::test_resource($rule, $resource->outertext)){
                                                      do_action('wpshifty_disable_resource', 'js_inline', $resource->outertext);
                                                      $resource->outertext = '';
                                                      break;
                                                }
                                          }
                                    }

                                    // Lazyload
                                    if (isset(self::$elements['lazyload']['js_inline'])){
                                          foreach ((array)self::$elements['lazyload']['js_inline'] as $rule){
                                                if (self::test_resource($rule, $resource->outertext)){
                                                      self::$has_lazy = true;
                                                      $resource->type = 'shifty/javascript';
                                                      do_action('wpshifty_lazyload_resource', 'js_inline', $resource->outertext);
                                                      break;
                                                }
                                          }
                                    }

                                    if (self::is_preview() !== false){
                                          $resource->{'data-wpshifty-hash'} = $hash;
                                    }
                              }
                        }
                  }
            }

            // Generate preload
            $preload_tags = array();
            if (isset(self::$elements['preload'])){
                  foreach ((array)self::$elements['preload'] as $preload){
                        // Don't preload disabled resources
                        if (in_array($preload['url'], (array)self::$disabled)){
                              continue;
                        }

                        // Overwrite preload src if source has been overwritten
                        if (isset(self::$overwrites[$preload['url']])){
                              $preload['url'] = self::$overwrites[$preload['url']];
                        }

                        $preload_tags[] = '<link rel="preload" href="' . $preload['url'] . '" as="' . $preload['as'] . '"' . (isset($preload['media']) && !empty($preload['media']) ? ' media="' . $preload['media'] . '"' : '') . '>';
                  }
            }

            $html = (string)$dom;

            // Preload
            $html = str_replace('<!--[if wpshifty]>WP_SHIFTY_PRELOAD<![endif]-->', implode("\n", $preload_tags), $html);

            // Inject
            $injected = array(
                  'head_beginning' => array(),
                  'head_after_styles' => array(),
                  'head_end' => array(),
                  'footer_beginning' => array(),
                  'footer_end' => array(),
            );
            if (isset(self::$elements['inject'])){
                  foreach ((array)self::$elements['inject'] as $key => $inject_group){
                        foreach ($inject_group as $inject){
                              $file_path = WP_Shifty_Helper::get_resource_path($inject);
                              if (file_exists($file_path)){
                                    $injected[$key][] = file_get_contents($file_path);
                              }
                        }
                  }
            }

            $maybe_lazy = (self::$has_lazy ?  WP_Shifty_Helper::get_script_tag(file_get_contents(WP_SHIFTY_DIR . 'templates/scripts/lazy.tpl.js')) : '');

            $html = str_replace('<!--[if wpshifty]>WP_SHIFTY_HEAD_BEGINNING<![endif]-->', implode("\n", $injected['head_beginning']), $html);
            $html = str_replace('<!--[if wpshifty]>WP_SHIFTY_HEAD_AFTER_STYLES<![endif]-->', implode("\n", $injected['head_after_styles']), $html);
            $html = str_replace('<!--[if wpshifty]>WP_SHIFTY_HEAD_END<![endif]-->', implode("\n", $injected['head_end']), $html);
            $html = str_replace('<!--[if wpshifty]>WP_SHIFTY_FOOTER_BEGINING<![endif]-->', implode("\n", $injected['footer_beginning']), $html);
            $html = str_replace('<!--[if wpshifty]>WP_SHIFTY_LAZY<![endif]-->', $maybe_lazy, $html);
            $html = str_replace('<!--[if wpshifty]>WP_SHIFTY_FOOTER_END<![endif]-->', implode("\n", $injected['footer_end']), $html);

      	return apply_filters('wp_shifty_dom', $html);
      }

      public static function init_rules(){
            $preview_id = self::is_preview();

            foreach ((array)WP_Shifty::$wpdb->get_results(WP_Shifty::$wpdb->prepare("SELECT id, rules FROM " . WP_SHIFTY_TABLE . " WHERE status = 'active' AND id != %d", (int)$preview_id)) as $ruleset){
                  $decoded = json_decode($ruleset->rules, true);
                  if (is_array($decoded) && !empty($decoded)){
                        self::$rules[$ruleset->id] = array(
                              'conditions' => $decoded[0],
                              'elements' => $decoded[1]
                        );
                  }
            }

            // Preview
            if ($preview_id !== false){
                  WP_Shifty_Preview::init($preview_id);

                  $ruleset = WP_Shifty::$wpdb->get_results(WP_Shifty::$wpdb->prepare("SELECT id, preview FROM " . WP_SHIFTY_TABLE . " WHERE id = %d", $preview_id));
                  if (isset($ruleset[0]->preview)){
                        $decoded = json_decode($ruleset[0]->preview, true);

                        if (is_array($decoded) && !empty($decoded)){
                              self::$rules[$ruleset[0]->id] = array(
                                    'conditions' => $decoded['rules'][0],
                                    'elements' => $decoded['rules'][1]
                              );

                              // Show preview placeholder
                              if (empty($decoded['rules'][0])){
                                    remove_action('plugins_loaded', array('WP_Shifty_Preview', 'messenger'),9);
                                    add_filter('template_redirect', function(){
                                          include_once WP_SHIFTY_DIR . 'templates/preview-placeholder.php';
                                          die;
                                    });
                              }
                              // CLI placeholder
                              else if (isset($_GET['wpshifty-cli'])){
                                    add_filter('template_redirect', function(){
                                          include_once WP_SHIFTY_DIR . 'templates/preview-cli.php';
                                          die;
                                    });
                              }

                              WP_Shifty_Browser::init($decoded['browser']);
                        }
                  }
            }
      }

      public static function add_placeholders(){
            $head       = (is_admin() ? 'admin_head' : 'wp_head');
            $footer     = (is_admin() ? 'admin_footer' : 'wp_footer');

            add_action($head, function(){
                  echo "<!--[if wpshifty]>WP_SHIFTY_HEAD_BEGINNING<![endif]-->\n";
                  echo "<!--[if wpshifty]>WP_SHIFTY_PRELOAD<![endif]-->\n";
            },0);

            add_action($head, function(){
                  echo "<!--[if wpshifty]>WP_SHIFTY_HEAD_AFTER_STYLES<![endif]-->\n";
            },8.1);

            add_action($head, function(){
                  echo "<!--[if wpshifty]>WP_SHIFTY_HEAD_END<![endif]-->\n";
                  echo "<!-- This page has been optimized using WP Shifty -->\n";
            }, PHP_INT_MAX);

            add_action($footer, function(){
                  echo "<!--[if wpshifty]>WP_SHIFTY_FOOTER_BEGINING<![endif]-->\n";
            },0);

            add_action($footer, function(){
                  echo "<!--[if wpshifty]>WP_SHIFTY_LAZY<![endif]-->\n";
            },PHP_INT_MAX);

            add_action($footer, function(){
                  echo "<!--[if wpshifty]>WP_SHIFTY_FOOTER_END<![endif]-->\n";
            },PHP_INT_MAX);
      }

      public static function is_preview(){
            if (isset($_GET['wpshifty-preview'])){
                  return (int)$_GET['wpshifty-preview'];
            }
            else if(isset($_SERVER['HTTP_REFERER']) && preg_match('~wpshifty-preview=(\d*)~', (string)parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY), $matches)){
                  return (int)$matches[1];
            }
            return false;
      }

      public static function is_strict_condition($condition){
            return (in_array($condition, array(
                  'query',
                  'user',
                  'post-data',
                  'header',
                  'cookie',
                  'useragent',
                  'device'
            )));
      }

      public static function sanitize_url($url){
            return str_replace(
                  array('&#038;', '&#044;'. '&#058;', '&#059;'),
                  array('&', ',', ':', ';'),
                  $url
            );
      }

      public static function maybe_skip($resource){
            return isset($resource->{'data-skip-shifty'});
      }
}

?>