<?php

class WP_Shifty_Resource {

      public static $hash = array(
            'core' => array(
                  'css' => array('d9cfa56d266e64b5c7ed09005ffd5a79'),
                  'js'  => array('afdf9ac12d15236f2dbb8df0008ab7d1')
            )
      );

      public static function measure_relation($type, $source, $resource = null){

            // Deal with inline resources
            if (strpos($source, '<') === 0){
                  return self::measure_inline_relation($type, $source, $resource);
            }

            $scheme           = parse_url(home_url(), PHP_URL_SCHEME);
            $admin_path       = parse_url(admin_url(), PHP_URL_PATH);
            $template         = parse_url(get_template_directory_uri(), PHP_URL_PATH);
            $stylesheet       = parse_url(get_stylesheet_directory_uri(), PHP_URL_PATH);
            $relative_path    = str_replace(untrailingslashit(home_url()), '', preg_replace('~^(https?:)?//~', $scheme . '://',$source));

            if (preg_match('~^data~', $source)){
                  return array('file', 'data');
            }

            /* Is it a core file? */
            if (strpos($relative_path, '/wp-includes/') !== false || strpos($relative_path, $admin_path) !== false) {
                  return array('file', 'core');
            }

            /* Is it theme related? */
            if (strpos($relative_path, '/wp-content/themes') !== false) {
                  if (strpos($relative_path, $template) !== false){
                        return array('file', 'theme', get_template());
                  }
                  else if (strpos($relative_path, $stylesheet) !== false){
                        return array('file', 'theme', get_stylesheet());
                  }
            }

            /* Is it plugin? */
            foreach (WP_Shifty_Preview::$plugins as $plugin){
                  if(strpos($relative_path, $plugin['dir']) !== false){
                        return array('file', 'plugin', $plugin['slug'], $plugin['name']);
                  }
            }

            /* Known third party */

            // Elementor uploads
            if (strpos($relative_path, '/wp-content/uploads/elementor') !== false && isset(WP_Shifty_Preview::$plugins['elementor'])) {
                  return array('file', 'plugin', 'elementor', WP_Shifty_Preview::$plugins['elementor']['name']);
            }

            // Google fonts
            if (strpos($relative_path, 'fonts.googleapis.com') !== false) {
                  return array('file', 'third_party', 'google_fonts', __('Gooogle Fonts', 'wp-shifty'), WP_SHIFTY_URI . 'images/google-fonts.svg');
            }

            /* Unknown third party */
            return array('file', 'unknown');
      }

      public static function measure_inline_relation($type, $source, $resource){
            $hash = self::get_hash($source);

            if (isset($resource->id)){
                  $id = preg_replace('~-(inline-(css|js|style|script)|(css|js)-(before|after|extra|translations))$~', '', $resource->id);
                  $registered_resources = ($type == 'css' ? wp_styles()->registered : wp_scripts()->registered);

                  if (isset($registered_resources[$id]) && isset($registered_resources[$id]->src) && !empty($registered_resources[$id]->src)){
                        $parent     = self::measure_relation($type, $registered_resources[$id]->src);
                        $parent[0]  = 'inline';
                        return $parent;
                  }

                  foreach (WP_Shifty_Preview::$plugins as $plugin){
                        if (strpos($id, $plugin['slug']) === 0){
                              return array('inline', 'plugin', $plugin['slug'], $plugin['name']);
                        }
                  }
            }


            // Known core files
            if (in_array($hash, self::$hash['core'][$type])){
                  return array('inline','core');
            }

            // Other known inline resources
            if (preg_match('~connect.facebook.net/([a-zA-Z_]+)/sdk.js~', $source)){
                  return array('inline', 'third_party', 'facebook', __('Facebook', 'wp-shifty'), WP_SHIFTY_URI . 'images/facebook.svg');
            }

            return array('inline', 'unknown');
      }

      public static function get_hash($resource){
            return md5(preg_replace('~\s~', '', $resource));
      }

      public static function get_id($resource){
            if (strpos($resource, '<') === 0 || strpos($resource, '^data') === 0){
                  return self::get_hash($resource);
            }
            else {
                  return trim($resource);
            }
      }
}