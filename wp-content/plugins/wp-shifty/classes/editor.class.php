<?php

class WP_Shifty_Editor {

      public static function init(){
            if (!defined('DOING_AJAX') && isset($_GET['page']) && $_GET['page'] == 'wp-shifty' && isset($_GET['editor'])){
                  self::load_editor();
            }
      }

      public static function load_editor(){
            set_current_screen('wp-shifty-editor');
            $css_checksum = md5_file(WP_SHIFTY_DIR . 'assets/editor.css');
            $js_checksum = md5_file(WP_SHIFTY_DIR . 'assets/editor.js');

            wp_enqueue_style ('wpshifty-editor', WP_SHIFTY_URI . 'assets/editor.css', array(), $css_checksum);
            wp_enqueue_style ('wpshifty-admin', WP_SHIFTY_URI . 'assets/admin.css', array(), $css_checksum);

            wp_enqueue_style('codemirror', WP_SHIFTY_URI . 'assets/codemirror/codemirror.css');
            wp_enqueue_style('codemirror-theme', WP_SHIFTY_URI . 'assets/codemirror/theme/monokai.css');
            wp_enqueue_style('codemirror-search', WP_SHIFTY_URI . 'assets/codemirror/addon/search/search.css');

            wp_enqueue_script('codemirror', WP_SHIFTY_URI . 'assets/codemirror/codemirror.js', array(), false, true);
            wp_enqueue_script('codemirror-xml', WP_SHIFTY_URI . 'assets/codemirror/mode/xml/xml.js', array(), false, true);
            wp_enqueue_script('codemirror-css', WP_SHIFTY_URI . 'assets/codemirror/mode/css/css.js', array(), false, true);
            wp_enqueue_script('codemirror-js', WP_SHIFTY_URI . 'assets/codemirror/mode/javascript/javascript.js', array(), false, true);
            wp_enqueue_script('codemirror-search', WP_SHIFTY_URI . 'assets/codemirror/addon/search/search.js', array(), false, true);

            wp_enqueue_script ('wpshifty-editor', WP_SHIFTY_URI . 'assets/editor.js', array('jquery'), $js_checksum);
            wp_enqueue_script ('jquery-ui-sortable');

            if ($_GET['editor'] == 'add-new'){
                  if (wp_verify_nonce($_GET['nonce'], 'wp-shifty-add')){
                        // Shortcuts
                        $settings = array(
                              'conditions' => array(),
                              'elements' => array(
                                    'plugins' => array(),
                                    'disable' => array(),
                                    'overwrite' => array(),
                                    'preload' => array(),
                                    'lazyload' => array(),
                                    'inject' => array()
                              ),
                        );
                        $preview = array(
                              'browser' => array()
                        );
                        if (isset($_GET['shortcut']) && !empty($_GET['shortcut'])){
                              $id = WP_Shifty_Helper::random_id();
                              switch ($_GET['shortcut']) {
                                    case 'page':
                                          $settings['conditions'][$id] = array('type' => 'page', 'pages' => array((int)$_GET['page_id']));
                                          break;
                                    case 'admin':
                                          $settings['conditions'][$id] = array('type' => 'admin', 'pages' => array($_GET['screen']));
                                          break;
                                    case 'post_type':
                                          $settings['conditions'][$id] = array('type' => 'post-type', 'post-types' => array((int)$_GET['post_type']));
                                          break;
                                    case 'url':
                                          $settings['conditions'][$id] = array('type' => 'url', 'match' => 'exact', 'url' => $_GET['url']);
                                          break;
                              }
                        }

                        WP_Shifty::$wpdb->insert(WP_SHIFTY_TABLE, array('status' => 'draft', 'settings' => json_encode($settings), 'preview' => json_encode($preview)));

                        $id = WP_Shifty::$wpdb->insert_id;
                        if (!empty($id)){
                              wp_redirect(add_query_arg('editor', $id, WP_SHIFTY_ADMIN_URL));
                        }
                        die;
                  }
                  else {
                        wp_redirect(WP_SHIFTY_ADMIN_URL);
                        die;
                  }
            }

            $wpshifty_editor = WP_Shifty::$wpdb->get_row(WP_Shifty::$wpdb->prepare("SELECT id, settings, preview, status FROM " . WP_SHIFTY_TABLE . " WHERE id = %d", (int)$_GET['editor']));

            if (empty($wpshifty_editor)){
                  include_once WP_SHIFTY_DIR . 'templates/editor/missing-scenario.tpl.php';
                  die;
            }

            @$wpshifty_editor->settings = json_decode($wpshifty_editor->settings);
            @$wpshifty_editor->preview = json_decode($wpshifty_editor->preview);

            // Check compression
            $response = wp_remote_head(home_url(), array('sslverify' => false, 'user-agent' => WP_SHIFTY_UA));
            $compression = (!is_wp_error($response) && preg_match('~(gzip|br|compress|deflate)~', wp_remote_retrieve_header($response, 'content-encoding')));

            include_once WP_SHIFTY_DIR . 'templates/editor/main.tpl.php';
            die;
      }

      public static function get_user_roles(){
            global $wp_roles;

            return $wp_roles->roles;
      }

      public static function get_condition_editor($condition, $id){
            ob_start();
            $_condition = $condition;
            include WP_SHIFTY_DIR . 'templates/editor/condition-' . $condition->type . '.tpl.php';
            unset($_condition);
            $editor = ob_get_clean();
            $editor = str_replace('%ID', $id, $editor);
            echo $editor;
      }

      public static function get_rule_editor($rule, $type, $id){
            ob_start();
            $_rule = $rule;
            $_rule->id = $id;
            include WP_SHIFTY_DIR . 'templates/editor/rule-' . $type . '.tpl.php';
            unset($_rule);
            $editor = ob_get_clean();
            $editor = str_replace('%ID', $id, $editor);
            echo $editor;
      }

      public static function maybe_checked($condition, $prop, $value = 'on', $check = 'equals'){
            switch ($check){
                  case 'equals':
                        echo (isset($condition->{$prop}) && $condition->{$prop} == $value ? ' checked' : '');
                        break;
                  case 'in':
                        echo (isset($condition->{$prop}) && is_array($condition->{$prop}) && in_array($value, $condition->{$prop}) ? ' checked' : '');
            }

      }

      public static function maybe_selected($condition, $prop, $value = 'on'){
            echo (isset($condition->{$prop}) && $condition->{$prop} == $value ? ' selected' : '');
      }

      public static function maybe_value($condition, $prop){
            echo (isset($condition->{$prop}) ? ' value="' . esc_attr($condition->{$prop}) . '"' : '');
      }

      public static function maybe_textarea($condition, $prop){
            echo (isset($condition->{$prop}) ? esc_textarea($condition->{$prop}) : '');
      }

      public static function save($id, $status, $conditions, $elements, $browser){
            $formatted_conditions = WP_Shifty_Api::request('utils/conditions', array('conditions' => json_encode(stripslashes_deep($conditions))));
            $formatted_elements = WP_Shifty_Api::request('utils/elements', array('elements' => json_encode(stripslashes_deep($elements))));

            if ($status == 'preview'){
                  $data = array(
                        'preview' => json_encode(array(
                              'rules' => array(
                                    $formatted_conditions,
                                    $formatted_elements
                              ),
                              'browser' => stripslashes_deep($browser)
                        ))
                  );
            }
            else {
                  $data = array(
                        'settings' => json_encode(array(
                              'conditions' => stripslashes_deep($conditions),
                              'elements'  => stripslashes_deep($elements)
                        )),
                        'rules' => json_encode(array(
                              $formatted_conditions,
                              $formatted_elements
                        )),
                        'preview' => json_encode(array(
                              'rules' => array(
                                    $formatted_conditions,
                                    $formatted_elements
                              ),
                              'browser' => stripslashes_deep($browser)
                        )),
                        'status' => $status
                  );
            }

            WP_Shifty::$wpdb->update(WP_SHIFTY_TABLE, $data, array('id' => $id));

            WP_Shifty::early_loader();
      }

      public static function load_file($source){
            $source = self::normalize_url($source);

            $response = wp_remote_get($source, array('sslverify' => false, 'timeout' => 30, 'user-agent' => WP_SHIFTY_UA));
            if (!is_wp_error($response)){
                  $content_type = (isset($response['headers']['content-type']) ? explode(';',$response['headers']['content-type'])[0] : '');
                  $content      = $response['body'];

                  if ($content_type == 'text/css'){
                        // Fix relative path URLs
                        $current_path = dirname($source);
                        $content = preg_replace_callback('~url(\s+)?\((\'|")?([^\'"\)]+)(\'|")?\)~', function($matches) use ($current_path) {
                              return 'url(' . self::canonicalize(trailingslashit($current_path) . $matches[3]) . ')';
                        }, $content);

                        // Fix relative path imports
                        $content = preg_replace_callback('~@import(\s+)?(\'|")?([^;\'"]+)(\'|")?;~', function($matches) use ($current_path) {
                              if (strpos($matches[3], 'url') === 0){
                                    return $matches[0];
                              }
                              return '@import \'' . self::canonicalize(trailingslashit($current_path) . $matches[3]) . '\'';
                        }, $content);
                  }

                  return array(
                        'content'   => $content,
                        'mime'      => $content_type
                  );
            }
            else {
                  return array(
                        'error' => $response->get_error_message()
                  );
            }
      }

      public static function save_file($source, $mime, $filename){
            // escape filename
            $filename = self::escape_filename($filename);

            // extension
            switch ($mime) {
                  case 'text/css':
                        $ext = '.css';
                        break;
                  case 'application/javascript':
                  case 'text/javascript':
                        $ext = '.js';
                        break;
                  case 'text/plain':
                  default:
                        $ext = '.txt';
                        break;
            }

            $upload_dir   = wp_upload_dir();
            $dir = trailingslashit($upload_dir['basedir']) . 'wp-shifty';
            $url = trailingslashit($upload_dir['baseurl']) . 'wp-shifty';

            if (!file_exists($dir)){
                  if (!is_writable(WP_Shifty_Helper::upload_dir()->basedir)){
                        return array(
                              'error' => sprintf(esc_html__('%s is not writable. Please change the permissions.', 'wp-shifty'), $upload_dir)
                        );
                  }
                  // try to create dir
                  mkdir(WP_Shifty_Helper::upload_dir()->dir, 0777);

                  if (!file_exists(WP_Shifty_Helper::upload_dir()->dir)){
                        // Still not exists
                        return array(
                              'error' => esc_html__('Couldn\'t create directory. May disk is full?', 'wp-shifty')
                        );
                  }
            }

            if (is_writable(WP_Shifty_Helper::upload_dir()->dir)){
                  file_put_contents(trailingslashit(WP_Shifty_Helper::upload_dir()->dir) . $filename . $ext, $source);
                  return array(
                        'message'   => esc_html__('File has been saved', 'wp-shifty'),
                        'file'      => trailingslashit(WP_Shifty_Helper::upload_dir()->url) . $filename . $ext
                  );
            }

            return array(
                  'error' => esc_html__('Couldn\'t save the file. May disk is full?', 'wp-shifty')
            );
      }

      public static function delete_file($file){
            $file = WP_Shifty_Helper::get_resource_path($file);

            // Delete only shifty files
            if (strpos($file, WP_Shifty_Helper::upload_dir()->dir) === 0){
                  unlink($file);
            }
      }

      public static function normalize_url($source){
            if (preg_match('~^//~', $source)){
                  return 'http:' . $source;
            }
            else if (preg_match('~^/~', $source)){
                  return home_url($source);
            }

            return self::canonicalize($source);
      }

      /**
      * Get canonicalized path from URL
      * @param string $address
      * @return string
      */
      public static function canonicalize($url){
          $address = explode('/', $url);
          $keys = array_keys($address, '..');

          foreach($keys as $keypos => $key){
              array_splice($address, $key - ($keypos * 2 + 1), 2);
          }

          $address = implode('/', $address);
          $address = str_replace('./', '', $address);

          return $address;
      }

      public static function escape_filename($filename){
            return preg_replace('~([^abcdef0-9])~','',$filename);
      }

}

?>