<?php

/**
 * Plugin Name: WP Shifty
 * Plugin URI: https://wp-shifty.com
 * Description: Plugin and assets organizer
 * Version: 1.0.7
 * Author: SWTE
 * Author URI: https://swteplugins.com
 * Text Domain: wp-shifty
 */

class WP_Shifty{

      public static $instance;

      public static $wpdb;

      public static $license;

      public function __construct(){
            // Bypass $wpdb
            self::$wpdb = $GLOBALS['wpdb'];

            self::$license = get_option('wp-shifty-license');

            // Set constants
            if (!defined('WP_SHIFTY_URI')){
                  define('WP_SHIFTY_URI', trailingslashit(plugins_url() . '/'. basename(__DIR__)));
            }

            if (!defined('WP_SHIFTY_DIR')){
                  define('WP_SHIFTY_DIR', trailingslashit(__DIR__));
            }

            if (!defined('WP_SHIFTY_ADMIN_URL')){
                  define('WP_SHIFTY_ADMIN_URL', admin_url('tools.php?page=wp-shifty'));
            }

            if (!defined('WP_SHIFTY_RESOURCE_MAX_LENGTH')){
                  define('WP_SHIFTY_RESOURCE_MAX_LENGTH', 512);
            }

            if (!defined('WP_SHIFTY_UA')){
                  define('WP_SHIFTY_UA', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/95.0.4638.69 Safari/537.36');
            }

            define('WP_SHIFTY_VER', '1.0.6');

            define('WP_SHIFTY_DB_VER', '0.2');

            define('WP_SHIFTY_TABLE', self::$wpdb->prefix . 'shifty');

            define('WP_SHIFTY_API_URL', 'https://api.wp-shifty.com/v0/');

            // Simple autoload
            spl_autoload_register(function($class_name){
                  // Get class name
                  preg_match('~^WP_Shifty_(.*)~', $class_name, $matches);
                  if (isset($matches[1]) && !empty($matches[1]) ){
                        $filename = str_replace(array('.','_'), array('','-'), strtolower($matches[1]));
                        require_once WP_SHIFTY_DIR . 'classes/'.$filename.'.class.php';
                  }
            });

            // Create instance
            if (empty(WP_Shifty::$instance)){
                  WP_Shifty::$instance = $this;
            }

            // Create admin menu
            add_action('admin_menu', array(__CLASS__, 'admin_menu'));

            // Toolbar items
            add_action('admin_bar_menu', array(__CLASS__, 'toolbar'),40);

            // Enqueue assets
            add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_assets'));

            // Init editor
            add_action('admin_init', array('WP_Shifty_Editor', 'init'));

            // DB install
            add_action('init', array(__CLASS__, 'db_install'));

            // Activate license
            if (isset($_GET['wp-shifty-connect']) && !empty($_GET['wp-shifty-connect'])){
                  add_action('init', function(){
                        if (wp_verify_nonce($_GET['token'], 'wp-shifty-activate')){
                              update_option('wp-shifty-license', $_GET['wp-shifty-connect'], false);
                        }
                        wp_redirect(WP_SHIFTY_ADMIN_URL);
                        die;
                  });
            }


            // Update
            add_action('init', array(__CLASS__, 'update'));

            // Plugin links
            add_filter('plugin_action_links', function ($links, $file) {
                  if ($file == plugin_basename(__FILE__)) {
                        $settings_link = '<a href="' . WP_SHIFTY_ADMIN_URL . '">'.__('Settings','swift-performance').'</a>';
                        array_unshift($links, $settings_link);
                  }

                  return $links;
            }, 10, 2);

            // Activation redirect
            add_action('admin_init', function(){
                  if (!defined('DOING_AJAX') && get_option('wp_shifty_activate') == get_current_user_id()){
                        delete_option('wp_shifty_activate');
                        wp_redirect(WP_SHIFTY_ADMIN_URL);
                        die;
                  }
            });
            register_activation_hook( __FILE__, array(__CLASS__, 'activate'));

            // Deactivation
            add_action('pre_current_active_plugins', function(){
                  include WP_SHIFTY_DIR . 'templates/popup.tpl.php';
            });

            // Init AJAX
            WP_Shifty_Ajax::init();

            // Do the magic
            WP_Shifty_Core::init();
      }

      public static function admin_menu(){
            add_submenu_page('tools.php', __('WP Shifty', 'wp-shifty'), __('WP Shifty', 'wp-shifty'), 'manage_options', 'wp-shifty', array(__CLASS__, 'dashboard'));
      }

      public static function dashboard(){
            include WP_SHIFTY_DIR . 'templates/dashboard.tpl.php';
            include WP_SHIFTY_DIR . 'templates/popup.tpl.php';
      }

      public static function enqueue_assets($hook){
            if(in_array($hook, array('tools_page_wp-shifty', 'plugins.php'))) {
                  $css_checksum = md5_file(WP_SHIFTY_DIR . 'assets/admin.css');
                  $js_checksum = md5_file(WP_SHIFTY_DIR . 'assets/admin.js');

                  wp_enqueue_style( 'wp-shifty', WP_SHIFTY_URI . 'assets/admin.css', array(), $css_checksum );

                  wp_enqueue_script( 'wp-shifty', WP_SHIFTY_URI . 'assets/admin.js', array('jquery'), $js_checksum );
                  wp_localize_script( 'wp-shifty', 'wp_shifty', array('i18n' => WP_Shifty_I18n::localize_script(), 'nonce' => wp_create_nonce('wpshifty-ajax-nonce')) );
            }
      }

      public static function toolbar($admin_bar){
            if (current_user_can('manage_options')){
                  $admin_bar->add_menu(array(
                        'id'    => 'wp-shifty',
                        'title' => '<img src="' . WP_SHIFTY_URI . 'images/fox.png' . '" style="display:inline-block;width:20px;vertical-align:middle;">',
                        'href'  => WP_SHIFTY_ADMIN_URL
                  ));

                  $query = WP_Shifty_Query::guess();
                  $nonce = wp_create_nonce('wp-shifty-add');

                  if (isset($query['type']) && $query['type'] == 'single' && $query['post_type'] == 'page'){
                        $admin_bar->add_menu(array(
                              'id'    => 'wp-shifty-add-page-rule',
                              'parent' => 'wp-shifty',
                              'title' => __('Add rule to current page', 'wp-shifty'),
                              'href'  => add_query_arg(array('editor' => 'add-new', 'shortcut' => 'page', 'page_id' => $query['id'], 'nonce' => $nonce), WP_SHIFTY_ADMIN_URL)
                        ));
                  }
                  if (isset($query['post_type']) && $query['post_type'] != 'page'){
                        $admin_bar->add_menu(array(
                              'id'    => 'wp-shifty-add-post_type-rule',
                              'parent' => 'wp-shifty',
                              'title' => __('Add rule to current post type', 'wp-shifty'),
                              'href'  => add_query_arg(array('editor' => 'add-new', 'shortcut' => 'post_type', 'post-type' => $query['post_type'], 'nonce' => $nonce), WP_SHIFTY_ADMIN_URL)
                        ));
                  }
                  if (!is_admin()){
                        $admin_bar->add_menu(array(
                              'id'    => 'wp-shifty-add-url-rule',
                              'parent' => 'wp-shifty',
                              'title' => __('Add rule to current URL', 'wp-shifty'),
                              'href'  => add_query_arg(array('editor' => 'add-new', 'shortcut' => 'url', 'url' => $query['url'], 'nonce' => $nonce), WP_SHIFTY_ADMIN_URL)
                        ));
                  }
            }
      }

      /**
      * Create DB for the plugin
      */
      public static function db_install(){
            global $wpdb;

            $sql = "CREATE TABLE " . WP_SHIFTY_TABLE . " (
                  id INT NOT NULL AUTO_INCREMENT,
                  settings LONGTEXT NOT NULL,
                  rules LONGTEXT NOT NULL,
                  preview LONGTEXT NOT NULL,
                  status VARCHAR(100) NOT NULL,
                  PRIMARY KEY (id),
                  KEY status (status)
            );";

            $current_db_version = get_option('wp-shifty-db_version');
            if (empty($current_db_version)){
                  $wpdb->query($sql);
                  update_option( 'wp-shifty-db_version', WP_SHIFTY_DB_VER );
            }
            else if ($current_db_version !== WP_SHIFTY_DB_VER){
                  require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
                  dbDelta( $sql );

                  update_option( 'wp-shifty-db_version', WP_SHIFTY_DB_VER );
            }
      }

      public static function early_loader(){
            // Create mu-plugins dir if not exists
            if (!file_exists(WPMU_PLUGIN_DIR)){
                  @mkdir(WPMU_PLUGIN_DIR, 0777);
            }
            // Copy loader to mu-plugins
            if (file_exists(WPMU_PLUGIN_DIR)){
                  $loader = file_get_contents(WP_SHIFTY_DIR . 'templates/loader.php');
                  $loader = str_replace('%PLUGIN_NAME%', 'WP Shifty Early Loader', $loader);
                  $loader = str_replace('%PLUGIN_DIR%', WP_SHIFTY_DIR, $loader);
                  $loader = str_replace('%PLUGIN_SLUG%', basename(WP_SHIFTY_DIR) . '/wp-shifty.php', $loader);
                  @file_put_contents(trailingslashit(WPMU_PLUGIN_DIR) . '__wps-loader.php', $loader);
            }

            if (!file_exists(trailingslashit(WPMU_PLUGIN_DIR) . '__wps-loader.php')) {
                  throw new Exception(__("Can't create MU loader. Disable plugin rules will not work."));
            }
      }

      public static function update(){
            require WP_SHIFTY_DIR . 'includes/puc/plugin-update-checker.php';
            $update_checker = Puc_v4_Factory::buildUpdateChecker(WP_SHIFTY_API_URL . 'update/info/', __FILE__, 'wp-shifty/wp-shifty.php');
      }

      public static function activate(){
            update_option('wp_shifty_activate', get_current_user_id());
      }

      public static function uninstall(){
            if (get_option('wp-shifty-keep_settings') != 1){
                  self::$wpdb->query('DELETE FROM ' . self::$wpdb->options . ' WHERE option_name LIKE "wp-shifty%"');
                  self::$wpdb->query('DROP TABLE ' . WP_SHIFTY_TABLE);
            }

            $loader = trailingslashit(WPMU_PLUGIN_DIR) . '__wps-loader.php';
            if (file_exists($loader)){
                  @unlink ($loader);
            }
      }
}

new WP_Shifty();