<?php

class WP_Shifty_Ajax {

      public static function init(){
            // Bind ajax actions
            add_action('wp_ajax_wpshifty_update_scenario_status', array(__CLASS__, 'update_status'));
            add_action('wp_ajax_wpshifty_delete_scenario', array(__CLASS__, 'delete_scenario'));
            add_action('wp_ajax_wpshifty_duplicate_scenario', array(__CLASS__, 'duplicate_scenario'));

            add_action('wp_ajax_wpshifty_update_preview', array(__CLASS__, 'update_preview'));
            add_action('wp_ajax_wpshifty_load_file', array(__CLASS__, 'load_file'));
            add_action('wp_ajax_wpshifty_save_file', array(__CLASS__, 'save_file'));
            add_action('wp_ajax_wpshifty_format_code', array(__CLASS__, 'format_code'));
            add_action('wp_ajax_wpshifty_save', array(__CLASS__, 'save'));
            add_action('wp_ajax_wpshifty_delete_file', array(__CLASS__, 'delete_file'));
            add_action('wp_ajax_wpshifty_cleanup', array(__CLASS__, 'delete_abandoned_files'));
            add_action('wp_ajax_wpshifty_measure_resource_sizes', array(__CLASS__, 'measure_resource_sizes'));
            add_action('wp_ajax_wpshifty_lighthouse', array(__CLASS__, 'lighthouse'));

            add_action('wp_ajax_wpshifty_disconnect', array(__CLASS__, 'disconnect'));
            add_action('wp_ajax_wpshifty_deactivate', array(__CLASS__, 'deactivate'));
      }

      public static function update_preview(){
            self::ajax_check();

            $conditions = (isset($_POST['condition']) ? $_POST['condition'] : array());
            list($summary, $urls) = WP_Shifty_Scenario::get_condition_summary(stripslashes_deep($conditions));

            // Save test rules
            $id = (int)$_POST['id'];
            try {
                  $conditions = (isset($_POST['condition']) ? $_POST['condition'] : array());
                  $elements   = (isset($_POST['elements']) ? $_POST['elements'] : array());
                  $browser    = (isset($_POST['browser']) ? $_POST['browser'] : array());
                  WP_Shifty_Editor::save($id, 'preview', $conditions, $elements, $browser);
            }
            catch (Exception $e){
                  if ($e->getCode() == '1'){
                        wp_send_json(array(
                              'error' => $e->getMessage()
                        ));
                  }
                  else {
                        $warning = $e->getMessage();
                  }
            }

            $current_url = (isset($_POST['current_url']) && !empty($_POST['current_url']) ? $_POST['current_url'] : '');

            if (!empty($current_url)){
                  if (!preg_match('~^http~', $current_url)){
                        $current_url = site_url($current_url);
                  }

                  array_unshift($urls['apply'], $current_url);
            }

            $response = array(
                  'summary'   => $summary,
                  'urls'      => array_values(array_unique((array)$urls['apply'])),
            );

            if (isset($warning)){
                  $response['warning'] = $warning;
            }

            wp_send_json($response);

      }

      public static function save(){
            self::ajax_check();

            $id = (int)$_POST['id'];
            $status = (isset($_POST['status']) && $_POST['status'] == 'active' ? 'active' : 'draft');

            if (isset($_POST['files_to_delete'])){
                  foreach ((array)$_POST['files_to_delete'] as $file){
                        WP_Shifty_Editor::delete_file($file);
                  }
            }

            try {
                  $conditions = (isset($_POST['condition']) ? $_POST['condition'] : array());
                  $elements = (isset($_POST['elements']) ? $_POST['elements'] : array());
                  $browser = (isset($_POST['browser']) ? $_POST['browser'] : array());
                  WP_Shifty_Editor::save($id, $status, $conditions, $elements, $browser);
            }
            catch (Exception $e){
                  if ($e->getCode() == '1'){
                        wp_send_json(array(
                              'error' => $e->getMessage()
                        ));
                  }
                  else {
                        $warning = $e->getMessage();
                  }
            }

            $response = array(
                  'status' => $status,
                  'message' => __('Your changes has been saved', 'wp-shifty')
            );

            if (isset($warning)){
                  $response['warning'] = $warning;
            }

            wp_send_json($response);

            die;
      }

      public static function load_file(){
            self::ajax_check();

            $response = WP_Shifty_Editor::load_file($_POST['source']);

            wp_send_json($response);
      }

      public static function save_file(){
            self::ajax_check();

            $source     = stripslashes($_POST['source']);
            $mime       = stripslashes($_POST['mime']);
            $filename   = $_POST['filename'];

            $response = WP_Shifty_Editor::save_file($source, $mime, $filename);

            wp_send_json($response);

      }

      public static function delete_file(){
            self::ajax_check();

            WP_Shifty_Editor::delete_file($_POST['file']);

            wp_send_json(array());
      }

      public static function format_code(){
            self::ajax_check();

            $source     = stripslashes($_POST['source']);
            $mime       = stripslashes($_POST['mime']);
            $format     = $_POST['format'];

            try {
                  $formatted = WP_Shifty_Api::request('utils/format_code', array(
                        'source' =>  $source,
                        'mime'   => $mime,
                        'format' => $format
                  ));

                  wp_send_json(array(
                        'content' => base64_decode($formatted)
                  ));
            }catch (Exception $e){
                  wp_send_json(array(
                        'error' => $e->getMessage()
                  ));
            }
      }

      public static function update_status(){
            self::ajax_check();

            $id = (int)$_POST['scenario'];
            $status = (isset($_POST['status']) && $_POST['status'] == 'active' ? 'active' : 'draft');
            WP_Shifty::$wpdb->update(WP_SHIFTY_TABLE, array('status' => $status), array('id' => $id));

            wp_send_json(array('status' => $status));
      }

      public static function delete_scenario(){
            self::ajax_check();

            $id = (int)$_POST['scenario'];
            $files = WP_Shifty_Helper::get_related_files($id);

            foreach ((array)$files as $file){
                  WP_Shifty_Editor::delete_file($file);
            }

            WP_Shifty::$wpdb->delete(WP_SHIFTY_TABLE, array('id' => $id));

            wp_send_json(array('message' => esc_html__('Scenario has been deleted', 'wp-shifty')));
      }

      public static function duplicate_scenario(){
            self::ajax_check();

            $id = (int)$_POST['scenario'];
            $scenario = WP_Shifty::$wpdb->get_row(WP_Shifty::$wpdb->prepare("SELECT * FROM " . WP_SHIFTY_TABLE . " WHERE id = %d", $id));
            unset($scenario->id);

            $settings = json_decode($scenario->settings, true);
            $preview = json_decode($scenario->preview, true);

            foreach ($settings['elements']['overwrite'] as $key => $data){
                  if (isset($data['overwrite']) && !empty($data['overwrite'])){
                        $file = basename($data['overwrite']);
                        $ext = pathinfo($file, PATHINFO_EXTENSION);
                        $new_file = WP_Shifty_Helper::random_id() . '.' . $ext;
                        copy (trailingslashit(WP_Shifty_Helper::upload_dir()->dir) . $file, trailingslashit(WP_Shifty_Helper::upload_dir()->dir) . $new_file);
                        $settings['elements']['overwrite'][$key]['overwrite'] = trailingslashit(WP_Shifty_Helper::upload_dir()->url) . $new_file;
                  }
            }

            WP_Shifty::$wpdb->insert(WP_SHIFTY_TABLE, array('status' => 'draft', 'settings' => json_encode($settings), 'preview' => json_encode($preview)));
            $new_id = WP_Shifty::$wpdb->insert_id;

            wp_send_json(array('message' => esc_html__('Scenario has been duplicated', 'wp-shifty'), 'url' => add_query_arg('editor', $new_id, WP_SHIFTY_ADMIN_URL)));
      }

      public static function delete_abandoned_files(){
            self::ajax_check();

            foreach ((array)$_POST['files'] as $file){
                  WP_Shifty_Editor::delete_file($file);
            }

            echo 1;die;
      }

      public static function disconnect(){
            self::ajax_check();
            delete_option('wp-shifty-license');

            try {
                  WP_Shifty_Api::request('user/disconnect');

                  wp_send_json(array(
                        'result' => 1
                  ));
            }catch (Exception $e){
                  wp_send_json(array(
                        'error' => $e->getMessage()
                  ));
            }
      }

      public static function deactivate(){
            self::ajax_check();

            if ($_POST['keep'] == 1){
                  update_option('wp-shifty-keep_settings', 1, false);
            }
            else {
                  delete_option('wp-shifty-keep_settings');
            }

            echo 1;die;
      }

      public static function measure_resource_sizes(){
            self::ajax_check();
            $result = array();

            foreach ((array)json_decode(stripslashes($_POST['files']), true) as $file){
                  if (isset($file['type']) && $file['type'] == 'inline'){
                        $result[] = array(
                              'id'  => $file['id'],
                              'size' => '~' . WP_Shifty_Helper::format_bytes(strlen($file['url'])*0.4)
                        );
                  }
                  else {
                        $url = WP_Shifty_Helper::normalize_url($file['url']);
                        $response = wp_remote_get($url, array(
                              'headers' => array(
                                    'Accept-Encoding' => 'gzip, deflate'
                              ),
                              'httpversion' => '2.0',
                              'decompress'=> false,
                              'sslverify' => false
                        ));
                        if (!is_wp_error($response)){
                              if (isset($response['headers']['content-length'])){
                                    $size = WP_Shifty_Helper::format_bytes($response['headers']['content-length']);
                              }
                              else {
                                    $size = '~' . WP_Shifty_Helper::format_bytes(strlen($response['body'])*0.4);
                              }
                              $result[] = array(
                                    'id'  => $file['id'],
                                    'size' => $size
                              );
                        }
                  }
            }

            wp_send_json(array('files' => $result));
      }

      public static function lighthouse(){
            self::ajax_check();

            $url = $_POST['url'];
            $device = $_POST['device'];

            $suggestions = array(
                  'render-blocking-resources' => array(),
                  'unused' => array(),
            );

            try {
                  $audit = WP_Shifty_Api::request('lighthouse/test', array(
                        'url' => $url,
                        'device' => $device
                  ));

                  // Render blocking resources
                  if (isset($audit['render-blocking-resources']['details']['items'])){
                        foreach($audit['render-blocking-resources']['details']['items'] as $item){
                              $suggestions['render-blocking-resources'][] = array(
                                    'wastedMs' => $item['wastedMs'] . __('ms', 'wp-shifty'),
                                    'url' => $item['url']
                              );
                        }
                  }

                  // Unused CSS
                  if (isset($audit['unused-css-rules']['details']['items'])){
                        foreach($audit['unused-css-rules']['details']['items'] as $item){
                              $suggestions['unused'][] = array(
                                    'wastedBytes' => WP_Shifty_Helper::format_bytes($item['wastedBytes']),
                                    'url' => $item['url']
                              );
                        }
                  }

                  // Unused JS
                  if (isset($audit['unused-javascript']['details']['items'])){
                        foreach($audit['unused-javascript']['details']['items'] as $item){
                              $suggestions['unused'][] = array(
                                    'wastedBytes' => WP_Shifty_Helper::format_bytes($item['wastedBytes']),
                                    'url' => $item['url']
                              );
                        }
                  }

                  wp_send_json($suggestions);

            }catch (Exception $e){
                  wp_send_json(array(
                        'error' => $e->getMessage()
                  ));
            }
      }

      public static function ajax_check(){
            if (!current_user_can('manage_options')){
                  wp_send_json(array(
                        'error' => esc_html__('Sorry, you are not allowed to run this action', 'wp-shifty')
                  ));
            }

            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wpshifty-ajax-nonce')){
                  wp_send_json(array(
                        'error' => esc_html__('Your session has timed out. Please refresh the page and try again.', 'wp-shifty')
                  ));
            }
      }

}

?>