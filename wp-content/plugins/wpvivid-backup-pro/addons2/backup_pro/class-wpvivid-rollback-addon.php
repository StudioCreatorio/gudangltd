<?php
/**
 * WPvivid addon: yes
 * Addon Name: wpvivid-backup-pro-all-in-one
 * Description: Pro
 * Version: 2.2.19
 * Need_init: yes
 * Interface Name: WPvivid_Rollback_Addon
 */
if (!defined('WPVIVID_BACKUP_PRO_PLUGIN_DIR'))
{
    die;
}

class WPvivid_Rollback_Addon
{
    public $main_tab;

    public function __construct()
    {
        add_filter( 'upgrader_pre_install', array( $this, 'backup' ), 10, 2 );
        add_filter('wpvivid_get_dashboard_menu', array($this, 'get_dashboard_menu'), 10, 2);
        add_filter('wpvivid_get_dashboard_screens', array($this, 'get_dashboard_screens'), 10);
        add_filter('wpvivid_get_toolbar_menus', array($this, 'get_toolbar_menus'),11);
        add_action('wp_ajax_wpvivid_rollback_plugin', array($this, 'rollback_plugin'));
        add_action('wp_ajax_wpvivid_rollback_theme', array($this, 'rollback_theme'));

        add_action('wp_ajax_wpvivid_enable_auto_backup', array($this, 'enable_auto_backup'));
        add_action('wp_ajax_wpvivid_theme_enable_auto_backup', array($this, 'theme_enable_auto_backup'));
        //
        add_action('wp_ajax_wpvivid_view_plugin_versions', array($this, 'view_plugin_versions'));
        add_action('wp_ajax_wpvivid_view_theme_versions', array($this, 'view_theme_versions'));
        add_action('wp_ajax_wpvivid_plugins_enable_auto_backup', array($this, 'plugins_enable_auto_backup'));
        add_action('wp_ajax_wpvivid_themes_enable_auto_backup', array($this, 'themes_enable_auto_backup'));
        //
        add_action('wp_ajax_wpvivid_get_plugins_list', array($this, 'get_plugins_list'));
        add_action('wp_ajax_wpvivid_get_themes_list', array($this, 'get_themes_list'));

        add_action('wp_ajax_wpvivid_download_rollback', array($this, 'download_rollback'));
        add_action('wp_ajax_wpvivid_download_core_rollback', array($this, 'download_core_rollback'));
        //
        add_action('wp_ajax_wpvivid_get_rollback_list', array($this, 'get_rollback_list'));
        add_action('wp_ajax_wpvivid_delete_rollback', array($this, 'delete_rollback'));
        //
        add_action('core_upgrade_preamble', array( $this,'core_auto_backup'),10);
        add_action('wpvivid_before_setup_page',array($this,'auto_backup_page'));
        //
        add_action('pre_auto_update',array($this,'auto_core_backup'),10,3);

        add_action('wp_ajax_wpvivid_enable_core_auto_backup', array($this, 'enable_core_auto_backup'));

        add_action('wp_ajax_wpvivid_rollback_core', array($this, 'rollback_core'));
        add_action('wp_ajax_wpvivid_do_rollback_core', array($this, 'do_rollback_core'));
        add_action('wp_ajax_wpvivid_get_rollback_core_progress', array($this, 'get_rollback_core_progress'));
        add_action('wp_ajax_wpvivid_delete_core_rollback', array($this, 'delete_core_rollback'));
        add_action('wp_ajax_wpvivid_get_core_list', array($this, 'get_core_list'));
        //
        add_filter('wpvivid_get_role_cap_list',array($this, 'get_caps'));
        add_action('init', array($this, 'init_rollback'));

        add_action('wp_ajax_wpvivid_set_rollback_setting', array($this, 'set_rollback_setting'));

        $this->check_schedule();
        add_action('wpvivid_check_rollback_event',array( $this,'check_rollback_event'));
    }

    public function check_schedule()
    {
        if(!defined( 'DOING_CRON' ))
        {
            if(wp_get_schedule('wpvivid_check_rollback_event')===false)
            {
                if(wp_schedule_event(time()+30, 'daily', 'wpvivid_check_rollback_event')===false)
                {
                    return false;
                }
            }
        }

        return true;
    }

    public function check_rollback_event()
    {
        set_time_limit(300);
        $this->check_plugins_versions();
        $this->check_themes_versions();
        $this->check_core_versions();
    }

    public function init_rollback()
    {
        $init=get_option('wpvivid_init_rollback_setting',false);
        if(!$init)
        {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
            $all_plugins     = get_plugins();

            $plugins_auto_backup_status=array();

            foreach ((array) $all_plugins as $plugin_file => $plugin_data)
            {
                if(!isset($plugin_data['Version'])||empty($plugin_data['Version']))
                {
                    continue;
                }

                $slug=$this->get_plugin_slug($plugin_file);

                if(is_plugin_active($plugin_file))
                {
                    $plugins_auto_backup_status[ $slug ]['enable_auto_backup']= true;
                }
            }

            update_option('wpvivid_plugins_auto_backup_status',$plugins_auto_backup_status);

            $themes =wp_get_themes();

            $themes_auto_backup_status=array();

            foreach ($themes as $key=>$theme)
            {
                if ( get_stylesheet() === $key)
                {
                    $themes_auto_backup_status[$key]=true;
                }
            }

            update_option('wpvivid_themes_auto_backup_status',$themes_auto_backup_status);
            update_option('wpvivid_init_rollback_setting',true);
        }
    }

    public function check_plugins_versions()
    {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        $all_plugins     = get_plugins();
        $counts=get_option('wpvivid_max_rollback_count',array());
        $max_plugins_count=isset($counts['max_plugins_count'])?$counts['max_plugins_count']:5;

        foreach ((array) $all_plugins as $plugin_file => $plugin_data)
        {

            $plugin['slug']=$this->get_plugin_slug($plugin_file);
            $plugin['rollback']=$this->get_rollback_data($plugin_file);

            if(!empty($plugin['rollback']))
            {
                if(sizeof($plugin['rollback'])>$max_plugins_count)
                {
                    $this->delete_old_plugins_rollback($plugin,$max_plugins_count);
                }
            }
        }
    }

    public function delete_old_plugins_rollback($plugin,$max_plugins_count)
    {
        $slug=$plugin['slug'];
        $rollback_data=$plugin['rollback'];
        uksort($rollback_data, function ($a, $b)
        {
            if($a==$b)
                return 0;

            if (version_compare($a,$b,'>'))
                return 1;
            else
                return -1;
        });

        $path=WP_CONTENT_DIR.'/'.WPvivid_Setting::get_backupdir().'/'.'rollback/plugins/'.$slug ;

        $size=sizeof($rollback_data);
        while($size>$max_plugins_count)
        {
            foreach ($rollback_data as $version=>$file)
            {
                if(file_exists($path.'/'.$version.'/'.$slug.'.zip'))
                {
                    @unlink($path.'/'.$version.'/'.$slug.'.zip');
                    @rmdir($path.'/'.$version);
                }
                unset($rollback_data[$version]);
                break;
            }
            $size=sizeof($rollback_data);
        }
    }

    public function check_themes_versions()
    {
        $themes =wp_get_themes();

        $counts=get_option('wpvivid_max_rollback_count',array());
        $max_themes_count=isset($counts['max_themes_count'])?$counts['max_themes_count']:5;

        foreach ($themes as $key=>$theme)
        {
            $theme_data["slug"]=$key;
            $theme_data['rollback']=$this->get_rollback_data($key,'themes');
            if(!empty($theme_data['rollback']))
            {
                if(sizeof($theme_data['rollback'])>$max_themes_count)
                {
                    $this->delete_old_theme_rollback($theme_data,$max_themes_count);
                }
            }
        }
    }

    public function delete_old_theme_rollback($theme_data,$max_themes_count)
    {
        $slug=$theme_data['slug'];
        $rollback_data=$theme_data['rollback'];
        uksort($rollback_data, function ($a, $b)
        {
            if($a==$b)
                return 0;

            if (version_compare($a,$b,'>'))
                return 1;
            else
                return -1;
        });

        $path=WP_CONTENT_DIR.'/'.WPvivid_Setting::get_backupdir().'/'.'rollback/themes/'.$slug ;

        $size=sizeof($rollback_data);
        while($size>$max_themes_count)
        {
            foreach ($rollback_data as $version=>$file)
            {
                if(file_exists($path.'/'.$version.'/'.$slug.'.zip'))
                {
                    @unlink($path.'/'.$version.'/'.$slug.'.zip');
                    @rmdir($path.'/'.$version);
                }
                unset($rollback_data[$version]);
                break;
            }
            $size=sizeof($rollback_data);
        }
    }

    public function check_core_versions()
    {
        $core_list=$this->get_core_data();

        $counts=get_option('wpvivid_max_rollback_count',array());
        $max_core_count=isset($counts['max_core_count'])?$counts['max_core_count']:5;
        if(!empty($core_list))
        {
            if(sizeof($core_list)>$max_core_count)
            {
                $this->delete_old_core_rollback($core_list,$max_core_count);
            }
        }
    }

    public function delete_old_core_rollback($core_list,$max_core_count)
    {
        uksort($core_list, function ($a, $b)
        {
            if($a==$b)
                return 0;

            if (version_compare($a,$b,'>'))
                return 1;
            else
                return -1;
        });

        $path=WP_CONTENT_DIR.'/'.WPvivid_Setting::get_backupdir().'/'.'rollback/core/' ;

        $size=sizeof($core_list);
        while($size>$max_core_count)
        {
            foreach ($core_list as $version=>$data)
            {
                if(file_exists($path.'/'.$version.'/wordpress.zip'))
                {
                    @unlink($path.'/'.$version.'/wordpress.zip');
                    @rmdir($path.'/'.$version);
                }
                unset($core_list[$version]);
                break;
            }
            $size=sizeof($core_list);
        }
    }

    public function get_caps($cap_list)
    {
        $cap['slug']='wpvivid-rollback';
        $cap['display']='Rollback';
        $cap['menu_slug']=strtolower(sprintf('%s-rollback', apply_filters('wpvivid_white_label_slug', 'wpvivid')));
        $cap['icon']='<span class="dashicons dashicons-update wpvivid-dashicons-grey"></span>';
        $cap['index']=13;
        $cap_list[$cap['slug']]=$cap;

        return $cap_list;
    }

    public function core_auto_backup()
    {
        $auto_backup_core=get_option('wpvivid_plugins_auto_backup_core',false);
        if($auto_backup_core===false)
        {
            return;
        }
        ?>
        <script>
            jQuery(document).ready(function ($)
            {
                $('form.upgrade[name="upgrade"]').submit(function ()
                {
                    $('form.upgrade[name="upgrade"]').attr('action', 'admin.php?page=<?php echo strtolower(apply_filters('wpvivid_white_label_slug', WPVIVID_PRO_PLUGIN_SLUG)).'-backup'; ?>&auto_backup=1&backup=core');
                });
            });
        </script>
        <?php
    }

    public function auto_backup_page()
    {
        if(isset($_REQUEST['auto_backup'])&&$_REQUEST['auto_backup']==1)
        {
            $auto_backup_core=get_option('wpvivid_plugins_auto_backup_core',false);
            if($auto_backup_core===false)
            {
                return;
            }

            $this->show_auto_backup_page();
        }
    }

    public function show_auto_backup_page()
    {
        $this->output_core_form();
        ?>
         <div class="wrap wpvivid-canvas">
            <div id="icon-options-general" class="icon32"></div>
            <h1><?php esc_attr_e( apply_filters('wpvivid_white_label_display', 'WPvivid').' Plugins - Rollback', 'wpvivid' ); ?></h1>
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <div class="meta-box-sortables ui-sortable">
                            <div class="wpvivid-backup">
                                <div class="wpvivid-welcome-bar wpvivid-clear-float">
                                    <h2>The update will start after the backup core files is finished</h2>
                                    <div class="wpvivid-one-coloum wpvivid-workflow wpvivid-clear-float" id="wpvivid_postbox_backup_percent">
                                        <p>
                                            <span class="wpvivid-span-progress">
                                                <span class="wpvivid-span-processed-progress wpvivid-span-processed-percent-progress" style="background:#007cba;width: 0%" >0% completed</span>
                                            </span>
                                        </p>
                                        <p>
                                            <span class="dashicons dashicons-backup wpvivid-dashicons-blue"></span>
                                            <span>
                                            <span>Backing up WordPress core...</span>
                                        </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
         </div>

        <script>
            var wpvivid_b_backup_core_finished=false;
            function wpvivid_simulate_backup_core_progress()
            {
                var MaxProgess = 95,
                    currentProgess = 0,
                    steps = 1,
                    time_steps=500;

                var timer = setInterval(function ()
                {
                    currentProgess += steps;
                    if(wpvivid_b_backup_core_finished)
                    {
                        currentProgess=100;
                        var progress_html='<p><span class="wpvivid-span-progress">' +
                            '<span class="wpvivid-span-processed-progress wpvivid-span-processed-percent-progress" style="background:#007cba;width: '+currentProgess+'%">' +
                            currentProgess+'% completed</span></span></p><p>' +
                            '<span><span id="wpvivid_rollback_progress_text">Backup WordPress core completed Successful</span></span></p>';
                    }
                    else
                    {
                        currentProgess += steps;
                        var progress_html='<p><span class="wpvivid-span-progress">' +
                            '<span class="wpvivid-span-processed-progress wpvivid-span-processed-percent-progress" style="background:#007cba;width: '+currentProgess+'%">' +
                            currentProgess+'% completed</span></span></p><p>' +
                            '<span><span id="wpvivid_rollback_progress_text">Backing up WordPress core...</span></span></p>';
                    }

                    jQuery("#wpvivid_postbox_backup_percent").html(progress_html);
                    if (currentProgess >= MaxProgess)
                    {
                        clearInterval(timer);
                    }
                }, time_steps);
            }

            function finish_wpvivid_auto_backup()
            {
                jQuery('#upgrade').click();
            }

            wpvivid_simulate_backup_core_progress();
        </script>
        <?php
        $ret=$this->backup_core();
        if($ret['result']=='success')
        {
            ?>
            <script>
                wpvivid_b_backup_core_finished=true;
                finish_wpvivid_auto_backup();
            </script>
            <?php
        }
        else
        {
            ?>
            <script>
                alert(<?php echo $ret['error'];?>);
            </script>
            <?php
        }
    }

    public function output_core_form()
    {
        $updates    = get_core_updates();
        foreach ( (array) $updates as $update )
        {
            $submit        = __( 'Update Now' );
            $current = false;
            if ( ! isset( $update->response ) || 'latest' == $update->response ) {
                $current = true;
            }
            if ( $current )
            {
                $form_action = 'update-core.php?action=do-core-reinstall';
            }
            else
            {
                $form_action   = 'update-core.php?action=do-core-upgrade';
            }

            //action=do-core-reinstall
            echo '<li style="display: none">';
            echo '<form method="post" action="' . $form_action . '" name="upgrade" class="upgrade">';
            wp_nonce_field( 'upgrade-core' );
            $name        = esc_attr( '_wpnonce' );
            echo '<input type="hidden" id="'.$name.'" name="'.$name.'" value="' . wp_create_nonce( 'upgrade-core' ) . '" />';
            $url=apply_filters('wpvivid_get_admin_url', '').'update-core.php';
            echo '<input type="hidden" name="_wp_http_referer" value="' . esc_attr( wp_unslash( $url ) ) . '" />';
            echo '<p>';
            echo '<input name="version" value="' . esc_attr( $update->current ) . '" />';
            echo '<input name="locale" value="' . esc_attr( $update->locale ) . '" />';
            submit_button( $submit, '', 'upgrade', false );
            echo '</p>';
            echo '</form>';
            echo '</li>';
        }
    }

    public function auto_core_backup($type, $item, $context)
    {
        $auto_backup_core=get_option('wpvivid_plugins_auto_backup_core',false);
        if($auto_backup_core===false)
        {
            return;
        }

        if ( 'core' === $type )
        {
            $this->backup_core();
        }
    }

    public function backup_core()
    {
        set_time_limit(300);

        $replace_path=$this -> transfer_path(ABSPATH);
        $files=$this->get_core_files();

        if (!class_exists('WPvivid_PclZip'))
            include_once WPVIVID_BACKUP_PRO_PLUGIN_DIR . 'includes/zip/class-wpvivid-pclzip.php';

        require ABSPATH . WPINC . '/version.php';
        global $wp_version;
        $version=$wp_version;

        $path=WP_CONTENT_DIR.'/'.WPvivid_Setting::get_backupdir().'/rollback/core/'.$version;
        $file_name='wordpress.zip';
        if(!file_exists($path))
        {
            @mkdir($path,0777,true);
        }

        if(file_exists($path.'/'.$file_name))
        {
            @unlink($path.'/'.$file_name);
        }

        $pclzip=new WPvivid_PclZip($path.'/'.$file_name);
        $ret = $pclzip -> add($files,WPVIVID_PCLZIP_OPT_REMOVE_PATH,$replace_path,WPVIVID_PCLZIP_OPT_NO_COMPRESSION,WPVIVID_PCLZIP_OPT_TEMP_FILE_THRESHOLD,16);
        if (!$ret)
        {
            $last_error = $pclzip->errorInfo(true);
            $ret['result']='failed';
            $ret['error'] = $last_error;
            return $ret;
        }
        else
        {
            $ret['result']='success';
            return $ret;
        }
    }

    public function get_core_files()
    {
        $root_path=$this->transfer_path(ABSPATH);
        $root_path=untrailingslashit($root_path);

        $include_regex=array();
        $include_regex[]='#^'.preg_quote($this -> transfer_path(ABSPATH.'wp-admin'), '/').'#';
        $include_regex[]='#^'.preg_quote($this->transfer_path(ABSPATH.'wp-includes'), '/').'#';
        $exclude_regex=array();
        $exclude_regex[]='#^'.preg_quote($this -> transfer_path(ABSPATH).'/'.'wp-config.php', '/').'#';
        $exclude_regex[]='#^'.preg_quote($this -> transfer_path(ABSPATH).'/'.'.htaccess', '/').'#';
        $files=array();
        $this->_get_files($root_path,$files,$exclude_regex,$include_regex);
        return $files;
    }

    public function _get_files($path,&$files,$exclude_regex,$include_regex)
    {
        $handler = opendir($path);

        if($handler===false)
            return;

        while (($filename = readdir($handler)) !== false)
        {
            if ($filename != "." && $filename != "..")
            {
                if (is_dir($path . '/' . $filename) && !@is_link($path . '/' . $filename))
                {
                    if ($this->regex_match($include_regex, $path . '/' . $filename, 1))
                    {
                        $this->_get_files($path . '/' . $filename,$files,$exclude_regex,$include_regex);
                    }
                }
                else
                {
                    if(is_readable($path . '/' . $filename) && !@is_link($path . '/' . $filename))
                    {
                        if($this->regex_match($exclude_regex, $this->transfer_path($path . '/' . $filename), 0))
                        {
                            $files[]=$this->transfer_path($path . '/' . $filename);
                        }
                    }
                }
            }
        }
        if($handler)
            @closedir($handler);

        return;
    }

    private function regex_match($regex_array,$string,$mode)
    {
        if(empty($regex_array))
        {
            return true;
        }

        if($mode==0)
        {
            foreach ($regex_array as $regex)
            {
                if(preg_match($regex,$string))
                {
                    return false;
                }
            }

            return true;
        }

        if($mode==1)
        {
            foreach ($regex_array as $regex)
            {
                if(preg_match($regex,$string))
                {
                    return true;
                }
            }

            return false;
        }

        return true;
    }

    public function backup($response, $args)
    {
        if ( is_wp_error( $response ) )
        {
            return $response;
        }

        $plugin = isset( $args['plugin'] ) ? $args['plugin'] : '';
        $theme = isset( $args['theme'] ) ? $args['theme'] : '';

        if(!empty($plugin))
        {
            $plugin_data = get_plugin_data( WP_PLUGIN_DIR .'/'.$plugin, false, true);
            $version=$plugin_data['Version'];

            $slug=dirname($plugin);
            if($slug=='.')
            {
                $plugin_dir=WP_PLUGIN_DIR.DIRECTORY_SEPARATOR.$plugin;

                $slug = pathinfo($plugin, PATHINFO_FILENAME);

                $path=WP_CONTENT_DIR.'/'.WPvivid_Setting::get_backupdir().'/rollback/plugins/'.$slug.'/'.$version;
                $file_name=$slug.'zip';
            }
            else
            {
                $plugin_dir=WP_PLUGIN_DIR.'/'.$slug;
                $path=WP_CONTENT_DIR.'/'.WPvivid_Setting::get_backupdir().'/rollback/plugins/'.$slug.'/'.$version;
                $file_name=$slug.'.zip';
            }

            if($this->get_enable_auto_backup_status($slug))
            {
                $plugin_dir=$this->transfer_path($plugin_dir);
                $replace_path=$this->transfer_path(WP_PLUGIN_DIR.'/');

                if (!class_exists('WPvivid_PclZip'))
                    include_once WPVIVID_BACKUP_PRO_PLUGIN_DIR . 'includes/zip/class-wpvivid-pclzip.php';

                if(!file_exists($path))
                {
                    @mkdir($path,0777,true);
                }

                if(file_exists($path.'/'.$file_name))
                {
                    @unlink($path.'/'.$file_name);
                }

                $pclzip=new WPvivid_PclZip($path.'/'.$file_name);
                $ret = $pclzip -> add($plugin_dir,WPVIVID_PCLZIP_OPT_REMOVE_PATH,$replace_path,WPVIVID_PCLZIP_OPT_NO_COMPRESSION,WPVIVID_PCLZIP_OPT_TEMP_FILE_THRESHOLD,16);
                if (!$ret)
                {
                    $last_error = $pclzip->errorInfo(true);
                    return new WP_Error('rollback_backup_failed',$last_error);
                }
            }
        }

        if(!empty($theme))
        {
            if($this->get_theme_enable_auto_backup_status($theme))
            {
                $wp_theme=wp_get_theme($theme);
                $version=$wp_theme->display( 'Version' );

                $path=WP_CONTENT_DIR.'/'.WPvivid_Setting::get_backupdir().'/rollback/themes/'.$theme.'/'.$version;
                $file_name=$theme.'.zip';

                $theme_root=$this->transfer_path(get_theme_root());
                $theme_dir=$theme_root.'/'.$theme;
                $replace_path=$this->transfer_path($theme_root.'/');

                if (!class_exists('WPvivid_PclZip'))
                    include_once WPVIVID_BACKUP_PRO_PLUGIN_DIR . 'includes/zip/class-wpvivid-pclzip.php';

                if(!file_exists($path))
                {
                    @mkdir($path,0777,true);
                }

                if(file_exists($path.'/'.$file_name))
                {
                    @unlink($path.'/'.$file_name);
                }

                $pclzip=new WPvivid_PclZip($path.'/'.$file_name);
                $ret = $pclzip -> add($theme_dir,WPVIVID_PCLZIP_OPT_REMOVE_PATH,$replace_path,WPVIVID_PCLZIP_OPT_NO_COMPRESSION,WPVIVID_PCLZIP_OPT_TEMP_FILE_THRESHOLD,16);
                if (!$ret)
                {
                    $last_error = $pclzip->errorInfo(true);
                    return new WP_Error('rollback_backup_failed',$last_error);
                }
            }
        }

        return $response;
    }

    public function transfer_path($path)
    {
        $path = str_replace('\\','/',$path);
        $values = explode('/',$path);
        return implode('/',$values);
    }

    public function get_dashboard_screens($screens)
    {
        $screen['menu_slug']='wpvivid-rollback';
        $screen['screen_id']='wpvivid-plugin_page_wpvivid-rollback';
        $screen['is_top']=false;
        $screens[]=$screen;
        return $screens;
    }

    public function get_dashboard_menu($submenus,$parent_slug)
    {
        $submenu['parent_slug'] = $parent_slug;
        $submenu['page_title'] = apply_filters('wpvivid_white_label_display', 'Rollback');
        $submenu['menu_title'] = 'Rollback';

        $submenu['capability'] = apply_filters("wpvivid_menu_capability","administrator","wpvivid-rollback");
        $submenu['menu_slug'] = strtolower(sprintf('%s-rollback', apply_filters('wpvivid_white_label_slug', 'wpvivid')));
        $submenu['index'] = 14;
        $submenu['function'] = array($this, 'init_page');
        $submenus[$submenu['menu_slug']] = $submenu;

        return $submenus;
    }

    public function get_toolbar_menus($toolbar_menus)
    {
        $admin_url = apply_filters('wpvivid_get_admin_url', '');
        $menu['id'] = 'wpvivid_admin_menu_backup_rollback';
        $menu['parent'] = 'wpvivid_admin_menu';
        $menu['title'] = 'Rollback';
        $menu['tab'] = 'admin.php?page=' . apply_filters('wpvivid_white_label_plugin_name', 'wpvivid-rollback');
        $menu['href'] = $admin_url . 'admin.php?page=' . apply_filters('wpvivid_white_label_plugin_name', 'wpvivid').'-rollback';

        $menu['capability'] = apply_filters("wpvivid_menu_capability","administrator","wpvivid-rollback");

        $menu['index'] = 7;
        $toolbar_menus[$menu['parent']]['child'][$menu['id']] = $menu;
        return $toolbar_menus;
    }

    public function init_page()
    {
        ?>
        <div class="wrap wpvivid-canvas">
            <div id="icon-options-general" class="icon32"></div>
            <h1><?php esc_attr_e( apply_filters('wpvivid_white_label_display', 'WPvivid').' Plugins - Rollback', 'wpvivid' ); ?></h1>
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <div class="meta-box-sortables ui-sortable">
                            <div class="wpvivid-backup">
                                <div class="wpvivid-welcome-bar wpvivid-clear-float">
                                    <div class="wpvivid-welcome-bar-left">
                                        <p>
                                            <span class="dashicons dashicons-update-alt wpvivid-dashicons-large wpvivid-dashicons-blue"></span>
                                            <span class="wpvivid-page-title">Rollback</span>
                                        </p>
                                        <span class="about-description">Perform a return to a prior state of plugins, themes and Wordpress core.</span>
                                    </div>
                                    <div class="wpvivid-welcome-bar-right">
                                        <p></p>
                                        <div style="float:right;">
                                            <span>Local Time:</span>
                                            <span>
                                <a href="<?php esc_attr_e(apply_filters('wpvivid_get_admin_url', '').'options-general.php'); ?>">
                                    <?php
                                    $offset=get_option('gmt_offset');
                                    echo date("l, F-d-Y H:i",time()+$offset*60*60);
                                    ?>
                                </a>
                            </span>
                                            <span class="dashicons dashicons-editor-help wpvivid-dashicons-editor-help wpvivid-tooltip">
                                <div class="wpvivid-left">
                                    <!-- The content you need -->
                                    <p>Clicking the date and time will redirect you to the WordPress General Settings page where you can change your timezone settings.</p>
                                    <i></i> <!-- do not delete this line -->
                                </div>
                            </span>
                                        </div>
                                    </div>
                                    <div class="wpvivid-nav-bar wpvivid-clear-float">
                                        <span class="dashicons  dashicons-editor-help wpvivid-dashicons-orange"></span>
                                        <span>
                            <strong>Please do not close or refresh the page when a rollback task is running.</strong>
                        </span>
                                    </div>
                                </div>
                                <div class="wpvivid-canvas wpvivid-clear-float">
                                    <?php
                                    if(!class_exists('WPvivid_Tab_Page_Container_Ex'))
                                        include_once WPVIVID_BACKUP_PRO_PLUGIN_DIR . 'includes/class-wpvivid-tab-page-container-ex.php';
                                    $this->main_tab=new WPvivid_Tab_Page_Container_Ex();

                                    $args['span_class']='dashicons dashicons-admin-plugins';
                                    $args['span_style']='color:#007cba; padding-right:0.5em;margin-top:0.2em;';
                                    $args['div_style']='padding-top:0;display:block;';
                                    $args['is_parent_tab']=0;

                                    $tabs['plugins']['title']='Plugins';
                                    $tabs['plugins']['slug']='plugins';
                                    $tabs['plugins']['callback']=array($this, 'output_plugins');
                                    $tabs['plugins']['args']=$args;

                                    $args['span_class']='dashicons dashicons-admin-appearance';
                                    $args['div_style']='';

                                    $tabs['themes']['title']='Themes';
                                    $tabs['themes']['slug']='themes';
                                    $tabs['themes']['callback']=array($this, 'output_themes');
                                    $tabs['themes']['args']=$args;

                                    $args['span_class']='dashicons dashicons-wordpress';

                                    $tabs['core']['title']='Wordpress Core';
                                    $tabs['core']['slug']='core';
                                    $tabs['core']['callback']=array($this, 'output_core');
                                    $tabs['core']['args']=$args;

                                    $args['span_class']='dashicons dashicons-admin-plugins';

                                    $tabs['settings']['title']='Settings';
                                    $tabs['settings']['slug']='settings';
                                    $tabs['settings']['callback']=array($this, 'output_settings');
                                    $tabs['settings']['args']=$args;

                                    $args['span_class']='dashicons dashicons-admin-plugins';
                                    $args['can_delete']=1;
                                    $args['hide']=1;
                                    $tabs['version_backup']['title']='Versioning Backups';
                                    $tabs['version_backup']['slug']='version_backup';
                                    $tabs['version_backup']['callback']=array($this, 'output_version_backup');
                                    $tabs['version_backup']['args']=$args;

                                    foreach ($tabs as $key=>$tab)
                                    {
                                        $this->main_tab->add_tab($tab['title'],$tab['slug'],$tab['callback'], $tab['args']);
                                    }

                                    $this->main_tab->display();
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                    $this->add_sidebar();
                    ?>
                </div>
            </div>
        </div>
        <?php
    }

    public function add_sidebar()
    {
        if(apply_filters('wpvivid_show_sidebar',true))
        {
            $href = 'https://docs.wpvivid.com/wpvivid-backup-pro-rollback-overview.html';

            ?>
            <div id="postbox-container-1" class="postbox-container">
                <div class="meta-box-sortables ui-sortable">
                    <div class="postbox  wpvivid-sidebar">
                        <h2 style="margin-top:0.5em;"><span class="dashicons dashicons-sticky wpvivid-dashicons-orange"></span>
                            <span><?php esc_attr_e(
                                    'Troubleshooting', 'WpAdminStyle'
                                ); ?></span></h2>
                        <div class="inside" style="padding-top:0;">
                            <ul class="" >
                                <li style="border-top:1px solid #f1f1f1;"><span class="dashicons dashicons-editor-help wpvivid-dashicons-orange" ></span>
                                    <a href="https://docs.wpvivid.com/troubleshooting"><b>Troubleshooting</b></a>
                                    <small><span style="float: right;"><a href="#" style="text-decoration: none;"><span class="dashicons dashicons-migrate wpvivid-dashicons-grey"></span></a></span></small><br>
                                </li>
                                <li style="border-top:1px solid #f1f1f1;"><span class="dashicons dashicons-admin-generic wpvivid-dashicons-orange" ></span>
                                    <a href="https://docs.wpvivid.com/wpvivid-backup-pro-advanced-settings.html"><b>Adjust Advanced Settings </b></a>
                                    <small><span style="float: right;"><a href="#" style="text-decoration: none;"><span class="dashicons dashicons-migrate wpvivid-dashicons-grey"></span></a></span></small><br>
                                </li>

                            </ul>
                        </div>

                        <h2><span class="dashicons dashicons-book-alt wpvivid-dashicons-orange" ></span>
                            <span><?php esc_attr_e(
                                    'Documentation', 'WpAdminStyle'
                                ); ?></span></h2>
                        <div class="inside" style="padding-top:0;">
                            <ul class="">
                                <li style="border-top:1px solid #f1f1f1;"><span class="dashicons dashicons-update wpvivid-dashicons-grey"></span>
                                    <a href="<?php echo $href; ?>"><b><?php echo 'Rollback'; ?></b></a>
                                    <small><span style="float: right;"><a href="<?php echo esc_url(apply_filters('wpvivid_white_label_page_redirect', apply_filters('wpvivid_get_admin_url', '').'admin.php?page=wpvivid-rollback', 'wpvivid-rollback')); ?>" style="text-decoration: none;"><span class="dashicons dashicons-migrate wpvivid-dashicons-grey"></span></a></span></small><br>

                                </li>
                            </ul>
                        </div>

                        <?php
                        if(apply_filters('wpvivid_show_submit_ticket',true))
                        {
                            ?>
                            <h2>
                                <span class="dashicons dashicons-businesswoman wpvivid-dashicons-green"></span>
                                <span><?php esc_attr_e(
                                        'Support', 'WpAdminStyle'
                                    ); ?></span>
                            </h2>
                            <div class="inside">
                                <ul class="">
                                    <li><span class="dashicons dashicons-admin-comments wpvivid-dashicons-green"></span>
                                        <a href="https://wpvivid.com/submit-ticket"><b>Submit A Ticket</b></a>
                                        <br>
                                        The ticket system is for <?php echo apply_filters('wpvivid_white_label_display', 'WPvivid'); ?> Pro users only. If you need any help with our plugin, submit a ticket and we will respond shortly.
                                    </li>
                                </ul>
                            </div>
                            <!-- .inside -->
                            <?php
                        }
                        ?>

                    </div>
                    <!-- .postbox -->

                </div>
                <!-- .meta-box-sortables -->

            </div>
            <?php
        }
    }

    public function output_plugins()
    {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        $all_plugins     = get_plugins();
        $current = get_site_transient( 'update_plugins' );
        $plugins = array();

        $plugins_auto_backup_status=get_option('wpvivid_plugins_auto_backup_status',array());

        foreach ((array) $all_plugins as $plugin_file => $plugin_data)
        {
            if(!isset($plugin_data['Version'])||empty($plugin_data['Version']))
            {
                continue;
            }

            if ( isset( $current->response[ $plugin_file ] ) )
            {
                $plugins[ $plugin_file ]= $plugin_data;
                $plugins[ $plugin_file ]['response']= (array)$current->response[ $plugin_file ];
            }
            else if( isset( $current->no_update[ $plugin_file ] ) )
            {
                $plugins[ $plugin_file ]= $plugin_data;
                $plugins[ $plugin_file ]['response']= (array)$current->no_update[ $plugin_file ];
            }
            else
            {
                $plugins[ $plugin_file ]= $plugin_data;
                $plugins[ $plugin_file ]['response']['new_version']='-';
                $plugins[ $plugin_file ]['response']['slug']=$this->get_plugin_slug($plugin_file);
            }

            if(isset($plugins_auto_backup_status[$plugins[ $plugin_file ]['response']['slug']]))
            {
                $plugins[ $plugin_file ]['enable_auto_backup']= $plugins_auto_backup_status[$plugins[ $plugin_file ]['response']['slug']];
            }
            else
            {
                $plugins[ $plugin_file ]['enable_auto_backup']= false;
            }

            $plugins[ $plugin_file ]['rollback']=$this->get_rollback_data($plugin_file);
        }

        ?>
        <div id="wpvivid_backup_plugin_progress" class="wpvivid-one-coloum wpvivid-workflow wpvivid-clear-float" style="display: none">
            <p>
                <span class="wpvivid-span-progress">
                    <span class="wpvivid-span-processed-progress wpvivid-span-processed-percent-progress" style="background:#007cba;width: 0%" >0% completed</span>
                </span>
            </p>
            <p>
                <span class="dashicons dashicons-backup wpvivid-dashicons-blue"></span>
                <span>
                    <span id="wpvivid_rollback_progress_text">Rolling back the plugin...</span>
                </span>
            </p>
        </div>
        <div id="wpvivid_plugins_list">
            <?php
            $table=new WPvivid_Rollback_Plugins_List();
            $table->set_list($plugins);
            $table->prepare_items();
            $table->display();
            ?>
        </div>
        <script>
            var wpvivid_current_rollback_slug='';
            var wpvivid_b_rollback_finished=false;
            var wpvivid_current_type='';
            jQuery('#wpvivid_plugins_list').on("click",'.wpvivid-enable-auto-backup',function()
            {
                var Obj=jQuery(this);
                var slug=Obj.closest('tr').attr('id');
                var enable=true;
                if (Obj.is(":checked"))
                {
                    enable=true;
                }
                else
                {
                    enable=false;
                }

                wpvivid_enable_auto_backup(slug,enable,Obj);
            });

            jQuery('#wpvivid_plugins_list').on("click",'.wpvivid-plugin-rollback',function()
            {
                var Obj=jQuery(this);
                var slug=Obj.closest('tr').attr('id');
                var version=Obj.prev('select').val();
                if(version!=='-')
                {
                    var descript = '<?php _e('Are you sure to rollback the plugin?', 'wpvivid'); ?>';

                    var ret = confirm(descript);
                    if(ret === true)
                    {
                        wpvivid_rollback_plugin(slug,version,Obj);
                    }
                }
            });

            jQuery('#wpvivid_plugins_list').on("click",'.wpvivid-view-plugin-versions',function()
            {
                var Obj=jQuery(this);
                var slug=Obj.closest('tr').attr('id');

                wpvivid_view_plugin_versions(slug);
            });

            function wpvivid_plugins_enable_auto_backup(enable)
            {
                var plugins= new Array();
                var count = 0;

                jQuery('#wpvivid_plugins_list th input').each(function (i)
                {
                    if(jQuery(this).prop('checked'))
                    {
                        plugins[count] =jQuery(this).closest('tr').attr('id');
                        count++;
                    }
                });

                if( count === 0 )
                {
                    alert('<?php _e('Please select at least one item.','wpvivid'); ?>');
                    return;
                }

                var ajax_data = {
                    'action': 'wpvivid_plugins_enable_auto_backup',
                    'plugins': plugins,
                    'enable': enable,
                };

                wpvivid_post_request_addon(ajax_data, function (data)
                {
                    var jsonarray = jQuery.parseJSON(data);
                    if (jsonarray.result === 'success')
                    {
                        jQuery('#wpvivid_plugins_list th input').each(function (i)
                        {
                            if(jQuery(this).prop('checked'))
                            {
                                if(enable=='enable')
                                {
                                    jQuery(this).parent().next().children().children(".wpvivid-enable-auto-backup").prop('checked', true);

                                }
                                else
                                {
                                    jQuery(this).parent().next().children().children(".wpvivid-enable-auto-backup").prop('checked', false);

                                }
                            }
                        });
                    }
                    else
                    {
                        alert(jsonarray.error);
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown)
                {
                    var error_message = wpvivid_output_ajaxerror('plugins enable', textStatus, errorThrown);
                    alert(error_message);
                });
            }

            function wpvivid_simulate_rollback_progress()
            {
                var MaxProgess = 95,
                    currentProgess = 0,
                    steps = 1,
                    time_steps=500;

                var timer = setInterval(function ()
                {
                    if(wpvivid_b_rollback_finished)
                    {
                        currentProgess=100;
                        var progress_html='<p><span class="wpvivid-span-progress">' +
                            '<span class="wpvivid-span-processed-progress wpvivid-span-processed-percent-progress" style="background:#007cba;width: '+currentProgess+'%">' +
                            currentProgess+'% completed</span></span></p><p>' +
                            '<span><span id="wpvivid_rollback_progress_text">Rolling back the plugin completed successfully.</span></span></p>';
                    }
                    else
                    {
                        currentProgess += steps;
                        var progress_html='<p><span class="wpvivid-span-progress">' +
                            '<span class="wpvivid-span-processed-progress wpvivid-span-processed-percent-progress" style="background:#007cba;width: '+currentProgess+'%">' +
                            currentProgess+'% completed</span></span></p><p>' +
                            '<span><span id="wpvivid_rollback_progress_text">Rolling back the plugin...</span></span></p>';
                    }

                    jQuery("#wpvivid_backup_plugin_progress").html(progress_html);
                    if (currentProgess >= MaxProgess)
                    {
                        clearInterval(timer);
                    }
                }, time_steps);
            }

            function wpvivid_rollback_plugin(slug,version,Obj)
            {
                var ajax_data = {
                    'action':'wpvivid_rollback_plugin',
                    'slug':slug,
                    'version':version
                };

                wpvivid_b_rollback_finished=false;
                jQuery('#wpvivid_backup_plugin_progress').show();
                jQuery('.wpvivid-span-processed-progress').html("0% completed");
                jQuery(".wpvivid-span-processed-progress").width( "0%" );
                jQuery('#wpvivid_rollback_progress_text').html("Rolling back the plugin...");

                wpvivid_simulate_rollback_progress();

                wpvivid_post_request_addon(ajax_data, function(data)
                {
                    try
                    {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success')
                        {
                            wpvivid_b_rollback_finished=true;
                            jQuery('.wpvivid-span-processed-progress').html("100% completed");
                            jQuery(".wpvivid-span-processed-progress").width( "100%" );
                            jQuery('#wpvivid_rollback_progress_text').html("Rollback has completed successfully.");
                            var span=Obj.parent().prev().prev().children(".current-version");
                            span.html(version);
                            setTimeout(function()
                            {
                                jQuery('#wpvivid_backup_plugin_progress').hide();
                                alert("Rolling back the plugin completed successfully.");
                            }, 1200);
                        }
                        else
                        {
                            jQuery('#wpvivid_backup_plugin_progress').hide();
                            alert(jsonarray.error);
                        }
                    }
                    catch(err)
                    {
                        jQuery('#wpvivid_backup_plugin_progress').hide();
                        alert(err);
                    }
                },function(XMLHttpRequest, textStatus, errorThrown)
                {
                    jQuery('#wpvivid_backup_plugin_progress').hide();
                    var error_message = wpvivid_output_ajaxerror('rollback', textStatus, errorThrown);
                    alert(error_message);
                });
            }

            function wpvivid_enable_auto_backup(slug,enable,Obj)
            {
                var ajax_data = {
                    'action':'wpvivid_enable_auto_backup',
                    'slug':slug,
                    'enable':enable
                };

                wpvivid_post_request_addon(ajax_data, function(data)
                {
                    try
                    {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success')
                        {

                        }
                        else
                        {
                            alert(jsonarray.error);
                        }
                    }
                    catch(err)
                    {
                        alert(err);
                    }
                },function(XMLHttpRequest, textStatus, errorThrown)
                {
                    var error_message = wpvivid_output_ajaxerror('enable auto backup', textStatus, errorThrown);
                    alert(error_message);
                });
            }

            function wpvivid_view_plugin_versions(slug)
            {
                var ajax_data = {
                    'action':'wpvivid_view_plugin_versions',
                    'slug':slug
                };

                wpvivid_post_request_addon(ajax_data, function(data)
                {
                    try
                    {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success')
                        {
                            wpvivid_current_type='plugins';
                            wpvivid_current_rollback_slug=slug;
                            jQuery('#wpvivid_rollback_detail').html(jsonarray.detail);
                            jQuery('#wpvivid_rollback_backup_list').html(jsonarray.backup_list);
                            jQuery( document ).trigger( '<?php echo $this->main_tab->container_id ?>-show',[ 'version_backup', 'plugins' ]);
                        }
                        else
                        {
                            alert(jsonarray.error);
                        }
                    }
                    catch(err)
                    {
                        alert(err);
                    }
                },function(XMLHttpRequest, textStatus, errorThrown)
                {
                    var error_message = wpvivid_output_ajaxerror('get plugin detail', textStatus, errorThrown);
                    alert(error_message);
                });
            }

            jQuery('#wpvivid_plugins_list').on("click",'#wpvivid_apply_plugins_bulk_action',function()
            {
                var action=jQuery('#wpvivid_plugins_bulk_action').val();
                if(action=='-1')
                {

                }
                else
                {
                    wpvivid_plugins_enable_auto_backup(action);
                }
            });

            jQuery('#wpvivid_plugins_list').on('click', 'thead tr td input', function()
            {
                wpvivid_control_plugins_select(jQuery(this));
            });

            jQuery('#wpvivid_plugins_list').on('click', 'tfoot tr td input', function()
            {
                wpvivid_control_plugins_select(jQuery(this));
            });

            function wpvivid_control_plugins_select(obj)
            {
                if(jQuery(obj).prop('checked'))
                {
                    jQuery('#wpvivid_plugins_list thead tr td input').each(function()
                    {
                        jQuery(this).prop('checked', true);
                    });

                    jQuery('#wpvivid_plugins_list tfoot tr td input').each(function()
                    {
                        jQuery(this).prop('checked', true);
                    });

                    jQuery('#wpvivid_plugins_list tbody tr').each(function()
                    {
                        jQuery(this).find('th input').prop('checked', true);
                    });
                }
                else
                {
                    jQuery('#wpvivid_plugins_list thead tr td input').each(function()
                    {
                        jQuery(this).prop('checked', false);
                    });

                    jQuery('#wpvivid_plugins_list tfoot tr td input').each(function()
                    {
                        jQuery(this).prop('checked', false);
                    });

                    jQuery('#wpvivid_plugins_list tbody tr').each(function ()
                    {
                        jQuery(this).find('th input').prop('checked', false);
                    });
                }
            }

            jQuery('#wpvivid_plugins_list').on("click",'.first-page',function()
            {
                wpvivid_get_plugins_list('first');
            });

            jQuery('#wpvivid_plugins_list').on("click",'.prev-page',function()
            {
                var page=parseInt(jQuery(this).attr('value'));
                wpvivid_get_plugins_list(page-1);
            });

            jQuery('#wpvivid_plugins_list').on("click",'.next-page',function()
            {
                var page=parseInt(jQuery(this).attr('value'));
                wpvivid_get_plugins_list(page+1);
            });

            jQuery('#wpvivid_plugins_list').on("click",'.last-page',function()
            {
                wpvivid_get_plugins_list('last');
            });

            jQuery('#wpvivid_plugins_list').on("keypress", '.current-page', function()
            {
                if(event.keyCode === 13)
                {
                    var page = jQuery(this).val();
                    wpvivid_get_plugins_list(page);
                }
            });

            function wpvivid_get_plugins_list(page=0)
            {
                if(page==0)
                {
                    page =jQuery('#wpvivid_plugins_list').find('.current-page').val();
                }

                var ajax_data = {
                    'action': 'wpvivid_get_plugins_list',
                    'page':page
                };
                wpvivid_post_request_addon(ajax_data, function (data)
                {
                    jQuery('#wpvivid_plugins_list').html('');
                    try
                    {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success')
                        {
                            jQuery('#wpvivid_plugins_list').html(jsonarray.html);
                        }
                        else
                        {
                            alert(jsonarray.error);
                        }
                    }
                    catch (err)
                    {
                        alert(err);
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown)
                {
                    var error_message = wpvivid_output_ajaxerror('achieving plugins', textStatus, errorThrown);
                    alert(error_message);
                });
            }

            wpvivid_get_plugins_list();
        </script>
        <?php
    }

    public function output_themes()
    {
        $current = get_site_transient( 'update_themes' );
        $themes =wp_get_themes();
        $themes_list=array();

        $themes_auto_backup_status=get_option('wpvivid_themes_auto_backup_status',array());

        foreach ($themes as $key=>$theme)
        {
            $stylesheet=$theme->get_stylesheet();
            $them_data["name"]=$theme->display( 'Name' );
            $them_data["version"]=$theme->display( 'Version' );
            $them_data["slug"]=$key;

            if ( isset( $current->response[ $stylesheet ] ) )
            {
                $update=(array)$current->response[ $stylesheet ];
                $them_data["new_version"]=$update['new_version'];
            }
            else if( isset( $current->no_update[ $stylesheet ] ) )
            {
                $update=(array)$current->no_update[ $stylesheet ];
                $them_data["new_version"]=$update['new_version'];
            }
            else
            {
                $them_data['new_version']='-';
            }

            $them_data['rollback']=$this->get_rollback_data($key,'themes');

            if(isset($themes_auto_backup_status[$key]))
            {
                $them_data['enable_auto_backup']= $themes_auto_backup_status[$key];
            }
            else
            {
                $them_data['enable_auto_backup']= false;
            }

            $themes_list[ $stylesheet ]= $them_data;
        }
        ?>
        <div id="wpvivid_backup_themes_progress" class="wpvivid-one-coloum wpvivid-workflow wpvivid-clear-float" style="display: none">
            <p>
                <span class="wpvivid-span-progress">
                    <span class="wpvivid-span-processed-progress wpvivid-span-processed-percent-progress" style="background:#007cba;width: 0%" >0% completed</span>
                </span>
            </p>
            <p>
                <span class="dashicons dashicons-backup wpvivid-dashicons-blue"></span>
                <span>
                    <span>Rolling back the theme...</span>
                </span>
            </p>
        </div>
        <div id="wpvivid_themes_list">
            <?php
            $table=new WPvivid_Themes_List();
            $table->set_list($themes_list);
            $table->prepare_items();
            $table->display();
            ?>
        </div>
        <script>
            jQuery('#wpvivid_themes_list').on("click",'.wpvivid-enable-auto-backup',function()
            {
                var Obj=jQuery(this);
                var slug=Obj.closest('tr').attr('id');
                var enable=true;
                if (Obj.is(":checked"))
                {
                    enable=true;
                }
                else
                {
                    enable=false;
                }

                wpvivid_theme_enable_auto_backup(slug,enable,Obj);
            });

            jQuery('#wpvivid_themes_list').on("click",'.wpvivid-theme-rollback',function()
            {
                var Obj=jQuery(this);
                var slug=Obj.closest('tr').attr('id');
                var version=Obj.prev('select').val();
                if(version!=='-')
                {
                    var descript = '<?php _e('Are you sure to rollback the theme?', 'wpvivid'); ?>';

                    var ret = confirm(descript);
                    if(ret === true)
                    {
                        wpvivid_rollback_theme(slug,version,Obj);
                    }
                }
            });

            jQuery('#wpvivid_themes_list').on("click",'.wpvivid-view-theme-versions',function()
            {
                var Obj=jQuery(this);
                var slug=Obj.closest('tr').attr('id');

                wpvivid_view_theme_versions(slug);
            });

            function wpvivid_themes_enable_auto_backup(enable)
            {
                var themes= new Array();
                var count = 0;

                jQuery('#wpvivid_themes_list th input').each(function (i)
                {
                    if(jQuery(this).prop('checked'))
                    {
                        themes[count] =jQuery(this).closest('tr').attr('id');
                        count++;
                    }
                });

                if( count === 0 )
                {
                    alert('<?php _e('Please select at least one item.','wpvivid'); ?>');
                    return;
                }

                var ajax_data = {
                    'action': 'wpvivid_themes_enable_auto_backup',
                    'themes': themes,
                    'enable': enable,
                };

                wpvivid_post_request_addon(ajax_data, function (data)
                {
                    var jsonarray = jQuery.parseJSON(data);
                    if (jsonarray.result === 'success')
                    {
                        jQuery('#wpvivid_themes_list th input').each(function (i)
                        {
                            if(jQuery(this).prop('checked'))
                            {
                                if(enable=='enable')
                                {
                                    jQuery(this).parent().next().children().children(".wpvivid-enable-auto-backup").prop('checked', true);

                                }
                                else
                                {
                                    jQuery(this).parent().next().children().children(".wpvivid-enable-auto-backup").prop('checked', false);

                                }
                            }
                        });
                    }
                    else
                    {
                        alert(jsonarray.error);
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown)
                {
                    var error_message = wpvivid_output_ajaxerror('themes enable', textStatus, errorThrown);
                    alert(error_message);
                });
            }

            function wpvivid_simulate_theme_rollback_progress()
            {
                var MaxProgess = 95,
                    currentProgess = 0,
                    steps = 1,
                    time_steps=500;

                var timer = setInterval(function ()
                {
                    if(wpvivid_b_rollback_finished)
                    {
                        currentProgess=100;
                        var progress_html='<p><span class="wpvivid-span-progress">' +
                            '<span class="wpvivid-span-processed-progress wpvivid-span-processed-percent-progress" style="background:#007cba;width: '+currentProgess+'%">' +
                            currentProgess+'% completed</span></span></p><p>' +
                            '<span><span id="wpvivid_rollback_progress_text">Rolling back the theme completed successfully.</span></span></p>';
                    }
                    else
                    {
                        currentProgess += steps;
                        var progress_html='<p><span class="wpvivid-span-progress">' +
                            '<span class="wpvivid-span-processed-progress wpvivid-span-processed-percent-progress" style="background:#007cba;width: '+currentProgess+'%">' +
                            currentProgess+'% completed</span></span></p><p>' +
                            '<span><span id="wpvivid_rollback_progress_text">Rolling back the theme...</span></span></p>';
                    }

                    jQuery("#wpvivid_backup_themes_progress").html(progress_html);
                    if (currentProgess >= MaxProgess)
                    {
                        clearInterval(timer);
                    }
                }, time_steps);
            }

            function wpvivid_rollback_theme(slug,version,Obj)
            {
                var ajax_data = {
                    'action':'wpvivid_rollback_theme',
                    'slug':slug,
                    'version':version
                };

                wpvivid_b_rollback_finished=false;
                jQuery('#wpvivid_backup_themes_progress').show();
                jQuery('.wpvivid-span-processed-progress').html("0% completed");
                jQuery(".wpvivid-span-processed-progress").width( "0%" );
                jQuery('#wpvivid_rollback_progress_text').html("Rolling back the theme...");

                wpvivid_simulate_theme_rollback_progress();

                wpvivid_post_request_addon(ajax_data, function(data)
                {
                    try
                    {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success')
                        {
                            wpvivid_b_rollback_finished=true;
                            jQuery('.wpvivid-span-processed-progress').html("100% completed");
                            jQuery(".wpvivid-span-processed-progress").width( "100%" );
                            jQuery('#wpvivid_rollback_progress_text').html("Rolling back the theme completed successfully.");
                            var span=Obj.parent().prev().prev().children(".current-version");
                            span.html(version);
                            setTimeout(function()
                            {
                                jQuery('#wpvivid_backup_themes_progress').hide();
                                alert("Rolling back the theme completed successfully.");
                            }, 1200);
                        }
                        else
                        {
                            jQuery('#wpvivid_backup_themes_progress').hide();
                            alert(jsonarray.error);
                        }
                    }
                    catch(err)
                    {
                        jQuery('#wpvivid_backup_themes_progress').hide();
                        alert(err);
                    }
                },function(XMLHttpRequest, textStatus, errorThrown)
                {
                    jQuery('#wpvivid_backup_themes_progress').hide();
                    var error_message = wpvivid_output_ajaxerror('rollback', textStatus, errorThrown);
                    alert(error_message);
                });
            }

            function wpvivid_theme_enable_auto_backup(slug,enable,Obj)
            {
                var ajax_data = {
                    'action':'wpvivid_theme_enable_auto_backup',
                    'slug':slug,
                    'enable':enable
                };

                wpvivid_post_request_addon(ajax_data, function(data)
                {
                    try
                    {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success')
                        {

                        }
                        else
                        {
                            alert(jsonarray.error);
                        }
                    }
                    catch(err)
                    {
                        alert(err);
                    }
                },function(XMLHttpRequest, textStatus, errorThrown)
                {
                    var error_message = wpvivid_output_ajaxerror('enable auto backup', textStatus, errorThrown);
                    alert(error_message);
                });
            }

            function wpvivid_view_theme_versions(slug)
            {
                var ajax_data = {
                    'action':'wpvivid_view_theme_versions',
                    'slug':slug
                };

                wpvivid_post_request_addon(ajax_data, function(data)
                {
                    try
                    {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success')
                        {
                            wpvivid_current_type='themes';
                            wpvivid_current_rollback_slug=slug;
                            jQuery('#wpvivid_rollback_detail').html(jsonarray.detail);
                            jQuery('#wpvivid_rollback_backup_list').html(jsonarray.backup_list);
                            jQuery( document ).trigger( '<?php echo $this->main_tab->container_id ?>-show',[ 'version_backup', 'themes' ]);
                        }
                        else
                        {
                            alert(jsonarray.error);
                        }
                    }
                    catch(err)
                    {
                        alert(err);
                    }
                },function(XMLHttpRequest, textStatus, errorThrown)
                {
                    var error_message = wpvivid_output_ajaxerror('get themes detail', textStatus, errorThrown);
                    alert(error_message);
                });
            }

            jQuery('#wpvivid_themes_list').on("click",'#wpvivid_apply_themes_bulk_action',function()
            {
                var action=jQuery('#wpvivid_themes_bulk_action').val();
                if(action=='-1')
                {
                    return;
                }
                else
                {
                    wpvivid_themes_enable_auto_backup(action);
                }
            });

            jQuery('#wpvivid_themes_list').on('click', 'thead tr td input', function()
            {
                wpvivid_control_themes_select(jQuery(this));
            });

            jQuery('#wpvivid_themes_list').on('click', 'tfoot tr td input', function()
            {
                wpvivid_control_themes_select(jQuery(this));
            });

            function wpvivid_control_themes_select(obj)
            {
                if(jQuery(obj).prop('checked'))
                {
                    jQuery('#wpvivid_themes_list thead tr td input').each(function()
                    {
                        jQuery(this).prop('checked', true);
                    });

                    jQuery('#wpvivid_themes_list tfoot tr td input').each(function()
                    {
                        jQuery(this).prop('checked', true);
                    });

                    jQuery('#wpvivid_themes_list tbody tr').each(function()
                    {
                        jQuery(this).find('th input').prop('checked', true);
                    });
                }
                else
                {
                    jQuery('#wpvivid_themes_list thead tr td input').each(function()
                    {
                        jQuery(this).prop('checked', false);
                    });

                    jQuery('#wpvivid_themes_list tfoot tr td input').each(function()
                    {
                        jQuery(this).prop('checked', false);
                    });

                    jQuery('#wpvivid_themes_list tbody tr').each(function ()
                    {
                        jQuery(this).find('th input').prop('checked', false);
                    });
                }
            }

            jQuery('#wpvivid_themes_list').on("click",'.first-page',function()
            {
                wpvivid_get_themes_list('first');
            });

            jQuery('#wpvivid_themes_list').on("click",'.prev-page',function()
            {
                var page=parseInt(jQuery(this).attr('value'));
                wpvivid_get_themes_list(page-1);
            });

            jQuery('#wpvivid_themes_list').on("click",'.next-page',function()
            {
                var page=parseInt(jQuery(this).attr('value'));
                wpvivid_get_themes_list(page+1);
            });

            jQuery('#wpvivid_themes_list').on("click",'.last-page',function()
            {
                wpvivid_get_themes_list('last');
            });

            jQuery('#wpvivid_themes_list').on("keypress", '.current-page', function()
            {
                if(event.keyCode === 13)
                {
                    var page = jQuery(this).val();
                    wpvivid_get_themes_list(page);
                }
            });

            function wpvivid_get_themes_list(page=0)
            {
                if(page==0)
                {
                    page =jQuery('#wpvivid_themes_list').find('.current-page').val();
                }

                var ajax_data = {
                    'action': 'wpvivid_get_themes_list',
                    'page':page
                };
                wpvivid_post_request_addon(ajax_data, function (data)
                {
                    jQuery('#wpvivid_themes_list').html('');
                    try
                    {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success')
                        {
                            jQuery('#wpvivid_themes_list').html(jsonarray.html);
                        }
                        else
                        {
                            alert(jsonarray.error);
                        }
                    }
                    catch (err)
                    {
                        alert(err);
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown)
                {
                    var error_message = wpvivid_output_ajaxerror('achieving themes', textStatus, errorThrown);
                    alert(error_message);
                });
            }
        </script>
        <?php
    }

    public function output_core()
    {
        $updates = get_core_updates();
        $current_version=get_bloginfo( 'version' );
        if ( isset( $updates[0]->version ) && version_compare( $updates[0]->version, $current_version, '>' ) )
        {
            $new_version=$updates[0]->version;
        }
        else
        {
            $new_version=$current_version;
        }

        $enable_core_auto_backup=get_option('wpvivid_plugins_auto_backup_core',false);

        if($enable_core_auto_backup)
        {
            $enable_check="checked";
        }
        else
        {
            $enable_check="";
        }
        ?>
        <div id="wpvivid_backup_core_progress" class="wpvivid-one-coloum wpvivid-workflow wpvivid-clear-float" style="display: none">
            <p>
                <span class="wpvivid-span-progress">
                    <span class="wpvivid-span-processed-progress wpvivid-span-processed-percent-progress" style="background:#007cba;width: 0%" >0% completed</span>
                </span>
            </p>
            <p>
                <span class="dashicons dashicons-backup wpvivid-dashicons-blue"></span>
                <span>
                    <span id="wpvivid_rollback_core_progress_text">Rolling back WordPress core...</span>
                </span>
            </p>
        </div>
        <table class="wp-list-table widefat plugins" style="margin-bottom:0.5rem;">
            <tbody id="the-list" data-wp-lists="list:plugin">
            <tr class="active">
                <th style="width:2rem;">
                    <span class="dashicons dashicons-wordpress-alt wpvivid-dashicons-large wpvivid-dashicons-blue"></span>
                </th>
                <td class="column-description desc">
                    <div class="eum-plugins-name-actions"><h4 class="eum-plugins-name" style="margin:0;">Wordpress Core</h4></div>
                    <div class="active second plugin-version-author-uri">
                        <div><span>Current Version </span><strong><span style="color: orange;"><?php echo $current_version;?></span></strong> | <span>Latest Version </span><strong><span style="color: green;"><?php echo $new_version;?></span></strong> </div>
                    </div>
                    <div>
                        <label class="wpvivid-switch" title="Enable/Disable the job">
                            <input id="wpvivid_enable_core_auto_backup" type="checkbox" <?php echo $enable_check;?> >
                            <span class="wpvivid-slider wpvivid-round"></span>
                        </label>
                        <span>Enable "Backup the <strong>Wordpress Core</strong> before update".</span>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>

        <div id="wpvivid_core_backup_list">
            <?php
            $core_list=$this->get_core_data();
            $table=new WPvivid_Core_List();
            $table->set_list($core_list);
            $table->prepare_items();
            $table->display();
            ?>
        </div>
        <a id="wpvivid_a_core_link" style="display: none;"></a>


        <script>
            jQuery('#wpvivid_enable_core_auto_backup').click(function()
            {
                if(jQuery(this).prop('checked'))
                {
                    var enable=true;
                }
                else
                {
                    var enable=false;
                }

                wpvivid_enable_core_auto_backup(enable);
            });

            function wpvivid_enable_core_auto_backup(enable)
            {
                var ajax_data = {
                    'action': 'wpvivid_enable_core_auto_backup',
                    'enable': enable,
                };

                wpvivid_post_request_addon(ajax_data, function (data)
                {
                    var jsonarray = jQuery.parseJSON(data);
                    if (jsonarray.result === 'success')
                    {
                    }
                    else
                    {
                        alert(jsonarray.error);
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown)
                {
                    var error_message = wpvivid_output_ajaxerror('enable', textStatus, errorThrown);
                    alert(error_message);
                });
            }

            jQuery('#wpvivid_core_backup_list').on("click",'.wpvivid-core-download',function()
            {
                var Obj=jQuery(this);
                var version=Obj.closest('tr').data('version');

                wpvivid_download_core_rollback(version);
            });

            function wpvivid_download_core_rollback(version)
            {
                var a = document.getElementById('wpvivid_a_core_link');
                var url=ajaxurl+'?_wpnonce='+wpvivid_ajax_object_addon.ajax_nonce+'&action=wpvivid_download_core_rollback&version='+version;
                a.href = url;
                a.download = 'wordpress'+version+'.zip';
                a.click();
            }

            jQuery('#wpvivid_core_backup_list').on("click",'.wpvivid-rollback-core-version',function()
            {
                var Obj=jQuery(this);
                var version=Obj.closest('tr').data('version');
                var descript = '<?php _e('Are you sure to rollback wordpress core?', 'wpvivid'); ?>';
                var ret = confirm(descript);
                if (ret === true)
                {
                    wpvivid_rollback_core(version);
                }
            });

            function wpvivid_simulate_core_rollback_progress()
            {
                var MaxProgess = 95,
                    currentProgess = 0,
                    steps = 1,
                    time_steps=500;

                var timer = setInterval(function ()
                {
                    if(wpvivid_b_rollback_finished)
                    {
                        currentProgess=100;
                        var progress_html='<p><span class="wpvivid-span-progress">' +
                            '<span class="wpvivid-span-processed-progress wpvivid-span-processed-percent-progress" style="background:#007cba;width: '+currentProgess+'%">' +
                            currentProgess+'% completed</span></span></p><p>' +
                            '<span><span id="wpvivid_rollback_progress_text">Rolling back WordPress core completed successfully.</span></span></p>';
                    }
                    else
                    {
                        currentProgess += steps;
                        var progress_html='<p><span class="wpvivid-span-progress">' +
                            '<span class="wpvivid-span-processed-progress wpvivid-span-processed-percent-progress" style="background:#007cba;width: '+currentProgess+'%">' +
                            currentProgess+'% completed</span></span></p><p>' +
                            '<span><span id="wpvivid_rollback_progress_text">Rolling back WordPress core...</span></span></p>';
                    }

                    jQuery("#wpvivid_backup_core_progress").html(progress_html);
                    if (currentProgess >= MaxProgess)
                    {
                        clearInterval(timer);
                    }
                }, time_steps);
            }

            function wpvivid_rollback_core(version)
            {
                var ajax_data = {
                    'action':'wpvivid_rollback_core',
                    'version': version
                };

                wpvivid_b_rollback_finished=false;
                jQuery('#wpvivid_backup_core_progress').show();
                jQuery('.wpvivid-span-processed-progress').html("0% completed");
                jQuery(".wpvivid-span-processed-progress").width( "0%" );
                jQuery('#wpvivid_rollback_core_progress_text').html("Rolling back WordPress core...");

                wpvivid_simulate_core_rollback_progress();

                wpvivid_post_request_addon(ajax_data, function(data)
                {
                    try
                    {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success')
                        {
                            wpvivid_do_rollback_core();
                        }
                        else
                        {
                            jQuery('#wpvivid_backup_core_progress').hide();
                            alert(jsonarray.error);
                        }
                    }
                    catch(err)
                    {
                        jQuery('#wpvivid_backup_core_progress').hide();
                        alert(err);
                    }
                },function(XMLHttpRequest, textStatus, errorThrown)
                {
                    jQuery('#wpvivid_backup_core_progress').hide();
                    var error_message = wpvivid_output_ajaxerror('rollback', textStatus, errorThrown);
                    alert(error_message);
                });
            }

            function wpvivid_do_rollback_core()
            {
                var ajax_data = {
                    'action':'wpvivid_do_rollback_core'
                };
                wpvivid_post_request_addon(ajax_data, function(data)
                {
                    setTimeout(function(){
                        wpvivid_get_rollback_core_progress();
                    }, 1000);
                }, function(XMLHttpRequest, textStatus, errorThrown)
                {
                    wpvivid_get_rollback_core_progress();
                });
            }

            function wpvivid_get_rollback_core_progress()
            {
                var ajax_data = {
                    'action':'wpvivid_get_rollback_core_progress'
                };
                wpvivid_post_request_addon(ajax_data, function(data)
                {
                    try
                    {
                        var jsonarray = jQuery.parseJSON(data);

                        if (jsonarray.result === 'success')
                        {
                            if(jsonarray.status=='running')
                            {
                                setTimeout(function(){
                                    wpvivid_get_rollback_core_progress();
                                }, 2000);
                            }
                            else if(jsonarray.status=='completed')
                            {
                                wpvivid_b_rollback_finished=true;
                                jQuery('.wpvivid-span-processed-progress').html("100% completed");
                                jQuery(".wpvivid-span-processed-progress").width( "100%" );
                                jQuery('#wpvivid_rollback_progress_text').html("Rolling back WordPress core completed successfully.");
                                alert("Rollback has completed successfully.");
                                location.reload();
                            }
                        }
                        else {
                            jQuery('#wpvivid_backup_core_progress').hide();
                            alert(jsonarray.error);
                        }
                    }
                    catch (err)
                    {
                        setTimeout(function(){
                            wpvivid_get_rollback_core_progress();
                        }, 2000);
                    }
                }, function(XMLHttpRequest, textStatus, errorThrown)
                {
                    setTimeout(function(){
                        wpvivid_get_rollback_core_progress();
                    }, 2000);
                });
            }

            jQuery('#wpvivid_core_backup_list').on("click",'#wpvivid_rollback_core_bulk_action',function()
            {
                var action=jQuery('#wpvivid_rollback_core_bulk_action_select').val();
                if(action=='-1')
                {
                    return;
                }
                else
                {
                    wpvivid_delete_core_rollback();
                }
            });

            function wpvivid_delete_core_rollback()
            {
                var versions= new Array();
                var count = 0;

                jQuery('#wpvivid_core_backup_list th input').each(function (i)
                {
                    if(jQuery(this).prop('checked'))
                    {
                        versions[count]=jQuery(this).closest('tr').data('version');
                        count++;
                    }
                });

                if( count === 0 )
                {
                    alert('<?php _e('Please select at least one item.','wpvivid'); ?>');
                    return;
                }

                var ajax_data = {
                    'action':'wpvivid_delete_core_rollback',
                    'versions':versions,
                };

                wpvivid_post_request_addon(ajax_data, function(data)
                {
                    try
                    {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success')
                        {
                            jQuery('#wpvivid_core_backup_list').html(jsonarray.html);
                        }
                        else
                        {
                            alert(jsonarray.error);
                        }
                    }
                    catch(err)
                    {
                        alert(err);
                    }
                },function(XMLHttpRequest, textStatus, errorThrown)
                {
                    var error_message = wpvivid_output_ajaxerror('rollback', textStatus, errorThrown);
                    alert(error_message);
                });
            }

            jQuery('#wpvivid_core_backup_list').on("click",'.first-page',function()
            {
                wpvivid_get_core_list('first');
            });

            jQuery('#wpvivid_core_backup_list').on("click",'.prev-page',function()
            {
                var page=parseInt(jQuery(this).attr('value'));
                wpvivid_get_core_list(page-1);
            });

            jQuery('#wpvivid_core_backup_list').on("click",'.next-page',function()
            {
                var page=parseInt(jQuery(this).attr('value'));
                wpvivid_get_core_list(page+1);
            });

            jQuery('#wpvivid_core_backup_list').on("click",'.last-page',function()
            {
                wpvivid_get_core_list('last');
            });

            jQuery('#wpvivid_core_backup_list').on("keypress", '.current-page', function()
            {
                if(event.keyCode === 13)
                {
                    var page = jQuery(this).val();
                    wpvivid_get_core_list(page);
                }
            });

            function wpvivid_get_core_list(page=0)
            {
                if(page==0)
                {
                    page =jQuery('#wpvivid_core_backup_list').find('.current-page').val();
                }

                var ajax_data = {
                    'action': 'wpvivid_get_core_list',
                    'page':page
                };
                wpvivid_post_request_addon(ajax_data, function (data)
                {
                    jQuery('#wpvivid_core_backup_list').html('');
                    try
                    {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success')
                        {
                            jQuery('#wpvivid_core_backup_list').html(jsonarray.html);
                        }
                        else
                        {
                            alert(jsonarray.error);
                        }
                    }
                    catch (err)
                    {
                        alert(err);
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown)
                {
                    var error_message = wpvivid_output_ajaxerror('achieving core backups', textStatus, errorThrown);
                    alert(error_message);
                });
            }

            jQuery('#wpvivid_core_backup_list').on('click', 'thead tr td input', function()
            {
                wpvivid_control_core_select(jQuery(this));
            });

            jQuery('#wpvivid_core_backup_list').on('click', 'tfoot tr td input', function()
            {
                wpvivid_control_core_select(jQuery(this));
            });

            function wpvivid_control_core_select(obj)
            {
                if(jQuery(obj).prop('checked'))
                {
                    jQuery('#wpvivid_core_backup_list thead tr td input').each(function()
                    {
                        jQuery(this).prop('checked', true);
                    });

                    jQuery('#wpvivid_core_backup_list tfoot tr td input').each(function()
                    {
                        jQuery(this).prop('checked', true);
                    });

                    jQuery('#wpvivid_core_backup_list tbody tr').each(function()
                    {
                        jQuery(this).find('th input').prop('checked', true);
                    });
                }
                else
                {
                    jQuery('#wpvivid_core_backup_list thead tr td input').each(function()
                    {
                        jQuery(this).prop('checked', false);
                    });

                    jQuery('#wpvivid_core_backup_list tfoot tr td input').each(function()
                    {
                        jQuery(this).prop('checked', false);
                    });

                    jQuery('#wpvivid_core_backup_list tbody tr').each(function ()
                    {
                        jQuery(this).find('th input').prop('checked', false);
                    });
                }
            }
        </script>
        <?php
    }

    public function output_settings()
    {
        $counts=get_option('wpvivid_max_rollback_count',array());

        $max_plugins_count=isset($counts['max_plugins_count'])?$counts['max_plugins_count']:5;
        $max_themes_count=isset($counts['max_themes_count'])?$counts['max_themes_count']:5;
        $max_core_count=isset($counts['max_core_count'])?$counts['max_core_count']:5;

        ?>
        <div>
            <p>Note: Once the retention is set up and reached, the oldest versioning backup will be deleted accordingly through a daily cron.</p>
            <p>
                <input type="text" class="wpvivid-rollback-count-retention" id="wpvivid_max_plugins_count" value="<?php echo $max_plugins_count;?>"> versioning backups retained for plugins.
            </p>
            <p>
                <input type="text" class="wpvivid-rollback-count-retention" id="wpvivid_max_themes_count" value="<?php echo $max_themes_count;?>"> versioning backups retained for themes.
            </p>
            <p>
                <input type="text" class="wpvivid-rollback-count-retention"  id="wpvivid_max_core_count" value="<?php echo $max_core_count;?>"> versioning backups retained for Wordpress core.
            </p>
        </div>
        <div style="margin-top:1rem;">
            <input class="button-primary" id="wpvivid_submit_rollback_setting" type="submit" value="Save Changes">
        </div>
        <div style="clear: both;"></div>
        <script>
            jQuery('.wpvivid-rollback-count-retention').on("keyup", function(){
                var regExp = /^[1-9][0-9]{0,2}$/g;
                var input_value = jQuery(this).val();
                if(!regExp.test(input_value)){
                    alert('Only enter numbers from 1-999');
                    jQuery(this).val('');
                }
            });

            jQuery('#wpvivid_submit_rollback_setting').click(function(){
                wpvivid_submit_rollback_setting();
            });

            function wpvivid_submit_rollback_setting()
            {
                var max_plugins_count = jQuery('#wpvivid_max_plugins_count').val();
                var max_themes_count = jQuery('#wpvivid_max_themes_count').val();
                var max_core_count = jQuery('#wpvivid_max_core_count').val();

                var ajax_data = {
                    'action': 'wpvivid_set_rollback_setting',
                    'max_plugins_count': max_plugins_count,
                    'max_themes_count': max_themes_count,
                    'max_core_count' : max_core_count
                };
                jQuery('.wpvivid_submit_rollback_setting').css({'pointer-events': 'none', 'opacity': '0.4'});

                wpvivid_post_request_addon(ajax_data, function (data)
                {
                    try
                    {
                        var jsonarray = jQuery.parseJSON(data);

                        jQuery('.wpvivid_submit_rollback_setting').css({'pointer-events': 'auto', 'opacity': '1'});
                        if (jsonarray.result === 'success')
                        {
                        }
                        else
                        {
                            alert(jsonarray.error);
                        }
                    }
                    catch (err) {
                        alert(err);
                        jQuery('.wpvivid_setting_general_save').css({'pointer-events': 'auto', 'opacity': '1'});
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown) {
                    jQuery('.wpvivid_setting_general_save').css({'pointer-events': 'auto', 'opacity': '1'});
                    var error_message = wpvivid_output_ajaxerror('changing settings', textStatus, errorThrown);
                    alert(error_message);
                });
            }
        </script>
        <?php
    }

    public function set_rollback_setting()
    {
        global $wpvivid_backup_pro;
        $wpvivid_backup_pro->ajax_check_security();

        if (isset($_POST['max_plugins_count']))
        {
            $max_plugins_count = sanitize_key($_POST['max_plugins_count']);
        }
        else
        {
            die();
        }

        if (isset($_POST['max_themes_count']))
        {
            $max_themes_count = sanitize_key($_POST['max_themes_count']);
        }
        else
        {
            die();
        }

        if (isset($_POST['max_core_count']))
        {
            $max_core_count = sanitize_key($_POST['max_core_count']);
        }
        else
        {
            die();
        }

        $counts=get_option('wpvivid_max_rollback_count',array());

        $counts['max_plugins_count']=$max_plugins_count;
        $counts['max_themes_count']=$max_themes_count;
        $counts['max_core_count']=$max_core_count;

        update_option('wpvivid_max_rollback_count',$counts);

        $ret['result']='success';
        echo json_encode($ret);
        die();
    }

    public function output_version_backup()
    {
        ?>

        <div id="wpvivid_rollback_detail">
        </div>

        <div id="wpvivid_rollback_progress" class="wpvivid-one-coloum wpvivid-workflow wpvivid-clear-float" style="display: none">
            <p>
                <span class="wpvivid-span-progress">
                    <span class="wpvivid-span-processed-progress wpvivid-span-processed-percent-progress" style="background:#007cba;width: 0%" >0% completed</span>
                </span>
            </p>
            <p>
                <span class="dashicons dashicons-backup wpvivid-dashicons-blue"></span>
                <span>
                    <span id="wpvivid_rollback_progress_text">Rollback ing...</span>
                </span>
            </p>
        </div>

        <div id="wpvivid_rollback_backup_list">
        </div>
        <a id="wpvivid_a_link" style="display: none;"></a>
        <script>
            //
            jQuery('#wpvivid_rollback_backup_list').on("click",'.wpvivid-rollback-download',function()
            {
                var Obj=jQuery(this);
                var slug=Obj.closest('tr').data('slug');
                var version=Obj.closest('tr').data('version');

                wpvivid_download_rollback(slug,version,wpvivid_current_type);
            });

            jQuery('#wpvivid_rollback_backup_list').on("click",'.wpvivid-rollback-version',function()
            {
                var Obj=jQuery(this);
                var slug=Obj.closest('tr').data('slug');
                var version=Obj.closest('tr').data('version');

                wpvivid_rollback_version(slug,version,wpvivid_current_type);
            });

            function wpvivid_download_rollback(slug,version,type)
            {
                var a = document.getElementById('wpvivid_a_link');
                var url=ajaxurl+'?_wpnonce='+wpvivid_ajax_object_addon.ajax_nonce+'&action=wpvivid_download_rollback&slug='+slug+'&version='+version+'&type='+type;
                a.href = url;
                a.download = slug+'.zip';
                a.click();
            }

            function wpvivid_simulate_rollback_progress_ex()
            {
                var MaxProgess = 95,
                    currentProgess = 0,
                    steps = 1,
                    time_steps=500;

                var timer = setInterval(function ()
                {
                    if(wpvivid_b_rollback_finished)
                    {
                        currentProgess=100;
                        var progress_html='<p><span class="wpvivid-span-progress">' +
                            '<span class="wpvivid-span-processed-progress wpvivid-span-processed-percent-progress" style="background:#007cba;width: '+currentProgess+'%">' +
                            currentProgess+'% completed</span></span></p><p>' +
                            '<span><span id="wpvivid_rollback_progress_text">Rollback has completed successfully.</span></span></p>';
                    }
                    else
                    {
                        currentProgess += steps;
                        var progress_html='<p><span class="wpvivid-span-progress">' +
                            '<span class="wpvivid-span-processed-progress wpvivid-span-processed-percent-progress" style="background:#007cba;width: '+currentProgess+'%">' +
                            currentProgess+'% completed</span></span></p><p>' +
                            '<span><span id="wpvivid_rollback_progress_text">Rollback ing...</span></span></p>';
                    }

                    jQuery("#wpvivid_rollback_progress").html(progress_html);
                    if (currentProgess >= MaxProgess)
                    {
                        clearInterval(timer);
                    }
                }, time_steps);
            }

            function wpvivid_rollback_version(slug,version,type)
            {
                if(type=='plugins')
                {
                    var action='wpvivid_rollback_plugin';
                }
                else
                {
                    var action='wpvivid_rollback_theme';
                }
                var ajax_data = {
                    'action':action,
                    'slug':slug,
                    'version':version
                };

                wpvivid_b_rollback_finished=false;
                jQuery('#wpvivid_rollback_progress').show();
                jQuery('.wpvivid-span-processed-progress').html("0% completed");
                jQuery(".wpvivid-span-processed-progress").width( "0%" );
                jQuery('#wpvivid_rollback_progress_text').html("Rollback ing...");

                wpvivid_simulate_rollback_progress_ex();

                wpvivid_post_request_addon(ajax_data, function(data)
                {
                    try
                    {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success')
                        {
                            wpvivid_b_rollback_finished=true;
                            jQuery('.wpvivid-span-processed-progress').html("100% completed");
                            jQuery(".wpvivid-span-processed-progress").width( "100%" );
                            jQuery('#wpvivid_rollback_progress_text').html("Rollback has completed successfully.");

                            setTimeout(function()
                            {
                                jQuery('#wpvivid_rollback_detail').find('.wpvivid-rollback-current-version').html(version);
                                jQuery('#wpvivid_rollback_progress').hide();
                                alert("Rollback has completed successfully.");
                            }, 1200);

                            //wpvivid_get_plugins_list();
                            //wpvivid_get_themes_list();
                        }
                        else
                        {
                            jQuery('#wpvivid_rollback_progress').hide();
                            alert(jsonarray.error);
                        }
                    }
                    catch(err)
                    {
                        jQuery('#wpvivid_rollback_progress').hide();
                        alert(err);
                    }
                },function(XMLHttpRequest, textStatus, errorThrown)
                {
                    jQuery('#wpvivid_rollback_progress').hide();
                    var error_message = wpvivid_output_ajaxerror('rollback', textStatus, errorThrown);
                    alert(error_message);
                });
            }

            //
            jQuery('#wpvivid_rollback_backup_list').on("click",'#wpvivid_rollback_bulk_action',function()
            {
                var action=jQuery('#wpvivid_rollback_bulk_action_select').val();
                if(action=='-1')
                {
                    return;
                }
                else
                {
                    wpvivid_delete_rollback();
                }
            });

            function wpvivid_delete_rollback()
            {
                var versions= new Array();
                var count = 0;

                jQuery('#wpvivid_rollback_backup_list th input').each(function (i)
                {
                    if(jQuery(this).prop('checked'))
                    {
                        versions[count]=jQuery(this).closest('tr').data('version');
                        count++;
                    }
                });

                if( count === 0 )
                {
                    alert('<?php _e('Please select at least one item.','wpvivid'); ?>');
                    return;
                }

                var ajax_data = {
                    'action':'wpvivid_delete_rollback',
                    'slug':wpvivid_current_rollback_slug,
                    'versions':versions,
                    'type':wpvivid_current_type
                };

                wpvivid_post_request_addon(ajax_data, function(data)
                {
                    try
                    {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success')
                        {
                            wpvivid_get_rollback_list();
                            wpvivid_get_plugins_list();
                        }
                        else
                        {
                            alert(jsonarray.error);
                        }
                    }
                    catch(err)
                    {
                        alert(err);
                    }
                },function(XMLHttpRequest, textStatus, errorThrown)
                {
                    var error_message = wpvivid_output_ajaxerror('rollback', textStatus, errorThrown);
                    alert(error_message);
                });
            }

            jQuery('#wpvivid_rollback_backup_list').on('click', 'thead tr td input', function()
            {
                wpvivid_control_rollback_select(jQuery(this));
            });

            jQuery('#wpvivid_rollback_backup_list').on('click', 'tfoot tr td input', function()
            {
                wpvivid_control_rollback_select(jQuery(this));
            });

            function wpvivid_control_rollback_select(obj)
            {
                if(jQuery(obj).prop('checked'))
                {
                    jQuery('#wpvivid_rollback_backup_list thead tr td input').each(function()
                    {
                        jQuery(this).prop('checked', true);
                    });

                    jQuery('#wpvivid_rollback_backup_list tfoot tr td input').each(function()
                    {
                        jQuery(this).prop('checked', true);
                    });

                    jQuery('#wpvivid_rollback_backup_list tbody tr').each(function()
                    {
                        jQuery(this).find('th input').prop('checked', true);
                    });
                }
                else
                {
                    jQuery('#wpvivid_rollback_backup_list thead tr td input').each(function()
                    {
                        jQuery(this).prop('checked', false);
                    });

                    jQuery('#wpvivid_rollback_backup_list tfoot tr td input').each(function()
                    {
                        jQuery(this).prop('checked', false);
                    });

                    jQuery('#wpvivid_rollback_backup_list tbody tr').each(function ()
                    {
                        jQuery(this).find('th input').prop('checked', false);
                    });
                }
            }

            jQuery('#wpvivid_rollback_backup_list').on("click",'.first-page',function()
            {
                wpvivid_get_rollback_list('first');
            });

            jQuery('#wpvivid_rollback_backup_list').on("click",'.prev-page',function()
            {
                var page=parseInt(jQuery(this).attr('value'));
                wpvivid_get_rollback_list(page-1);
            });

            jQuery('#wpvivid_rollback_backup_list').on("click",'.next-page',function()
            {
                var page=parseInt(jQuery(this).attr('value'));
                wpvivid_get_rollback_list(page+1);
            });

            jQuery('#wpvivid_rollback_backup_list').on("click",'.last-page',function()
            {
                wpvivid_get_rollback_list('last');
            });

            jQuery('#wpvivid_rollback_backup_list').on("keypress", '.current-page', function()
            {
                if(event.keyCode === 13)
                {
                    var page = jQuery(this).val();
                    wpvivid_get_rollback_list(page);
                }
            });

            function wpvivid_get_rollback_list(page=0)
            {
                if(page==0)
                {
                    page =jQuery('#wpvivid_rollback_backup_list').find('.current-page').val();
                }

                var ajax_data = {
                    'action': 'wpvivid_get_rollback_list',
                    'slug':wpvivid_current_rollback_slug,
                    'type':wpvivid_current_type,
                    'page':page
                };
                wpvivid_post_request_addon(ajax_data, function (data)
                {
                    jQuery('#wpvivid_rollback_backup_list').html('');
                    try
                    {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success')
                        {
                            jQuery('#wpvivid_rollback_backup_list').html(jsonarray.html);
                        }
                        else
                        {
                            alert(jsonarray.error);
                        }
                    }
                    catch (err)
                    {
                        alert(err);
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown)
                {
                    var error_message = wpvivid_output_ajaxerror('achieving plugins', textStatus, errorThrown);
                    alert(error_message);
                });
            }
        </script>
        <?php
    }

    public function get_plugin_slug($file)
    {
        $plugin=dirname($file);
        if($plugin=='.')
        {
            $slug = pathinfo($file, PATHINFO_FILENAME);
        }
        else
        {
            $slug=$plugin;
        }

        return $slug;
    }

    public function get_rollback_data($slug,$type='plugins')
    {
        $plugin=dirname($slug);
        if($plugin=='.')
        {
            $plugin = pathinfo($slug, PATHINFO_FILENAME);
        }

        $path=WP_CONTENT_DIR.'/'.WPvivid_Setting::get_backupdir().'/'.'rollback/'.$type.'/'.$plugin;
        if(file_exists($path))
        {
            $rollback=array();
            $plugin_dir  = @opendir( $path );

            while ( ( $file = readdir( $plugin_dir ) ) !== false )
            {
                if ( '.' === substr( $file, 0, 1 ) )
                {
                    continue;
                }

                if ( is_dir( $path . '/' . $file ) )
                {
                    if(file_exists($path . '/' . $file.'/'.$plugin.'.zip'))
                    {
                        $rollback[$file]=$plugin.'.zip';
                    }
                }
            }

            closedir( $plugin_dir );
            return $rollback;
        }
        else
        {
            return array();
        }
    }

    public function get_core_data()
    {
        $path=WP_CONTENT_DIR.'/'.WPvivid_Setting::get_backupdir().'/'.'rollback/core';
        $core_list=array();

        if(file_exists($path))
        {
            $core_dir  = @opendir( $path );

            while ( ( $file = readdir( $core_dir ) ) !== false )
            {
                if ( '.' === substr( $file, 0, 1 ) )
                {
                    continue;
                }

                if ( is_dir( $path . '/' . $file ) )
                {
                    if(file_exists($path . '/' . $file.'/wordpress.zip'))
                    {
                        $file_name=$path . '/' . $file.'/wordpress.zip';
                        $info['id']=$file;
                        $info['version']=$file;
                        $info['date']=date('M d Y h:i A', filemtime($file_name));
                        $info['size']=size_format(filesize($file_name),2);
                        $core_list[$file]=$info;
                    }
                }
            }

            closedir( $core_dir );
        }

        return $core_list;
    }

    public function get_rollback_file_info($slug,$version,$file,$type='plugins')
    {
        $path=WP_CONTENT_DIR.'/'.WPvivid_Setting::get_backupdir().'/'.'rollback/'.$type.'/'.$slug.'/'.$version.'/'.$file;
        if(file_exists($path))
        {
            $info['date']=date('M d Y h:i A', filemtime($path));
            $info['size']=size_format(filesize($path),2);
            return $info;
        }
        else
        {
            $info['date']='';
            $info['size']='';
            return $info;
        }
    }

    public function get_enable_auto_backup_status($slug)
    {
        $plugins_auto_backup_status=get_option('wpvivid_plugins_auto_backup_status',array());
        if(isset($plugins_auto_backup_status[$slug]))
        {
            return $plugins_auto_backup_status[$slug];
        }
        else
        {
            return false;
        }
    }

    public function get_theme_enable_auto_backup_status($slug)
    {
        $themes_auto_backup_status=get_option('wpvivid_themes_auto_backup_status',array());
        if(isset($themes_auto_backup_status[$slug]))
        {
            return $themes_auto_backup_status[$slug];
        }
        else
        {
            return false;
        }
    }

    public function rollback_plugin()
    {
        global $wpvivid_backup_pro;
        $wpvivid_backup_pro->ajax_check_security();

        if (isset($_POST['slug']) && !empty($_POST['slug']) && is_string($_POST['slug']))
        {
            $slug = sanitize_key($_POST['slug']);
        }
        else
        {
            die();
        }

        if (isset($_POST['version']) && !empty($_POST['version']) && is_string($_POST['version']))
        {
            $version = sanitize_text_field($_POST['version']);
        }
        else
        {
            die();
        }

        $package=WP_CONTENT_DIR.'/'.WPvivid_Setting::get_backupdir().'/'.'rollback/plugins/'.$slug.'/' . $version.'/'.$slug.'.zip';
        if(!file_exists($package))
        {
            $ret['result']='failed';
            $ret['error']="Could not find the backup. Please check whether the backup exists.";
            echo json_encode($ret);
            die();
        }

        if( ! function_exists('plugins_api') )
        {
            require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
        }

        if(!class_exists('WP_Upgrader'))
            require_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );

        //require_once( ABSPATH . 'wp-admin/includes/class-plugin-installer-skin.php' );
        if(!class_exists('Plugin_Upgrader'))
            require_once( ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php' );


        $skin     = new WP_Ajax_Upgrader_Skin();
        $upgrader = new Plugin_Upgrader( $skin );
        $ret['test']=$package;


        $remote_destination = WP_PLUGIN_DIR .'/'.$slug.'/';

        if(file_exists($remote_destination))
        {
            WP_Filesystem();
            $upgrader->clear_destination($remote_destination);
        }

        $return=$upgrader->install($package);

        if($return)
        {
            $ret['result']= 'success';
        }
        else
        {
            $ret['result'] = 'failed';
            if(is_wp_error( $return ))
            {
                $ret['error'] =$return->get_error_message() ;
            }
            else
            {
                $ret['error']='Installing the plugin failed. '.$return;
            }
        }

        echo json_encode($ret);
        die();
    }

    public function rollback_theme()
    {
        global $wpvivid_backup_pro;
        $wpvivid_backup_pro->ajax_check_security();

        if (isset($_POST['slug']) && !empty($_POST['slug']) && is_string($_POST['slug']))
        {
            $slug = sanitize_key($_POST['slug']);
        }
        else
        {
            die();
        }

        if (isset($_POST['version']) && !empty($_POST['version']) && is_string($_POST['version']))
        {
            $version = sanitize_text_field($_POST['version']);
        }
        else
        {
            die();
        }

        $package=WP_CONTENT_DIR.'/'.WPvivid_Setting::get_backupdir().'/'.'rollback/themes/'.$slug.'/' . $version.'/'.$slug.'.zip';
        if(!file_exists($package))
        {
            $ret['result']='failed';
            $ret['error']="Could not find the backup. Please check whether the backup exists.";
            echo json_encode($ret);
            die();
        }

        if( ! function_exists('plugins_api') )
        {
            require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
        }

        if(!class_exists('WP_Upgrader'))
            require_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );


        $skin     = new WP_Ajax_Upgrader_Skin();
        $upgrader = new Theme_Upgrader( $skin );
        $ret['test']=$package;


        $remote_destination = get_theme_root() .'/'.$slug.'/';

        if(file_exists($remote_destination))
        {
            WP_Filesystem();
            $upgrader->clear_destination($remote_destination);
        }

        $return=$upgrader->install($package);

        if($return)
        {
            $ret['result']= 'success';
        }
        else
        {
            $ret['result'] = 'failed';
            if(is_wp_error( $return ))
            {
                $ret['error'] =$return->get_error_message() ;
            }
            else
            {
                $ret['error']='Installing the theme failed. '.$return;
            }
        }

        echo json_encode($ret);
        die();
    }

    public function enable_auto_backup()
    {
        global $wpvivid_backup_pro;
        $wpvivid_backup_pro->ajax_check_security();

        if (isset($_POST['slug']) && !empty($_POST['slug']) && is_string($_POST['slug']))
        {
            $slug = sanitize_key($_POST['slug']);
        }
        else
        {
            die();
        }

        if (isset($_POST['enable']))
        {
            $enable=$_POST['enable'];
        }
        else
        {
            die();
        }

        if($enable=='true')
        {
            $enable=true;
        }
        else
        {
            $enable=false;
        }

        $plugins_auto_backup_status=get_option('wpvivid_plugins_auto_backup_status',array());
        $plugins_auto_backup_status[$slug]=$enable;
        update_option('wpvivid_plugins_auto_backup_status',$plugins_auto_backup_status);

        $ret['result']='success';
        echo json_encode($ret);
        die();
    }

    public function theme_enable_auto_backup()
    {
        global $wpvivid_backup_pro;
        $wpvivid_backup_pro->ajax_check_security();

        if (isset($_POST['slug']) && !empty($_POST['slug']) && is_string($_POST['slug']))
        {
            $slug = sanitize_key($_POST['slug']);
        }
        else
        {
            die();
        }

        if (isset($_POST['enable']))
        {
            $enable=$_POST['enable'];
        }
        else
        {
            die();
        }

        if($enable=='true')
        {
            $enable=true;
        }
        else
        {
            $enable=false;
        }

        $themes_auto_backup_status=get_option('wpvivid_themes_auto_backup_status',array());
        $themes_auto_backup_status[$slug]=$enable;
        update_option('wpvivid_themes_auto_backup_status',$themes_auto_backup_status);

        $ret['result']='success';
        echo json_encode($ret);
        die();
    }

    public function plugins_enable_auto_backup()
    {
        global $wpvivid_backup_pro;
        $wpvivid_backup_pro->ajax_check_security();

        if (isset($_POST['plugins']) && !empty($_POST['plugins']) && is_array($_POST['plugins']))
        {
            $plugins=$_POST['plugins'];

            if (isset($_POST['enable']))
            {
                $enable=$_POST['enable'];
            }
            else
            {
                die();
            }

            if($enable=='enable')
            {
                $enable=true;
            }
            else
            {
                $enable=false;
            }

            $plugins_auto_backup_status=get_option('wpvivid_plugins_auto_backup_status',array());

            foreach ($plugins as $slug)
            {
                $plugins_auto_backup_status[$slug]=$enable;
            }

            update_option('wpvivid_plugins_auto_backup_status',$plugins_auto_backup_status);

            $ret['result']='success';
            echo json_encode($ret);

            die();
        }
        else
        {
            die();
        }
    }

    public function themes_enable_auto_backup()
    {
        global $wpvivid_backup_pro;
        $wpvivid_backup_pro->ajax_check_security();

        if (isset($_POST['themes']) && !empty($_POST['themes']) && is_array($_POST['themes']))
        {
            $themes=$_POST['themes'];

            if (isset($_POST['enable']))
            {
                $enable=$_POST['enable'];
            }
            else
            {
                die();
            }

            if($enable=='enable')
            {
                $enable=true;
            }
            else
            {
                $enable=false;
            }

            $themes_auto_backup_status=get_option('wpvivid_themes_auto_backup_status',array());

            foreach ($themes as $slug)
            {
                $themes_auto_backup_status[$slug]=$enable;
            }

            update_option('wpvivid_themes_auto_backup_status',$themes_auto_backup_status);

            $ret['result']='success';
            echo json_encode($ret);

            die();
        }
        else
        {
            die();
        }
    }

    public function enable_core_auto_backup()
    {
        global $wpvivid_backup_pro;
        $wpvivid_backup_pro->ajax_check_security();

        if (isset($_POST['enable']))
        {
            $enable=$_POST['enable'];
        }
        else
        {
            die();
        }

        if($enable=='true')
        {
            $enable=true;
        }
        else
        {
            $enable=false;
        }

        update_option('wpvivid_plugins_auto_backup_core',$enable);

        $ret['result']='success';
        echo json_encode($ret);
        die();
    }

    public function view_plugin_versions()
    {
        global $wpvivid_backup_pro;
        $wpvivid_backup_pro->ajax_check_security();

        if (isset($_POST['slug']) && !empty($_POST['slug']) && is_string($_POST['slug']))
        {
            $slug = sanitize_key($_POST['slug']);
        }
        else
        {
            die();
        }

        require_once ABSPATH . 'wp-admin/includes/plugin.php';

        $all_plugins     = get_plugins();
        $current_plugin_file='';
        foreach ((array) $all_plugins as $plugin_file => $plugin_data)
        {
           if($this->get_plugin_slug($plugin_file)==$slug)
           {
               $current_plugin_file=$plugin_file;
               break;
           }
        }

        if(empty($current_plugin_file))
        {
            $ret['result']='failed';
            $ret['error']='plugin not found.';
            echo json_encode($ret);
            die();
        }

        $current = get_site_transient( 'update_plugins' );

        $plugin_data=get_plugin_data(WP_PLUGIN_DIR.'/'.$current_plugin_file);

        if ( isset( $current->response[ $current_plugin_file ] ) )
        {
            $plugin_data['response']= (array)$current->response[ $current_plugin_file ];
        }
        else if( isset( $current->no_update[ $current_plugin_file ] ) )
        {
            $plugin_data['response']= (array)$current->no_update[ $current_plugin_file ];
        }
        else
        {
            $plugin_data['response']['new_version']='-';
        }

        $plugin_data['rollback']=$this->get_rollback_data($current_plugin_file);

        $rollback_list=array();
        if(!empty($plugin_data['rollback']))
        {
            foreach ($plugin_data['rollback'] as $version=>$file)
            {
                $info=$this->get_rollback_file_info($slug,$version,$file);
                $rollback['version']=$version;
                $rollback['slug']=$slug;
                $rollback['date']=$info['date'];
                $rollback['size']=$info['size'];
                $rollback_list[$version]=$rollback;
            }
        }

        $ret['result']='success';
        $table=new WPvivid_Rollback_List();
        $table->set_list($rollback_list);
        $table->prepare_items();
        ob_start();
        $table->display();
        $ret['backup_list'] = ob_get_clean();
        $ret['detail']=$this->get_plugin_detail($plugin_data,$current_plugin_file);
        $ret['test']=$rollback_list;

        echo json_encode($ret);

        die();
    }

    public function view_theme_versions()
    {
        global $wpvivid_backup_pro;
        $wpvivid_backup_pro->ajax_check_security();

        if (isset($_POST['slug']) && !empty($_POST['slug']) && is_string($_POST['slug']))
        {
            $slug = sanitize_key($_POST['slug']);
        }
        else
        {
            die();
        }

        $theme=wp_get_theme($slug);

        $them_data["Name"]=$theme->display( 'Name' );
        $them_data["Version"]=$theme->display( 'Version' );
        $them_data["Description"]=$theme->display( 'Description' );
        $them_data["icon"]=$theme->get_screenshot() . '?ver=' . $theme->get( 'Version' );
        $current = get_site_transient( 'update_themes' );

        if ( isset( $current->response[ $slug ] ) )
        {
            $update=(array)$current->response[ $slug ];
            $them_data["new_version"]=$update['new_version'];
            $them_data['response']= $update;
        }
        else if( isset( $current->no_update[ $slug ] ) )
        {
            $update=(array)$current->no_update[ $slug ];
            $them_data["new_version"]=$update['new_version'];
            $them_data['response']= $update;
        }
        else
        {
            $them_data['new_version']='-';
            $them_data['response']= array();
        }


        $them_data['rollback']=$this->get_rollback_data($slug,'themes');

        $rollback_list=array();
        if(!empty($them_data['rollback']))
        {
            foreach ($them_data['rollback'] as $version=>$file)
            {
                $info=$this->get_rollback_file_info($slug,$version,$file,'themes');
                $rollback['version']=$version;
                $rollback['slug']=$slug;
                $rollback['date']=$info['date'];
                $rollback['size']=$info['size'];
                $rollback_list[$version]=$rollback;
            }
        }

        $ret['result']='success';
        $table=new WPvivid_Rollback_List();
        $table->set_list($rollback_list);
        $table->prepare_items();
        ob_start();
        $table->display();
        $ret['backup_list'] = ob_get_clean();
        $ret['detail']=$this->get_theme_detail($them_data,$slug);
        $ret['test']=$them_data;

        echo json_encode($ret);

        die();
    }

    public function get_plugin_detail($plugin_data,$plugin_file)
    {
        $icon= '<span class="dashicons dashicons-admin-plugins"></span>';
        if(isset($plugin_data['response']['icons']))
        {
            $preferred_icons = array( 'svg', '2x', '1x', 'default' );
            foreach ( $preferred_icons as $preferred_icon )
            {
                if ( ! empty( $plugin_data['response']['icons'][ $preferred_icon ] ) )
                {
                    $icon = '<img src="' . esc_url(  $plugin_data['response']['icons'][ $preferred_icon ] ) . '" alt="" />';
                    break;
                }
            }
        }

        $name=$plugin_data["Name"];
        $description=$plugin_data["Description"];
        $current_version=$plugin_data['Version'];
        $new_version= $plugin_data['response']['new_version'];

        if(is_plugin_active($plugin_file))
        {
            $plugin_active="This plugin is active for your site.";
        }
        else
        {
            $plugin_active="";
        }

        $html='<table class="wp-list-table widefat plugins" style="margin-bottom:0.5rem;">
                <tbody id="the-list" data-wp-lists="list:plugin">
                <tr class="active">
                    <th class="plugin-title">
                        '.$icon.'
                    </th>
                    <td class="column-description desc">
                        <div class="eum-plugins-name-actions"><h4 class="eum-plugins-name" style="margin:0;">'.$name.'</h4></div>
                        <div class="plugin-description"><p>'.$description.'</p></div>
                        <div class="active second plugin-version-author-uri">
                        <div><span>Current Version </span><strong><span class="wpvivid-rollback-current-version" style="color: orange;">'.$current_version.'</span></strong> | <span>Latest Version </span><strong><span style="color: green;">'.$new_version.'</span></strong> </div>
                        <div>'.$plugin_active.'</div></div>
                    </td>

                </tr>
                </tbody>
            </table>';

        return $html;
    }

    public function get_theme_detail($them_data,$slug)
    {
        $icon= '<span class="dashicons dashicons-admin-plugins"></span>';
        if(isset($them_data['icon']))
        {
            $icon = '<img src="' . esc_url(  $them_data['icon'] ) . '" width="85" height="64" class="updates-table-screenshot" alt="" />';
        }

        $name=$them_data["Name"];
        $description=$them_data["Description"];
        $current_version=$them_data['Version'];
        $new_version= $them_data['new_version'];

        if ( get_stylesheet() === $slug )
        {
            $theme_active="This theme is active for your site.";
        }
        else
        {
            $theme_active="";
        }

        $html='<table class="wp-list-table widefat plugins" style="margin-bottom:0.5rem;">
                <tbody id="the-list" data-wp-lists="list:plugin">
                <tr class="active">
                    <th class="plugin-title">
                        '.$icon.'
                    </th>
                    <td class="column-description desc">
                        <div class="eum-plugins-name-actions"><h4 class="eum-plugins-name" style="margin:0;">'.$name.'</h4></div>
                        <div class="plugin-description"><p>'.$description.'</p></div>
                        <div class="active second plugin-version-author-uri">
                        <div><span>Current Version </span><strong><span class="wpvivid-rollback-current-version" style="color: orange;">'.$current_version.'</span></strong> | <span>Latest Version </span><strong><span style="color: green;">'.$new_version.'</span></strong> </div>
                        <div>'.$theme_active.'</div></div>
                    </td>

                </tr>
                </tbody>
            </table>';

        return $html;
    }

    public function get_plugins_list()
    {
        global $wpvivid_backup_pro;
        $wpvivid_backup_pro->ajax_check_security();
        try
        {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
            $all_plugins     = get_plugins();
            $current = get_site_transient( 'update_plugins' );
            $plugins = array();

            $plugins_auto_backup_status=get_option('wpvivid_plugins_auto_backup_status',array());

            foreach ((array) $all_plugins as $plugin_file => $plugin_data)
            {
                if(!isset($plugin_data['Version'])||empty($plugin_data['Version']))
                {
                    continue;
                }

                if ( isset( $current->response[ $plugin_file ] ) )
                {
                    $plugins[ $plugin_file ]= $plugin_data;
                    $plugins[ $plugin_file ]['response']= (array)$current->response[ $plugin_file ];
                }
                else if( isset( $current->no_update[ $plugin_file ] ) )
                {
                    $plugins[ $plugin_file ]= $plugin_data;
                    $plugins[ $plugin_file ]['response']= (array)$current->no_update[ $plugin_file ];
                }
                else
                {
                    $plugins[ $plugin_file ]= $plugin_data;
                    $plugins[ $plugin_file ]['response']['new_version']='-';
                    $plugins[ $plugin_file ]['response']['slug']=$this->get_plugin_slug($plugin_file);
                }

                if(isset($plugins_auto_backup_status[$plugins[ $plugin_file ]['response']['slug']]))
                {
                    $plugins[ $plugin_file ]['enable_auto_backup']= $plugins_auto_backup_status[$plugins[ $plugin_file ]['response']['slug']];
                }
                else
                {
                    $plugins[ $plugin_file ]['enable_auto_backup']= false;
                }

                $plugins[ $plugin_file ]['rollback']=$this->get_rollback_data($plugin_file);
            }

            $table=new WPvivid_Rollback_Plugins_List();
            if(isset($_POST['page']))
            {
                $table->set_list($plugins,$_POST['page']);
            }
            else
            {
                $table->set_list($plugins);
            }
            $table->prepare_items();
            ob_start();
            $table->display();
            $html = ob_get_clean();

            $ret['result']='success';
            $ret['html']=$html;
            $ret['plugins']=$plugins;
            echo json_encode($ret);
        }
        catch (Exception $error)
        {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function get_themes_list()
    {
        global $wpvivid_backup_pro;
        $wpvivid_backup_pro->ajax_check_security();
        try
        {
            $current = get_site_transient( 'update_themes' );
            $themes =wp_get_themes();
            $themes_list=array();

            $themes_auto_backup_status=get_option('wpvivid_themes_auto_backup_status',array());

            foreach ($themes as $key=>$theme)
            {
                $stylesheet=$theme->get_stylesheet();
                $them_data["name"]=$theme->display( 'Name' );
                $them_data["version"]=$theme->display( 'Version' );
                $them_data["slug"]=$key;

                if ( isset( $current->response[ $stylesheet ] ) )
                {
                    $update=(array)$current->response[ $stylesheet ];
                    $them_data["new_version"]=$update['new_version'];
                }
                else if( isset( $current->no_update[ $stylesheet ] ) )
                {
                    $update=(array)$current->no_update[ $stylesheet ];
                    $them_data["new_version"]=$update['new_version'];
                }
                else
                {
                    $them_data['new_version']='-';
                }

                $them_data['rollback']=$this->get_rollback_data($key,'themes');

                if(isset($themes_auto_backup_status[$key]))
                {
                    $them_data['enable_auto_backup']= $themes_auto_backup_status[$key];
                }
                else
                {
                    $them_data['enable_auto_backup']= false;
                }

                $themes_list[ $stylesheet ]= $them_data;
            }

            $table=new WPvivid_Themes_List();
            if(isset($_POST['page']))
            {
                $table->set_list($themes_list,$_POST['page']);
            }
            else
            {
                $table->set_list($themes_list);
            }
            $table->prepare_items();
            ob_start();
            $table->display();
            $html = ob_get_clean();

            $ret['result']='success';
            $ret['html']=$html;
            echo json_encode($ret);
        }
        catch (Exception $error)
        {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function get_core_list()
    {
        global $wpvivid_backup_pro;
        $wpvivid_backup_pro->ajax_check_security();
        try
        {
            $core_list=$this->get_core_data();
            $table=new WPvivid_Core_List();
            if(isset($_POST['page']))
            {
                $table->set_list($core_list,$_POST['page']);
            }
            else
            {
                $table->set_list($core_list);
            }
            $table->prepare_items();
            ob_start();
            $table->display();
            $html = ob_get_clean();

            $ret['result']='success';
            $ret['html']=$html;
            echo json_encode($ret);
        }
        catch (Exception $error)
        {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function download_rollback()
    {
        global $wpvivid_backup_pro;
        $wpvivid_backup_pro->ajax_check_security();

        if (isset($_REQUEST['slug']) && !empty($_REQUEST['slug']) && is_string($_REQUEST['slug']))
        {
            $slug = sanitize_key($_REQUEST['slug']);
        }
        else
        {
            die();
        }

        if (isset($_REQUEST['version']) && !empty($_REQUEST['version']) && is_string($_REQUEST['version']))
        {
            $version = sanitize_text_field($_REQUEST['version']);
        }
        else
        {
            die();
        }

        if (isset($_REQUEST['type']) && !empty($_REQUEST['type']) && is_string($_REQUEST['type']))
        {
            $type = sanitize_text_field($_REQUEST['type']);
        }
        else
        {
            die();
        }

        $package=WP_CONTENT_DIR.'/'.WPvivid_Setting::get_backupdir().'/'.'rollback/'.$type.'/'.$slug.'/' . $version.'/'.$slug.'.zip';

        if(!file_exists($package))
        {
            echo $package;
            die();
        }

        if (file_exists($package))
        {
            if (session_id())
                session_write_close();

            $size = filesize($package);
            if (!headers_sent())
            {
                header('Content-Description: File Transfer');
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . basename($package) . '"');
                header('Cache-Control: must-revalidate');
                header('Content-Length: ' . $size);
                header('Content-Transfer-Encoding: binary');
            }

            @ini_set( 'memory_limit', '512M' );

            if ($size < 1024 * 1024 * 60) {
                ob_end_clean();
                readfile($package);
                exit;
            } else {
                ob_end_clean();
                $download_rate = 1024 * 10;
                $file = fopen($package, "r");
                while (!feof($file)) {
                    @set_time_limit(20);
                    // send the current file part to the browser
                    print fread($file, round($download_rate * 1024));
                    // flush the content to the browser
                    ob_flush();
                    flush();
                    // sleep one second
                    sleep(1);
                }
                fclose($file);
                exit;
            }
        }
    }

    public function download_core_rollback()
    {
        global $wpvivid_backup_pro;
        $wpvivid_backup_pro->ajax_check_security();

        if (isset($_REQUEST['version']) && !empty($_REQUEST['version']) && is_string($_REQUEST['version']))
        {
            $version = sanitize_text_field($_REQUEST['version']);
        }
        else
        {
            die();
        }

        $package=WP_CONTENT_DIR.'/'.WPvivid_Setting::get_backupdir().'/'.'rollback/core/'. $version.'/wordpress.zip';

        if(!file_exists($package))
        {
            echo $package;
            die();
        }

        if (file_exists($package))
        {
            if (session_id())
                session_write_close();

            $size = filesize($package);
            if (!headers_sent())
            {
                header('Content-Description: File Transfer');
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . basename($package) . '"');
                header('Cache-Control: must-revalidate');
                header('Content-Length: ' . $size);
                header('Content-Transfer-Encoding: binary');
            }

            @ini_set( 'memory_limit', '512M' );

            if ($size < 1024 * 1024 * 60) {
                ob_end_clean();
                readfile($package);
                exit;
            } else {
                ob_end_clean();
                $download_rate = 1024 * 10;
                $file = fopen($package, "r");
                while (!feof($file)) {
                    @set_time_limit(20);
                    // send the current file part to the browser
                    print fread($file, round($download_rate * 1024));
                    // flush the content to the browser
                    ob_flush();
                    flush();
                    // sleep one second
                    sleep(1);
                }
                fclose($file);
                exit;
            }
        }
    }

    public function get_rollback_list()
    {
        global $wpvivid_backup_pro;
        $wpvivid_backup_pro->ajax_check_security();
        try
        {
            if (isset($_POST['slug']) && !empty($_POST['slug']) && is_string($_POST['slug']))
            {
                $slug = sanitize_key($_POST['slug']);
            }
            else
            {
                die();
            }

            if (isset($_POST['type']) && !empty($_POST['type']) && is_string($_POST['type']))
            {
                $type = sanitize_text_field($_POST['type']);
            }
            else
            {
                die();
            }

            if($type=='plugins')
            {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';

                $all_plugins     = get_plugins();
                $current_plugin_file='';
                foreach ((array) $all_plugins as $plugin_file => $plugin_data)
                {
                    if($this->get_plugin_slug($plugin_file)==$slug)
                    {
                        $current_plugin_file=$plugin_file;
                        break;
                    }
                }

                if(empty($current_plugin_file))
                {
                    $ret['result']='failed';
                    $ret['error']='plugin not found.';
                    echo json_encode($ret);
                    die();
                }

                $current = get_site_transient( 'update_plugins' );

                $plugin_data=get_plugin_data(WP_PLUGIN_DIR.'/'.$current_plugin_file);

                if ( isset( $current->response[ $current_plugin_file ] ) )
                {
                    $plugin_data['response']= (array)$current->response[ $current_plugin_file ];
                }
                else if( isset( $current->no_update[ $current_plugin_file ] ) )
                {
                    $plugin_data['response']= (array)$current->no_update[ $current_plugin_file ];
                }
                else
                {
                    $plugin_data['response']['new_version']='-';
                }

                $plugin_data['rollback']=$this->get_rollback_data($current_plugin_file);

                $rollback_list=array();
                if(!empty($plugin_data['rollback']))
                {
                    foreach ($plugin_data['rollback'] as $version=>$file)
                    {
                        $info=$this->get_rollback_file_info($slug,$version,$file);
                        $rollback['version']=$version;
                        $rollback['slug']=$slug;
                        $rollback['date']=$info['date'];
                        $rollback['size']=$info['size'];
                        $rollback_list[$version]=$rollback;
                    }
                }

                $ret['result']='success';
                $table=new WPvivid_Rollback_List();
                if(isset($_POST['page']))
                {
                    $table->set_list($rollback_list,$_POST['page']);
                }
                else
                {
                    $table->set_list($rollback_list);
                }

                $table->prepare_items();
                ob_start();
                $table->display();
                $ret['html'] = ob_get_clean();

                echo json_encode($ret);
            }
            else
            {
                $theme=wp_get_theme($slug);

                $them_data["Name"]=$theme->display( 'Name' );
                $them_data["Version"]=$theme->display( 'Version' );
                $them_data["Description"]=$theme->display( 'Description' );
                $them_data["icon"]=$theme->get_screenshot() . '?ver=' . $theme->get( 'Version' );
                $current = get_site_transient( 'update_themes' );

                if ( isset( $current->response[ $slug ] ) )
                {
                    $update=(array)$current->response[ $slug ];
                    $them_data["new_version"]=$update['new_version'];
                    $them_data['response']= $update;
                }
                else if( isset( $current->no_update[ $slug ] ) )
                {
                    $update=(array)$current->no_update[ $slug ];
                    $them_data["new_version"]=$update['new_version'];
                    $them_data['response']= $update;
                }
                else
                {
                    $them_data['new_version']='-';
                    $them_data['response']= array();
                }


                $them_data['rollback']=$this->get_rollback_data($slug,'themes');

                $rollback_list=array();
                if(!empty($them_data['rollback']))
                {
                    foreach ($them_data['rollback'] as $version=>$file)
                    {
                        $info=$this->get_rollback_file_info($slug,$version,$file,'themes');
                        $rollback['version']=$version;
                        $rollback['slug']=$slug;
                        $rollback['date']=$info['date'];
                        $rollback['size']=$info['size'];
                        $rollback_list[$version]=$rollback;
                    }
                }

                $ret['result']='success';
                $table=new WPvivid_Rollback_List();
                if(isset($_POST['page']))
                {
                    $table->set_list($rollback_list,$_POST['page']);
                }
                else
                {
                    $table->set_list($rollback_list);
                }
                $table->prepare_items();
                ob_start();
                $table->display();
                $ret['html'] = ob_get_clean();

                echo json_encode($ret);
            }


            die();
        }
        catch (Exception $error)
        {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function delete_rollback()
    {
        global $wpvivid_backup_pro;
        $wpvivid_backup_pro->ajax_check_security();

        if (isset($_POST['versions']) && !empty($_POST['versions']) && is_array($_POST['versions']))
        {
            $versions=$_POST['versions'];

            if (isset($_POST['slug']) && !empty($_POST['slug']) && is_string($_POST['slug']))
            {
                $slug = sanitize_key($_POST['slug']);
            }
            else
            {
                die();
            }

            if (isset($_POST['type']) && !empty($_POST['type']) && is_string($_POST['type']))
            {
                $type = sanitize_text_field($_POST['type']);
            }
            else
            {
                die();
            }

            $path=WP_CONTENT_DIR.'/'.WPvivid_Setting::get_backupdir().'/'.'rollback/'.$type.'/'.$slug ;

            foreach ($versions as $version)
            {
                if(file_exists($path.'/'.$version.'/'.$slug.'.zip'))
                {
                    @unlink($path.'/'.$version.'/'.$slug.'.zip');
                    @rmdir($path.'/'.$version);
                }
            }


            $ret['result']='success';
            echo json_encode($ret);

            die();
        }
        else
        {
            die();
        }
    }

    public function delete_core_rollback()
    {
        global $wpvivid_backup_pro;
        $wpvivid_backup_pro->ajax_check_security();

        if (isset($_POST['versions']) && !empty($_POST['versions']) && is_array($_POST['versions']))
        {
            $versions=$_POST['versions'];

            $path=WP_CONTENT_DIR.'/'.WPvivid_Setting::get_backupdir().'/'.'rollback/core' ;

            foreach ($versions as $version)
            {
                if(file_exists($path.'/'.$version.'/wordpress.zip'))
                {
                    @unlink($path.'/'.$version.'/wordpress.zip');
                    @rmdir($path.'/'.$version);
                }
            }


            $core_list=$this->get_core_data();
            $table=new WPvivid_Core_List();
            if(isset($_POST['page']))
            {
                $table->set_list($core_list,$_POST['page']);
            }
            else
            {
                $table->set_list($core_list);
            }
            $table->prepare_items();
            ob_start();
            $table->display();
            $html = ob_get_clean();

            $ret['result']='success';
            $ret['html']=$html;
            echo json_encode($ret);

            die();
        }
        else
        {
            die();
        }
    }

    public function rollback_core()
    {
        global $wpvivid_backup_pro;
        $wpvivid_backup_pro->ajax_check_security();

        if (isset($_POST['version']) && !empty($_POST['version']) && is_string($_POST['version']))
        {
            $version = sanitize_text_field($_POST['version']);
        }
        else
        {
            die();
        }

        $package=WP_CONTENT_DIR.'/'.WPvivid_Setting::get_backupdir().'/'.'rollback/core/'. $version.'/wordpress.zip';
        if(!file_exists($package))
        {
            $ret['result']='failed';
            $ret['error']="Could not find the backup. Please check whether the backup exists.";
            echo json_encode($ret);
            die();
        }

        $rollback_core['version']=$version;
        $rollback_core['update_time']=time();
        $rollback_core['restore_timeout_count']=0;
        $rollback_core['status']='ready';

        update_option('wpvivid_core_rollback_task',$rollback_core);

        $ret['result']='success';
        echo json_encode($ret);
        die();
    }

    public function do_rollback_core()
    {
        global $wpvivid_backup_pro;
        $wpvivid_backup_pro->ajax_check_security();

        set_time_limit(300);

        $rollback_core=get_option('wpvivid_core_rollback_task',false);

        if(!isset($rollback_core['version'])||empty($rollback_core['version']))
        {
            $rollback_core['status']='error';
            $rollback_core['error']="Could not find the backup. Please check whether the backup exists.";
            update_option('wpvivid_core_rollback_task',$rollback_core);

            $ret['result']='failed';
            $ret['error']="Could not find the backup. Please check whether the backup exists.";
            echo json_encode($ret);
            die();
        }

        $version=$rollback_core['version'];
        $package=WP_CONTENT_DIR.'/'.WPvivid_Setting::get_backupdir().'/'.'rollback/core/'. $version.'/wordpress.zip';
        if(!file_exists($package))
        {
            $rollback_core['status']='error';
            $rollback_core['error']="Could not find the backup. Please check whether the backup exists.";
            update_option('wpvivid_core_rollback_task',$rollback_core);

            $ret['result']='failed';
            $ret['error']="Could not find the backup. Please check whether the backup exists.";
            echo json_encode($ret);
            die();
        }

        $rollback_core['status']='running';
        $rollback_core['update_time']=time();
        update_option('wpvivid_core_rollback_task',$rollback_core);

        if (!class_exists('WPvivid_PclZip'))
            include_once WPVIVID_BACKUP_PRO_PLUGIN_DIR . 'includes/zip/class-wpvivid-pclzip.php';

        if(!defined('PCLZIP_TEMPORARY_DIR'))
            define(PCLZIP_TEMPORARY_DIR,dirname($package));

        $root_path = $this->transfer_path(ABSPATH);

        $root_path = rtrim($root_path, '/');
        $root_path = rtrim($root_path, DIRECTORY_SEPARATOR);

        $archive = new WPvivid_PclZip($package);
        $zip_ret = $archive->extract(WPVIVID_PCLZIP_OPT_PATH, $root_path,WPVIVID_PCLZIP_OPT_REPLACE_NEWER,WPVIVID_PCLZIP_CB_PRE_EXTRACT,'wpvivid_pro_function_pre_core_extract_callback',WPVIVID_PCLZIP_OPT_TEMP_FILE_THRESHOLD,16);
        if(!$zip_ret)
        {
            $ret['result']='failed';
            $ret['error'] = $archive->errorInfo(true);
            $rollback_core['status']='error';
            $rollback_core['error']=$ret['error'];
            update_option('wpvivid_core_rollback_task',$rollback_core);

            echo json_encode($ret);
            die();
        }
        else
        {
            $ret['result']='success';
            $rollback_core['status']='completed';
            update_option('wpvivid_core_rollback_task',$rollback_core);
            echo json_encode($ret);
            die();
        }
    }

    public function get_rollback_core_progress()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );

        $rollback_task=get_option('wpvivid_core_rollback_task',false);

        if($rollback_task==false)
        {
            $ret['result']='failed';
            $ret['error']='restore task has error';
            $ret['test']=$rollback_task;
            echo json_encode($ret);
            die();
        }

        $ret['test']=$rollback_task;
        if($rollback_task['status']=='error')
        {
            $ret['result']='failed';
            $ret['error']=$rollback_task['error'];
            echo json_encode($ret);
            die();
        }
        else if($rollback_task['status']=='ready')
        {
            $ret['result']='success';
            $ret['status']='running';
            echo json_encode($ret);
            die();
        }
        else if($rollback_task['status']=='running')
        {
            if(time()-$rollback_task['update_time']>300)
            {
                $ret['result']='failed';
                $ret['error']='restore timeout';
                echo json_encode($ret);
                die();
            }
            else
            {
                $ret['result']='success';
                $ret['status']='running';
                echo json_encode($ret);
                die();
            }
        }
        else if($rollback_task['status']=='completed')
        {
            $ret['result']='success';
            $ret['status']='completed';
            echo json_encode($ret);
            die();
        }
        else
        {
            $ret['result']='failed';
            $ret['error']='restore task has error';
            $ret['test']=$rollback_task;
            echo json_encode($ret);
            die();
        }
    }
}

if ( ! class_exists( 'WP_List_Table' ) )
{
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WPvivid_Rollback_Plugins_List extends WP_List_Table
{
    public $page_num;
    public $plugins_list;

    public function __construct( $args = array() )
    {
        parent::__construct(
            array(
                'plural' => 'wpvivid_plugins',
                'screen' => 'wpvivid_plugins'
            )
        );
    }

    protected function get_table_classes()
    {
        return array( 'widefat plugins' );
    }

    public function print_column_headers( $with_id = true )
    {
        list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

        if (!empty($columns['cb'])) {
            static $cb_counter = 1;
            $columns['cb'] = '<label class="screen-reader-text" for="cb-select-all-' . $cb_counter . '">' . __('Select All') . '</label>'
                . '<input id="cb-select-all-' . $cb_counter . '" type="checkbox"/>';
            $cb_counter++;
        }

        foreach ( $columns as $column_key => $column_display_name )
        {
            $class = array( 'manage-column', "column-$column_key" );

            if ( in_array( $column_key, $hidden ) )
            {
                $class[] = 'hidden';
            }

            if ( $column_key === $primary )
            {
                $class[] = 'column-primary';
            }

            if ( $column_key === 'cb' )
            {
                $class[] = 'wpvivid-check-column';
            }

            $tag   = ( 'cb' === $column_key ) ? 'td' : 'th';
            $scope = ( 'th' === $tag ) ? 'scope="col"' : '';
            $id    = $with_id ? "id='$column_key'" : '';

            if ( ! empty( $class ) )
            {
                $class = "class='" . join( ' ', $class ) . "'";
            }

            if ( $column_key === 'wpvivid_status' )
            {
                $column_display_name='<div>Status
													<span class="dashicons dashicons-editor-help wpvivid-dashicons-editor-help wpvivid-tooltip">
													<div class="wpvivid-bottom">
														<!-- The content you need -->
														<p>Enable/Disable "Back up the plugin(s) before update".</p>
														<i></i> <!-- do not delete this line -->
													</div>
													</span>
													</div>';
            }

            echo "<$tag $scope $id $class>$column_display_name</$tag>";
        }
    }

    public function get_columns()
    {
        $columns = array();
        $columns['cb'] = __( 'cb', 'wpvivid' );
        $columns['wpvivid_status'] = __( 'Status', 'wpvivid' );
        $columns['wpvivid_plugins'] = __( 'Plugins', 'wpvivid' );
        $columns['wpvivid_version'] =__( 'Current/Latest Version', 'wpvivid'  );
        $columns['wpvivid_version_backup'] = __( 'Versioning Backups', 'wpvivid' );
        $columns['wpvivid_rollback'] = __( 'Rollback', 'wpvivid' );
        return $columns;
    }

    public function column_cb( $plugin )
    {
        $html='<input type="checkbox"/>';
        echo $html;
    }

    public function _column_wpvivid_status( $plugin )
    {
        if(isset($plugin['enable_auto_backup'])&&$plugin['enable_auto_backup'])
        {
            $enable = 'checked';
        }
        else
        {
            $enable = '';
        }
        ?>
        <td scope="col" class="manage-column column-wpvivid_backup column-primary">
            <label class="wpvivid-switch" title="Enable/Disable the job">
                <input class="wpvivid-enable-auto-backup" type="checkbox" <?php echo $enable;?> />
                <span class="wpvivid-slider wpvivid-round"></span>
            </label>
        </td>
        <?php
    }

    public function _column_wpvivid_plugins( $plugin )
    {
        ?>
        <td scope="col" id="wpvivid_content" class="manage-column"><?php echo $plugin['Name']?></td>
        <?php
    }

    public function _column_wpvivid_version( $plugin )
    {
        ?>
        <td scope="col" class="manage-column wpvivid-version">
            <span class="current-version"><?php echo $plugin['Version']?></span><span style="padding:0 0.3rem">|</span><span><?php echo $plugin['response']['new_version']?></span>
        </td>
        <?php
    }

    public function _column_wpvivid_version_backup( $plugin )
    {
        ?>
        <td scope="col" class="manage-column">
            <span class="wpvivid-view-plugin-versions dashicons dashicons-visibility wpvivid-dashicons-grey" title="View details" style="cursor:pointer;"></span>
            <span class="wpvivid-view-plugin-versions" style="cursor:pointer;">View versioning backups</span>
        </td>
        <?php
    }

    public function _column_wpvivid_rollback( $plugin )
    {
        $rollback=$plugin['rollback'];
        ?>
        <td scope="col" class="manage-column">
            <select>
                <option value="" selected="selected">-</option>
                <?php
                if(!empty($rollback))
                {
                    foreach ($rollback as $version=>$file)
                    {
                        echo '<option value="'.$version.'">'.$version.'</option>';
                    }
                }
                ?>
            </select>
            <input class="wpvivid-plugin-rollback button action" type="submit" value="Rollback">
        </td>
        <?php
    }

    public function set_list($plugins,$page_num=1)
    {
        $this->plugins_list=$plugins;
        $this->page_num=$page_num;
    }

    public function get_pagenum()
    {
        if($this->page_num=='first')
        {
            $this->page_num=1;
        }
        else if($this->page_num=='last')
        {
            $this->page_num=$this->_pagination_args['total_pages'];
        }
        $pagenum = $this->page_num ? $this->page_num : 0;

        if ( isset( $this->_pagination_args['total_pages'] ) && $pagenum > $this->_pagination_args['total_pages'] )
        {
            $pagenum = $this->_pagination_args['total_pages'];
        }

        return max( 1, $pagenum );
    }

    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array();
        $this->_column_headers = array($columns, $hidden, $sortable);

        if(empty($this->plugins_list))
        {
            $total_items=0;
        }
        else
        {
            $total_items =sizeof($this->plugins_list);
        }

        $this->set_pagination_args(
            array(
                'total_items' => $total_items,
                'per_page'    => 30,
            )
        );
    }

    public function has_items()
    {
        return !empty($this->plugins_list);
    }

    public function display_rows()
    {
        $this->_display_rows($this->plugins_list);
    }

    private function _display_rows($plugins_list)
    {
        $page=$this->get_pagenum();

        $page_plugins_list=array();
        $temp_page_plugins_list=array();

        if(empty($plugins_list))
        {
            return;
        }

        foreach ( $plugins_list as $key=>$plugin)
        {
            $page_plugins_list[$key]=$plugin;
        }

        $count=0;
        while ( $count<$page )
        {
            $temp_page_plugins_list = array_splice( $page_plugins_list, 0, 30);
            $count++;
        }

        foreach ( $temp_page_plugins_list as $key=>$plugin)
        {
            $plugin['plugin_file']=$key;
            $this->single_row($plugin);
        }
    }

    public function single_row($plugin)
    {
        $row_style = 'display: table-row;';

        if(is_plugin_active($plugin['plugin_file']))
        {
            $class='active';
        }
        else
        {
            $class='';
        }
        ?>
        <tr style="<?php echo $row_style?>" class='wpvivid-backup-row <?php echo $class?>' id="<?php echo $plugin['response']['slug'];?>">
            <?php $this->single_row_columns( $plugin ); ?>
        </tr>
        <?php
    }

    protected function pagination( $which )
    {
        if ( empty( $this->_pagination_args ) )
        {
            return;
        }

        $total_items     = $this->_pagination_args['total_items'];
        $total_pages     = $this->_pagination_args['total_pages'];
        $infinite_scroll = false;
        if ( isset( $this->_pagination_args['infinite_scroll'] ) )
        {
            $infinite_scroll = $this->_pagination_args['infinite_scroll'];
        }

        if ( 'top' === $which && $total_pages > 1 )
        {
            $this->screen->render_screen_reader_content( 'heading_pagination' );
        }

        $output = '<span class="displaying-num">' . sprintf( _n( '%s item', '%s items', $total_items ), number_format_i18n( $total_items ) ) . '</span>';

        $current              = $this->get_pagenum();

        $page_links = array();

        $total_pages_before = '<span class="paging-input">';
        $total_pages_after  = '</span></span>';

        $disable_first = $disable_last = $disable_prev = $disable_next = false;

        if ( $current == 1 ) {
            $disable_first = true;
            $disable_prev  = true;
        }
        if ( $current == 2 ) {
            $disable_first = true;
        }
        if ( $current == $total_pages ) {
            $disable_last = true;
            $disable_next = true;
        }
        if ( $current == $total_pages - 1 ) {
            $disable_last = true;
        }

        if ( $disable_first ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='first-page button'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                __( 'First page' ),
                '&laquo;'
            );
        }

        if ( $disable_prev ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='prev-page button' value='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                $current,
                __( 'Previous page' ),
                '&lsaquo;'
            );
        }

        if ( 'bottom' === $which ) {
            $html_current_page  = $current;
            $total_pages_before = '<span class="screen-reader-text">' . __( 'Current Page' ) . '</span><span id="table-paging" class="paging-input"><span class="tablenav-paging-text">';
        } else {
            $html_current_page = sprintf(
                "%s<input class='current-page' id='current-page-selector-backuplist' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' /><span class='tablenav-paging-text'>",
                '<label for="current-page-selector-backuplist" class="screen-reader-text">' . __( 'Current Page' ) . '</label>',
                $current,
                strlen( $total_pages )
            );
        }
        $html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
        $page_links[]     = $total_pages_before . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . $total_pages_after;

        if ( $disable_next ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='next-page button' value='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                $current,
                __( 'Next page' ),
                '&rsaquo;'
            );
        }

        if ( $disable_last ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='last-page button'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                __( 'Last page' ),
                '&raquo;'
            );
        }

        $pagination_links_class = 'pagination-links';
        if ( ! empty( $infinite_scroll ) ) {
            $pagination_links_class .= ' hide-if-js';
        }
        $output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';

        if ( $total_pages ) {
            $page_class = $total_pages < 2 ? ' one-page' : '';
        } else {
            $page_class = ' no-pages';
        }
        $this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

        echo $this->_pagination;
    }

    protected function display_tablenav( $which ) {
        $css_type = '';
        if ( 'top' === $which ) {
            wp_nonce_field( 'bulk-' . $this->_args['plural'] );
            $css_type = 'margin: 0 0 10px 0';
        }
        else if( 'bottom' === $which ) {
            $css_type = 'margin: 10px 0 0 0';
        }

        $total_pages     = $this->_pagination_args['total_pages'];
        if ( $total_pages >1)
        {
            ?>
            <div class="tablenav <?php echo esc_attr( $which ); ?>" style="<?php esc_attr_e($css_type); ?>">
                <div class="alignleft actions bulkactions" style="padding:0.5rem 0;">
                    <label for="wpvivid_plugins_bulk_action" class="screen-reader-text">Select bulk action</label>
                    <select class="wpvivid-plugins-bulk-action" id="wpvivid_plugins_bulk_action">
                        <option value="-1" selected="selected">Bulk Actions</option>
                        <option value="enable">Enable</option>
                        <option value="disable">Disable</option>
                    </select>
                    <input type="submit" id="wpvivid_apply_plugins_bulk_action" class="button action" value="Apply">
                </div>
                <?php
                $this->extra_tablenav( $which );
                $this->pagination( $which );
                ?>

                <br class="clear" />
            </div>
            <?php
        }
        else
        {
            ?>
            <div class="alignleft actions bulkactions" style="padding:0.5rem 0;">
                <label for="wpvivid_plugins_bulk_action" class="screen-reader-text">Select bulk action</label>
                <select class="wpvivid-plugins-bulk-action" id="wpvivid_plugins_bulk_action">
                    <option value="-1" selected="selected">Bulk Actions</option>
                    <option value="enable">Enable</option>
                    <option value="disable">Disable</option>
                </select>
                <input type="submit" id="wpvivid_apply_plugins_bulk_action" class="button action" value="Apply">
            </div>
            <?php
        }
    }
}

class WPvivid_Themes_List extends WP_List_Table
{
    public $page_num;
    public $themes_list;

    public function __construct( $args = array() )
    {
        parent::__construct(
            array(
                'plural' => 'wpvivid_themes',
                'screen' => 'wpvivid_themes'
            )
        );
    }

    protected function get_table_classes()
    {
        return array( 'widefat plugins' );
    }

    public function print_column_headers( $with_id = true )
    {
        list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

        if (!empty($columns['cb'])) {
            static $cb_counter = 1;
            $columns['cb'] = '<label class="screen-reader-text" for="cb-select-all-' . $cb_counter . '">' . __('Select All') . '</label>'
                . '<input id="cb-select-all-' . $cb_counter . '" type="checkbox"/>';
            $cb_counter++;
        }

        foreach ( $columns as $column_key => $column_display_name )
        {
            $class = array( 'manage-column', "column-$column_key" );

            if ( in_array( $column_key, $hidden ) )
            {
                $class[] = 'hidden';
            }

            if ( $column_key === $primary )
            {
                $class[] = 'column-primary';
            }

            if ( $column_key === 'cb' )
            {
                $class[] = 'wpvivid-check-column';
            }

            $tag   = ( 'cb' === $column_key ) ? 'td' : 'th';
            $scope = ( 'th' === $tag ) ? 'scope="col"' : '';
            $id    = $with_id ? "id='$column_key'" : '';

            if ( ! empty( $class ) )
            {
                $class = "class='" . join( ' ', $class ) . "'";
            }

            if($column_key=="wpvivid_status")
            {
                $column_display_name='<div>Status
													<span class="dashicons dashicons-editor-help wpvivid-dashicons-editor-help wpvivid-tooltip wpvivid-tooltip-padding-top" style="padding-top: 0px;">
													<div class="wpvivid-bottom">
														<!-- The content you need -->
														<p>Enable/Disable "Back up the theme(s) before update".</p>
														<i></i> <!-- do not delete this line -->
													</div>
													</span>
													</div>';
            }
            echo "<$tag $scope $id $class>$column_display_name</$tag>";
        }
    }

    public function get_columns()
    {
        $columns = array();
        $columns['cb'] = __( 'cb', 'wpvivid' );
        $columns['wpvivid_status'] = __( 'Status', 'wpvivid' );
        $columns['wpvivid_themes'] = __( 'Themes', 'wpvivid' );
        $columns['wpvivid_version'] =__( 'Current/Latest Version', 'wpvivid'  );
        $columns['wpvivid_version_backup'] = __( 'Versioning Backups', 'wpvivid' );
        $columns['wpvivid_rollback'] = __( 'Rollback', 'wpvivid' );
        return $columns;
    }

    public function column_cb( $theme )
    {
        $html='<input type="checkbox"/>';
        echo $html;
    }

    public function _column_wpvivid_status( $theme )
    {
        if(isset($theme['enable_auto_backup'])&&$theme['enable_auto_backup'])
        {
            $enable = 'checked';
        }
        else
        {
            $enable = '';
        }
        ?>
        <td scope="col" class="manage-column column-primary">
            <label class="wpvivid-switch" title="Enable/Disable the job">
                <input class="wpvivid-enable-auto-backup" type="checkbox" <?php echo $enable;?> />
                <span class="wpvivid-slider wpvivid-round"></span>
            </label>
        </td>
        <?php
    }

    public function _column_wpvivid_themes( $theme )
    {
        ?>
        <td scope="col" class="manage-column"><?php echo $theme['name']?></td>
        <?php
    }

    public function _column_wpvivid_version( $theme )
    {
        ?>
        <td scope="col" class="manage-column">
            <span class="current-version"><?php echo $theme['version']?></span><span style="padding:0 0.3rem">|</span><span><?php echo $theme['new_version']?></span>
        </td>
        <?php
    }

    public function _column_wpvivid_version_backup( $theme )
    {
        ?>
        <td scope="col" class="manage-column">
            <span class="wpvivid-view-theme-versions dashicons dashicons-visibility wpvivid-dashicons-grey" title="View details" style="cursor:pointer;"></span>
            <span class="wpvivid-view-theme-versions" style="cursor:pointer;">View versioning backups</span>
        </td>
        <?php
    }

    public function _column_wpvivid_rollback( $theme )
    {
        $rollback=$theme['rollback'];
        ?>
        <td scope="col" class="manage-column">
            <select>
                <option value="" selected="selected">-</option>
                <?php
                if(!empty($rollback))
                {
                    foreach ($rollback as $version=>$file)
                    {
                        echo '<option value="'.$version.'">'.$version.'</option>';
                    }
                }
                ?>
            </select>
            <input class="wpvivid-theme-rollback button action" type="submit" value="Rollback">
        </td>
        <?php
    }

    public function set_list($plugins,$page_num=1)
    {
        $this->themes_list=$plugins;
        $this->page_num=$page_num;
    }

    public function get_pagenum()
    {
        if($this->page_num=='first')
        {
            $this->page_num=1;
        }
        else if($this->page_num=='last')
        {
            $this->page_num=$this->_pagination_args['total_pages'];
        }
        $pagenum = $this->page_num ? $this->page_num : 0;

        if ( isset( $this->_pagination_args['total_pages'] ) && $pagenum > $this->_pagination_args['total_pages'] )
        {
            $pagenum = $this->_pagination_args['total_pages'];
        }

        return max( 1, $pagenum );
    }

    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array();
        $this->_column_headers = array($columns, $hidden, $sortable);

        if(empty($this->themes_list))
        {
            $total_items=0;
        }
        else
        {
            $total_items =sizeof($this->themes_list);
        }

        $this->set_pagination_args(
            array(
                'total_items' => $total_items,
                'per_page'    => 30,
            )
        );
    }

    public function has_items()
    {
        return !empty($this->themes_list);
    }

    public function display_rows()
    {
        $this->_display_rows($this->themes_list);
    }

    private function _display_rows($themes_list)
    {
        $page=$this->get_pagenum();

        $page_themes_list=array();
        $temp_page_themes_list=array();

        if(empty($themes_list))
        {
            return;
        }

        foreach ( $themes_list as $key=>$theme)
        {
            $page_themes_list[$key]=$theme;
        }

        $count=0;
        while ( $count<$page )
        {
            $temp_page_themes_list = array_splice( $page_themes_list, 0, 30);
            $count++;
        }

        foreach ( $temp_page_themes_list as $key=>$theme)
        {
            $this->single_row($theme);
        }
    }

    public function single_row($theme)
    {
        $row_style = 'display: table-row;';

        if ( get_stylesheet() === $theme['slug'] )
        {
            $class='active';
        }
        else
        {
            $class='';
        }
        ?>
        <tr style="<?php echo $row_style?>" class='wpvivid-backup-row <?php echo $class?>' id="<?php echo $theme['slug'];?>">
            <?php $this->single_row_columns( $theme ); ?>
        </tr>
        <?php
    }

    protected function pagination( $which )
    {
        if ( empty( $this->_pagination_args ) )
        {
            return;
        }

        $total_items     = $this->_pagination_args['total_items'];
        $total_pages     = $this->_pagination_args['total_pages'];
        $infinite_scroll = false;
        if ( isset( $this->_pagination_args['infinite_scroll'] ) )
        {
            $infinite_scroll = $this->_pagination_args['infinite_scroll'];
        }

        if ( 'top' === $which && $total_pages > 1 )
        {
            $this->screen->render_screen_reader_content( 'heading_pagination' );
        }

        $output = '<span class="displaying-num">' . sprintf( _n( '%s item', '%s items', $total_items ), number_format_i18n( $total_items ) ) . '</span>';

        $current              = $this->get_pagenum();

        $page_links = array();

        $total_pages_before = '<span class="paging-input">';
        $total_pages_after  = '</span></span>';

        $disable_first = $disable_last = $disable_prev = $disable_next = false;

        if ( $current == 1 ) {
            $disable_first = true;
            $disable_prev  = true;
        }
        if ( $current == 2 ) {
            $disable_first = true;
        }
        if ( $current == $total_pages ) {
            $disable_last = true;
            $disable_next = true;
        }
        if ( $current == $total_pages - 1 ) {
            $disable_last = true;
        }

        if ( $disable_first ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='first-page button'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                __( 'First page' ),
                '&laquo;'
            );
        }

        if ( $disable_prev ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='prev-page button' value='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                $current,
                __( 'Previous page' ),
                '&lsaquo;'
            );
        }

        if ( 'bottom' === $which ) {
            $html_current_page  = $current;
            $total_pages_before = '<span class="screen-reader-text">' . __( 'Current Page' ) . '</span><span id="table-paging" class="paging-input"><span class="tablenav-paging-text">';
        } else {
            $html_current_page = sprintf(
                "%s<input class='current-page' id='current-page-selector-backuplist' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' /><span class='tablenav-paging-text'>",
                '<label for="current-page-selector-backuplist" class="screen-reader-text">' . __( 'Current Page' ) . '</label>',
                $current,
                strlen( $total_pages )
            );
        }
        $html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
        $page_links[]     = $total_pages_before . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . $total_pages_after;

        if ( $disable_next ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='next-page button' value='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                $current,
                __( 'Next page' ),
                '&rsaquo;'
            );
        }

        if ( $disable_last ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='last-page button'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                __( 'Last page' ),
                '&raquo;'
            );
        }

        $pagination_links_class = 'pagination-links';
        if ( ! empty( $infinite_scroll ) ) {
            $pagination_links_class .= ' hide-if-js';
        }
        $output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';

        if ( $total_pages ) {
            $page_class = $total_pages < 2 ? ' one-page' : '';
        } else {
            $page_class = ' no-pages';
        }
        $this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

        echo $this->_pagination;
    }

    protected function display_tablenav( $which ) {
        $css_type = '';
        if ( 'top' === $which ) {
            wp_nonce_field( 'bulk-' . $this->_args['plural'] );
            $css_type = 'margin: 0 0 10px 0';
        }
        else if( 'bottom' === $which ) {
            $css_type = 'margin: 10px 0 0 0';
        }

        $total_pages     = $this->_pagination_args['total_pages'];
        if ( $total_pages >1)
        {
            ?>
            <div class="tablenav <?php echo esc_attr( $which ); ?>" style="<?php esc_attr_e($css_type); ?>">
                <div class="alignleft actions bulkactions" style="padding:0.5rem 0;">
                    <label for="wpvivid_themes_bulk_action" class="screen-reader-text">Select bulk action</label>
                    <select class="wpvivid-themes-bulk-action" id="wpvivid_themes_bulk_action">
                        <option value="-1" selected="selected">Bulk Actions</option>
                        <option value="enable">Enable</option>
                        <option value="disable">Disable</option>
                    </select>
                    <input type="submit" id="wpvivid_apply_themes_bulk_action" class="button action" value="Apply">
                </div>
                <?php
                $this->extra_tablenav( $which );
                $this->pagination( $which );
                ?>

                <br class="clear" />
            </div>
            <?php
        }
        else
        {
            ?>
            <div class="alignleft actions bulkactions" style="padding:0.5rem 0;">
                <label for="wpvivid_themes_bulk_action" class="screen-reader-text">Select bulk action</label>
                <select class="wpvivid-themes-bulk-action" id="wpvivid_themes_bulk_action">
                    <option value="-1" selected="selected">Bulk Actions</option>
                    <option value="enable">Enable</option>
                    <option value="disable">Disable</option>
                </select>
                <input type="submit" id="wpvivid_apply_themes_bulk_action" class="button action" value="Apply">
            </div>
            <?php
        }
    }
}

class WPvivid_Core_List extends WP_List_Table
{
    public $page_num;
    public $core_list;

    public function __construct( $args = array() )
    {
        parent::__construct(
            array(
                'plural' => 'wpvivid_core',
                'screen' => 'wpvivid_core'
            )
        );
    }

    protected function get_table_classes()
    {
        return array( 'widefat striped plugins' );
    }

    public function print_column_headers( $with_id = true )
    {
        list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

        if (!empty($columns['cb'])) {
            static $cb_counter = 1;
            $columns['cb'] = '<label class="screen-reader-text" for="cb-select-all-' . $cb_counter . '">' . __('Select All') . '</label>'
                . '<input id="cb-select-all-' . $cb_counter . '" type="checkbox"/>';
            $cb_counter++;
        }

        foreach ( $columns as $column_key => $column_display_name )
        {
            $class = array( 'manage-column', "column-$column_key" );

            if ( in_array( $column_key, $hidden ) )
            {
                $class[] = 'hidden';
            }

            if ( $column_key === $primary )
            {
                $class[] = 'column-primary';
            }

            if ( $column_key === 'cb' )
            {
                $class[] = 'wpvivid-check-column';
            }

            $tag   = ( 'cb' === $column_key ) ? 'td' : 'th';
            $scope = ( 'th' === $tag ) ? 'scope="col"' : '';
            $id    = $with_id ? "id='$column_key'" : '';

            if ( ! empty( $class ) )
            {
                $class = "class='" . join( ' ', $class ) . "'";
            }

            echo "<$tag $scope $id $class>$column_display_name</$tag>";
        }
    }

    public function get_columns()
    {
        $columns = array();
        $columns['cb'] = __( 'cb', 'wpvivid' );
        $columns['wpvivid_version'] = __( 'Version', 'wpvivid' );
        $columns['wpvivid_modified'] = __( 'Modified', 'wpvivid' );
        $columns['wpvivid_size'] =__( 'Size', 'wpvivid'  );
        $columns['wpvivid_action'] = __( 'Action', 'wpvivid' );
        return $columns;
    }

    public function column_cb( $core )
    {
        $html='<input type="checkbox"/>';
        echo $html;
    }

    public function _column_wpvivid_version( $core )
    {
        ?>
        <td scope="col" class="manage-column column-primary">
            <strong>
                <?php echo $core['version'];?>
            </strong>
        </td>
        <?php
    }

    public function _column_wpvivid_modified( $core )
    {
        ?>
        <td scope="col" class="column-description desc">
            <?php echo $core['date']?>
        </td>
        <?php
    }

    public function _column_wpvivid_size( $core )
    {
        ?>
        <td scope="col" class="column-description desc">
            <?php echo $core['size']?>
        </td>
        <?php
    }

    public function _column_wpvivid_action( $core )
    {
        ?>
        <td class="column-description desc">
            <span class="dashicons dashicons-download wpvivid-dashicons-blue wpvivid-core-download" title="Download" style="cursor:pointer;"></span>
            <span class="dashicons dashicons-update-alt wpvivid-dashicons-blue wpvivid-rollback-core-version" style="cursor:pointer;" title="Rollback"></span>
        </td>
        <?php
    }

    public function set_list($core_list,$page_num=1)
    {
        $this->core_list=$core_list;
        $this->page_num=$page_num;
    }

    public function get_pagenum()
    {
        if($this->page_num=='first')
        {
            $this->page_num=1;
        }
        else if($this->page_num=='last')
        {
            $this->page_num=$this->_pagination_args['total_pages'];
        }
        $pagenum = $this->page_num ? $this->page_num : 0;

        if ( isset( $this->_pagination_args['total_pages'] ) && $pagenum > $this->_pagination_args['total_pages'] )
        {
            $pagenum = $this->_pagination_args['total_pages'];
        }

        return max( 1, $pagenum );
    }

    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array();
        $this->_column_headers = array($columns, $hidden, $sortable);

        if(empty($this->core_list))
        {
            $total_items=0;
        }
        else
        {
            $total_items =sizeof($this->core_list);
        }

        $this->set_pagination_args(
            array(
                'total_items' => $total_items,
                'per_page'    => 30,
            )
        );
    }

    public function has_items()
    {
        return !empty($this->core_list);
    }

    public function display_rows()
    {
        $this->_display_rows($this->core_list);
    }

    private function _display_rows($core_list)
    {
        $page=$this->get_pagenum();

        $page_core_list=array();
        $temp_page_core_list=array();

        if(empty($core_list))
        {
            return;
        }

        foreach ( $core_list as $key=>$core)
        {
            $page_core_list[$key]=$core;
        }

        $count=0;
        while ( $count<$page )
        {
            $temp_page_core_list = array_splice( $page_core_list, 0, 30);
            $count++;
        }

        foreach ( $temp_page_core_list as $key=>$core)
        {
            $this->single_row($core);
        }
    }

    public function single_row($core)
    {
        $row_style = 'display: table-row;';

        $class='';
        ?>
        <tr style="<?php echo $row_style?>" class='wpvivid-backup-row <?php echo $class?>' id="<?php echo $core['id'];?>" data-version="<?php echo $core['id'];?>">
            <?php $this->single_row_columns( $core ); ?>
        </tr>
        <?php
    }

    protected function pagination( $which )
    {
        if ( empty( $this->_pagination_args ) )
        {
            return;
        }

        $total_items     = $this->_pagination_args['total_items'];
        $total_pages     = $this->_pagination_args['total_pages'];
        $infinite_scroll = false;
        if ( isset( $this->_pagination_args['infinite_scroll'] ) )
        {
            $infinite_scroll = $this->_pagination_args['infinite_scroll'];
        }

        if ( 'top' === $which && $total_pages > 1 )
        {
            $this->screen->render_screen_reader_content( 'heading_pagination' );
        }

        $output = '<span class="displaying-num">' . sprintf( _n( '%s item', '%s items', $total_items ), number_format_i18n( $total_items ) ) . '</span>';

        $current              = $this->get_pagenum();

        $page_links = array();

        $total_pages_before = '<span class="paging-input">';
        $total_pages_after  = '</span></span>';

        $disable_first = $disable_last = $disable_prev = $disable_next = false;

        if ( $current == 1 ) {
            $disable_first = true;
            $disable_prev  = true;
        }
        if ( $current == 2 ) {
            $disable_first = true;
        }
        if ( $current == $total_pages ) {
            $disable_last = true;
            $disable_next = true;
        }
        if ( $current == $total_pages - 1 ) {
            $disable_last = true;
        }

        if ( $disable_first ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='first-page button'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                __( 'First page' ),
                '&laquo;'
            );
        }

        if ( $disable_prev ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='prev-page button' value='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                $current,
                __( 'Previous page' ),
                '&lsaquo;'
            );
        }

        if ( 'bottom' === $which ) {
            $html_current_page  = $current;
            $total_pages_before = '<span class="screen-reader-text">' . __( 'Current Page' ) . '</span><span id="table-paging" class="paging-input"><span class="tablenav-paging-text">';
        } else {
            $html_current_page = sprintf(
                "%s<input class='current-page' id='current-page-selector-backuplist' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' /><span class='tablenav-paging-text'>",
                '<label for="current-page-selector-backuplist" class="screen-reader-text">' . __( 'Current Page' ) . '</label>',
                $current,
                strlen( $total_pages )
            );
        }
        $html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
        $page_links[]     = $total_pages_before . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . $total_pages_after;

        if ( $disable_next ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='next-page button' value='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                $current,
                __( 'Next page' ),
                '&rsaquo;'
            );
        }

        if ( $disable_last ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='last-page button'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                __( 'Last page' ),
                '&raquo;'
            );
        }

        $pagination_links_class = 'pagination-links';
        if ( ! empty( $infinite_scroll ) ) {
            $pagination_links_class .= ' hide-if-js';
        }
        $output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';

        if ( $total_pages ) {
            $page_class = $total_pages < 2 ? ' one-page' : '';
        } else {
            $page_class = ' no-pages';
        }
        $this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

        echo $this->_pagination;
    }

    protected function display_tablenav( $which ) {
        $css_type = '';
        if ( 'top' === $which ) {
            wp_nonce_field( 'bulk-' . $this->_args['plural'] );
            $css_type = 'margin: 0 0 10px 0';
        }
        else if( 'bottom' === $which ) {
            $css_type = 'margin: 10px 0 0 0';
        }

        $total_pages     = $this->_pagination_args['total_pages'];
        if ( $total_pages >1)
        {
            ?>
            <div class="tablenav <?php echo esc_attr( $which ); ?>" style="<?php esc_attr_e($css_type); ?>">
                <div class="tablenav bottom">
                    <div class="alignleft actions bulkactions">
                        <label for="wpvivid_rollback_core_bulk_action_select" class="screen-reader-text">Select bulk action</label>
                        <select name="action2" id="wpvivid_rollback_core_bulk_action_select">
                            <option value="-1">Bulk actions</option>
                            <option value="delete">Delete permanently</option>
                        </select>
                        <input type="submit" id="wpvivid_rollback_core_bulk_action" class="button action" value="Apply">
                    </div>
                </div>
                <?php
                $this->extra_tablenav( $which );
                $this->pagination( $which );
                ?>

                <br class="clear" />
            </div>
            <?php
        }
        else
        {
            ?>
            <div class="tablenav bottom">
                <div class="alignleft actions bulkactions">
                    <label for="wpvivid_rollback_core_bulk_action_select" class="screen-reader-text">Select bulk action</label>
                    <select name="action2" id="wpvivid_rollback_core_bulk_action_select">
                        <option value="-1">Bulk actions</option>
                        <option value="delete">Delete permanently</option>
                    </select>
                    <input type="submit" id="wpvivid_rollback_core_bulk_action" class="button action" value="Apply">
                </div>
            </div>
            <?php
        }
    }
}

class WPvivid_Rollback_List extends WP_List_Table
{
    public $page_num;
    public $rollback_list;

    public function __construct( $args = array() )
    {
        parent::__construct(
            array(
                'plural' => 'wpvivid_rollback',
                'screen' => 'wpvivid_rollback'
            )
        );
    }

    protected function get_table_classes()
    {
        return array( 'widefat striped plugins' );
    }

    public function print_column_headers( $with_id = true )
    {
        list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

        if (!empty($columns['cb'])) {
            static $cb_counter = 1;
            $columns['cb'] = '<label class="screen-reader-text" for="cb-select-all-' . $cb_counter . '">' . __('Select All') . '</label>'
                . '<input id="cb-select-all-' . $cb_counter . '" type="checkbox"/>';
            $cb_counter++;
        }

        foreach ( $columns as $column_key => $column_display_name )
        {
            $class = array( 'manage-column', "column-$column_key" );

            if ( in_array( $column_key, $hidden ) )
            {
                $class[] = 'hidden';
            }

            if ( $column_key === $primary )
            {
                $class[] = 'column-primary';
            }

            if ( $column_key === 'cb' )
            {
                $class[] = 'wpvivid-check-column';
            }

            $tag   = ( 'cb' === $column_key ) ? 'td' : 'th';
            $scope = ( 'th' === $tag ) ? 'scope="col"' : '';
            $id    = $with_id ? "id='$column_key'" : '';

            if ( ! empty( $class ) )
            {
                $class = "class='" . join( ' ', $class ) . "'";
            }

            echo "<$tag $scope $id $class>$column_display_name</$tag>";
        }
    }

    public function get_columns()
    {
        $columns = array();
        $columns['cb'] = __( 'cb', 'wpvivid' );
        $columns['wpvivid_version'] = __( 'Version', 'wpvivid' );
        $columns['wpvivid_modified'] = __( 'Modified', 'wpvivid' );
        $columns['wpvivid_size'] =__( 'Size', 'wpvivid'  );
        $columns['wpvivid_action'] = __( 'Action', 'wpvivid' );
        return $columns;
    }

    public function column_cb( $rollback )
    {
        $html='<input type="checkbox"/>';
        echo $html;
    }

    public function _column_wpvivid_version( $rollback )
    {
        ?>
        <td class="plugin-title column-primary"><strong><?php echo $rollback['version']?></strong></td>
        <?php
    }

    public function _column_wpvivid_modified( $rollback )
    {
        ?>
        <td class="column-description desc"><?php echo $rollback['date']?></td>
        <?php
    }

    public function _column_wpvivid_size( $rollback )
    {
        ?>
        <td class="column-description desc"><?php echo $rollback['size']?></td>
        <?php
    }

    public function _column_wpvivid_action( $rollback )
    {
        ?>
        <td class="column-description desc">
            <span class="dashicons dashicons-download wpvivid-dashicons-blue wpvivid-rollback-download" title="Download" style="cursor:pointer;"></span>
            <span class="dashicons dashicons-update-alt wpvivid-dashicons-blue wpvivid-rollback-version" style="cursor:pointer;" title="Rollback"></span>
        </td>
        <?php
    }

    public function set_list($rollback_list,$page_num=1)
    {
        $this->rollback_list=$rollback_list;
        $this->page_num=$page_num;
    }

    public function get_pagenum()
    {
        if($this->page_num=='first')
        {
            $this->page_num=1;
        }
        else if($this->page_num=='last')
        {
            $this->page_num=$this->_pagination_args['total_pages'];
        }
        $pagenum = $this->page_num ? $this->page_num : 0;

        if ( isset( $this->_pagination_args['total_pages'] ) && $pagenum > $this->_pagination_args['total_pages'] )
        {
            $pagenum = $this->_pagination_args['total_pages'];
        }

        return max( 1, $pagenum );
    }

    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array();
        $this->_column_headers = array($columns, $hidden, $sortable);

        if(empty($this->plugins_list))
        {
            $total_items=0;
        }
        else
        {
            $total_items =sizeof($this->plugins_list);
        }

        $this->set_pagination_args(
            array(
                'total_items' => $total_items,
                'per_page'    => 30,
            )
        );
    }

    public function has_items()
    {
        return !empty($this->rollback_list);
    }

    public function display_rows()
    {
        $this->_display_rows($this->rollback_list);
    }

    private function _display_rows($rollback_list)
    {
        $page=$this->get_pagenum();

        $page_rollback_list=array();
        $temp_page_rollback_list=array();

        if(empty($rollback_list))
        {
            return;
        }

        foreach ( $rollback_list as $key=>$rollback)
        {
            $page_rollback_list[$key]=$rollback;
        }

        $count=0;
        while ( $count<$page )
        {
            $temp_page_rollback_list = array_splice( $page_rollback_list, 0, 30);
            $count++;
        }

        foreach ( $temp_page_rollback_list as $key=>$rollback)
        {
            $this->single_row($rollback);
        }
    }

    public function single_row($rollback)
    {
        $row_style = 'display: table-row;';
        $class='';
        ?>
        <tr style="<?php echo $row_style?>" class='wpvivid-backup-row <?php echo $class?>' data-slug="<?php echo $rollback['slug'];?>" data-version="<?php echo $rollback['version'];?>">
            <?php $this->single_row_columns( $rollback ); ?>
        </tr>
        <?php
    }

    protected function pagination( $which )
    {
        if ( empty( $this->_pagination_args ) )
        {
            return;
        }

        $total_items     = $this->_pagination_args['total_items'];
        $total_pages     = $this->_pagination_args['total_pages'];
        $infinite_scroll = false;
        if ( isset( $this->_pagination_args['infinite_scroll'] ) )
        {
            $infinite_scroll = $this->_pagination_args['infinite_scroll'];
        }

        if ( 'top' === $which && $total_pages > 1 )
        {
            $this->screen->render_screen_reader_content( 'heading_pagination' );
        }

        $output = '<span class="displaying-num">' . sprintf( _n( '%s item', '%s items', $total_items ), number_format_i18n( $total_items ) ) . '</span>';

        $current              = $this->get_pagenum();

        $page_links = array();

        $total_pages_before = '<span class="paging-input">';
        $total_pages_after  = '</span></span>';

        $disable_first = $disable_last = $disable_prev = $disable_next = false;

        if ( $current == 1 ) {
            $disable_first = true;
            $disable_prev  = true;
        }
        if ( $current == 2 ) {
            $disable_first = true;
        }
        if ( $current == $total_pages ) {
            $disable_last = true;
            $disable_next = true;
        }
        if ( $current == $total_pages - 1 ) {
            $disable_last = true;
        }

        if ( $disable_first ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='first-page button'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                __( 'First page' ),
                '&laquo;'
            );
        }

        if ( $disable_prev ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='prev-page button' value='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                $current,
                __( 'Previous page' ),
                '&lsaquo;'
            );
        }

        if ( 'bottom' === $which ) {
            $html_current_page  = $current;
            $total_pages_before = '<span class="screen-reader-text">' . __( 'Current Page' ) . '</span><span id="table-paging" class="paging-input"><span class="tablenav-paging-text">';
        } else {
            $html_current_page = sprintf(
                "%s<input class='current-page' id='current-page-selector-backuplist' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' /><span class='tablenav-paging-text'>",
                '<label for="current-page-selector-backuplist" class="screen-reader-text">' . __( 'Current Page' ) . '</label>',
                $current,
                strlen( $total_pages )
            );
        }
        $html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
        $page_links[]     = $total_pages_before . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . $total_pages_after;

        if ( $disable_next ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='next-page button' value='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                $current,
                __( 'Next page' ),
                '&rsaquo;'
            );
        }

        if ( $disable_last ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='last-page button'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                __( 'Last page' ),
                '&raquo;'
            );
        }

        $pagination_links_class = 'pagination-links';
        if ( ! empty( $infinite_scroll ) ) {
            $pagination_links_class .= ' hide-if-js';
        }
        $output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';

        if ( $total_pages ) {
            $page_class = $total_pages < 2 ? ' one-page' : '';
        } else {
            $page_class = ' no-pages';
        }
        $this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

        echo $this->_pagination;
    }

    protected function display_tablenav( $which ) {
        $css_type = '';
        if ( 'top' === $which ) {
            wp_nonce_field( 'bulk-' . $this->_args['plural'] );
            $css_type = 'margin: 0 0 10px 0';
        }
        else if( 'bottom' === $which ) {
            $css_type = 'margin: 10px 0 0 0';
        }

        $total_pages     = $this->_pagination_args['total_pages'];
        if ( $total_pages >1)
        {
            ?>
            <div class="tablenav <?php echo esc_attr( $which ); ?>" style="<?php esc_attr_e($css_type); ?>">
                <div class="tablenav bottom">
                    <div class="alignleft actions bulkactions">
                        <label for="wpvivid_rollback_bulk_action_select" class="screen-reader-text">Select bulk action</label>
                        <select name="action2" id="wpvivid_rollback_bulk_action_select">
                            <option value="-1">Bulk actions</option>
                            <option value="delete">Delete permanently</option>
                        </select>
                        <input type="submit" id="wpvivid_rollback_bulk_action" class="button action" value="Apply">
                    </div>
                </div>
                <?php
                $this->extra_tablenav( $which );
                $this->pagination( $which );
                ?>

                <br class="clear" />
            </div>
            <?php
        }
        else
        {
            ?>
            <div class="tablenav bottom">
                <div class="alignleft actions bulkactions">
                    <label for="wpvivid_rollback_bulk_action_select" class="screen-reader-text">Select bulk action</label>
                    <select name="action2" id="wpvivid_rollback_bulk_action_select">
                        <option value="-1">Bulk actions</option>
                        <option value="delete">Delete permanently</option>
                    </select>
                    <input type="submit" id="wpvivid_rollback_bulk_action" class="button action" value="Apply">
                </div>
            </div>
            <?php
        }
    }
}

function wpvivid_pro_function_pre_core_extract_callback($p_event, &$p_header)
{
    $plugins = substr(WP_PLUGIN_DIR, strpos(WP_PLUGIN_DIR, 'wp-content/'));

    $path = str_replace('\\','/',WP_CONTENT_DIR);
    $content_path = $path.'/';
    if(strpos($p_header['filename'], $content_path.'advanced-cache.php')!==false)
    {
        return 0;
    }

    if(strpos($p_header['filename'], $content_path.'db.php')!==false)
    {
        return 0;
    }

    if(strpos($p_header['filename'], $content_path.'object-cache.php')!==false)
    {
        return 0;
    }

    if(strpos($p_header['filename'],$plugins.'/wpvivid-backuprestore')!==false)
    {
        return 0;
    }

    if(strpos($p_header['filename'],'wp-config.php')!==false)
    {
        return 0;
    }

    if(strpos($p_header['filename'],'wpvivid_package_info.json')!==false)
    {
        return 0;
    }

    if(strpos($p_header['filename'],'.htaccess')!==false)
    {
        return 0;
    }

    if(strpos($p_header['filename'],'.user.ini')!==false)
    {
        return 0;
    }

    if(strpos($p_header['filename'],'wordfence-waf.php')!==false)
    {
        return 0;
    }

    if(strpos($p_header['filename'], $content_path.'mu-plugins/endurance-browser-cache.php')!==false)
    {
        return 0;
    }

    if(strpos($p_header['filename'], $content_path.'mu-plugins/endurance-page-cache.php')!==false)
    {
        return 0;
    }

    if(strpos($p_header['filename'], $content_path.'mu-plugins/endurance-php-edge.php')!==false)
    {
        return 0;
    }

    return 1;
}