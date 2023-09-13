<?php
if (!defined('ABSPATH')){
      die('Keep calm and carry on!');
}
global $title, $hook_suffix, $current_screen, $wp_locale, $pagenow, $wp_version,
		$update_title, $total_update_count, $parent_file;
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
      <head>
      	<meta name="viewport" content="width=device-width" />
      	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
      	<title><?php echo esc_html__('WP Shifty', 'wp-shifty');?></title>
      	<script type="text/javascript">
      	addLoadEvent = function(func){if(typeof jQuery!="undefined")jQuery(document).ready(func);else if(typeof wpOnload!='function'){wpOnload=func;}else{var oldonload=wpOnload;wpOnload=function(){oldonload();func();}}};
      	var ajaxurl = '<?php echo admin_url( 'admin-ajax.php', 'relative' ); ?>',
      		pagenow = '',
      		typenow = '',
      		adminpage = '',
      		thousandsSeparator = '<?php echo addslashes( $wp_locale->number_format['thousands_sep'] ); ?>',
      		decimalPoint = '<?php echo addslashes( $wp_locale->number_format['decimal_point'] ); ?>',
      		isRtl = <?php echo (int) is_rtl(); ?>;

                  var wp_shifty = <?php echo json_encode(array(
                        'i18n' => array(WP_Shifty_I18n::localize_script()),
                        'plugin_url' => WP_SHIFTY_URI,
                        'origin' => parse_url(home_url(), PHP_URL_SCHEME) . '://' . parse_url(home_url(), PHP_URL_HOST) . (!empty(parse_url(home_url(), PHP_URL_PORT)) ? ':' . parse_url(home_url(), PHP_URL_PORT) : ''),
                        'api_url' => WP_SHIFTY_API_URL,
                        'editor_id' => (int)$wpshifty_editor->id,
                        'resource_max_length' => WP_SHIFTY_RESOURCE_MAX_LENGTH,
                        'is_compressed' => $compression,
                        'autocomplete' => array(),
                        'lighthouse' => array('render-blocking-resources' => array(), 'unused' => array()),
                        'file_sizes' => array(),
                        'cm' => NULL,
                        'nonce' => wp_create_nonce('wpshifty-ajax-nonce')
                  ));?>
      	</script>
      	<?php do_action( 'admin_print_styles' ); ?>
      	<?php do_action( 'admin_print_scripts' );?>
      	<?php do_action( 'admin_head' ); ?>
      </head>
      <body class="wp-core-ui wpshifty-editor-wrapper wpshifty-is-loading" data-wpshifty-tab="wpshifty-what-live">
            <form id="wpshifty-editor-form" method="post" data-status="<?php echo esc_attr($wpshifty_editor->status)?>">
                  <div class="wpshifty-editor-header">
                        <a class="wpshifty-editor-header-exit wpshifty-header-element" href="<?php echo esc_url(add_query_arg('tab','scenario',WP_SHIFTY_ADMIN_URL));?>">
                              <svg width="36px" height="36px" xmlns="http://www.w3.org/2000/svg" viewBox="-2 -2 24 24" role="img" aria-hidden="true" focusable="false"><path d="M20 10c0-5.51-4.49-10-10-10C4.48 0 0 4.49 0 10c0 5.52 4.48 10 10 10 5.51 0 10-4.48 10-10zM7.78 15.37L4.37 6.22c.55-.02 1.17-.08 1.17-.08.5-.06.44-1.13-.06-1.11 0 0-1.45.11-2.37.11-.18 0-.37 0-.58-.01C4.12 2.69 6.87 1.11 10 1.11c2.33 0 4.45.87 6.05 2.34-.68-.11-1.65.39-1.65 1.58 0 .74.45 1.36.9 2.1.35.61.55 1.36.55 2.46 0 1.49-1.4 5-1.4 5l-3.03-8.37c.54-.02.82-.17.82-.17.5-.05.44-1.25-.06-1.22 0 0-1.44.12-2.38.12-.87 0-2.33-.12-2.33-.12-.5-.03-.56 1.2-.06 1.22l.92.08 1.26 3.41zM17.41 10c.24-.64.74-1.87.43-4.25.7 1.29 1.05 2.71 1.05 4.25 0 3.29-1.73 6.24-4.4 7.78.97-2.59 1.94-5.2 2.92-7.78zM6.1 18.09C3.12 16.65 1.11 13.53 1.11 10c0-1.3.23-2.48.72-3.59C3.25 10.3 4.67 14.2 6.1 18.09zm4.03-6.63l2.58 6.98c-.86.29-1.76.45-2.71.45-.79 0-1.57-.11-2.29-.33.81-2.38 1.62-4.74 2.42-7.1z"></path></svg>
                        </a>
                        <div class="wpshifty-editor-header-where wpshifty-header-element">
                              <a href="#" id="wpshifty-add-condition">
                                    <svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" role="img" aria-hidden="true" focusable="false"><path d="M18 11.2h-5.2V6h-1.6v5.2H6v1.6h5.2V18h1.6v-5.2H18z"></path></svg>
                              </a>
                              <div id="wpshifty-editor-summary"></div>
                        </div>
                        <div class="wpshifty-editor-actions wpshifty-header-element">
                              <div class="wpshifty-row">
                              <div class="wpshifty-editor-actions-draft">
                                    <a href="#" class="wpshifty-btn wpshifty-btn-success wpshifty-editor-save" data-editor-status="active"><?php esc_html_e('Save & Activate', 'wp-shifty')?></a>
                                    <a href="#" class="wpshifty-btn wpshifty-editor-save" data-editor-status="draft"><?php esc_html_e('Save as draft', 'wp-shifty')?></a>
                              </div>
                              <div class="wpshifty-editor-actions-active">
                                    <a href="#" class="wpshifty-btn wpshifty-btn-success wpshifty-editor-save" data-editor-status="active"><?php esc_html_e('Save changes', 'wp-shifty')?></a>
                                    <a href="#" class="wpshifty-btn wpshifty-editor-save" data-editor-status="draft"><?php esc_html_e('Switch to draft', 'wp-shifty')?></a>
                              </div>
                              </div>
                        </div>
                  </div>
                  <div class="wpshifty-editor-body">
                        <div id="wpshifty-preview-wrapper">
                              <div id="wpshifty-where" class="wpshifty-hidden">
                                    <ul id="wpshifty-condition-type-selector">
                                          <li><a href="#add-condition-page">
                                                <div class="wpshifty-condition-icon"><img src="<?php echo WP_SHIFTY_URI;?>/images/icons/page.png"></div>
                                                <?php esc_html_e('Page', 'wp-shifty');?></a>
                                          </li>
                                          <li><a href="#add-condition-post-type">
                                                <div class="wpshifty-condition-icon"><img src="<?php echo WP_SHIFTY_URI;?>/images/icons/post-type.png"></div>
                                                <?php esc_html_e('Post Type', 'wp-shifty');?></a>
                                          </li>
                                          <li><a href="#add-condition-archive">
                                                <div class="wpshifty-condition-icon"><img src="<?php echo WP_SHIFTY_URI;?>/images/icons/archive.png"></div>
                                                <?php esc_html_e('Archive', 'wp-shifty');?></a>
                                          </li>
                                          <li><a href="#add-condition-author">
                                                <div class="wpshifty-condition-icon"><img src="<?php echo WP_SHIFTY_URI;?>/images/icons/author.png"></div>
                                                <?php esc_html_e('Author', 'wp-shifty');?></a>
                                          </li>
                                          <li><a href="#add-condition-search">
                                                <div class="wpshifty-condition-icon"><img src="<?php echo WP_SHIFTY_URI;?>/images/icons/search.png"></div>
                                                <?php esc_html_e('Search', 'wp-shifty');?></a>
                                          </li>
                                          <li><a href="#add-condition-url">
                                                <div class="wpshifty-condition-icon"><img src="<?php echo WP_SHIFTY_URI;?>/images/icons/url.png"></div>
                                                <?php esc_html_e('URL', 'wp-shifty');?></a>
                                          </li>
                                          <li><a href="#add-condition-admin">
                                                <div class="wpshifty-condition-icon"><img src="<?php echo WP_SHIFTY_URI;?>/images/icons/admin-pages.png"></div>
                                                <?php esc_html_e('Admin pages', 'wp-shifty');?></a>
                                          </li>
                                          <li><a href="#add-condition-ajax">
                                                <div class="wpshifty-condition-icon"><img src="<?php echo WP_SHIFTY_URI;?>/images/icons/ajax.png"></div>
                                                <?php esc_html_e('AJAX', 'wp-shifty');?></a>
                                          </li>
                                          <?php if (class_exists('WooCommerce')):?>
                                          <li><a href="#add-condition-shop">
                                                <div class="wpshifty-condition-icon"><img src="<?php echo WP_SHIFTY_URI;?>/images/icons/shop.png"></div>
                                                <?php esc_html_e('Shop Pages', 'wp-shifty');?></a>
                                          </li>
                                          <?php endif;?>
                                          <li><a href="#add-condition-frontpage">
                                                <div class="wpshifty-condition-icon"><img src="<?php echo WP_SHIFTY_URI;?>/images/icons/frontpage.png"></div>
                                                <?php esc_html_e('Frontpage', 'wp-shifty');?></a>
                                          </li>
                                          <li><a href="#add-condition-frontend">
                                                <div class="wpshifty-condition-icon"><img src="<?php echo WP_SHIFTY_URI;?>/images/icons/frontend.png"></div>
                                                <?php esc_html_e('Frontend', 'wp-shifty');?></a>
                                          </li>
                                          <li><a href="#add-condition-everywhere">
                                                <div class="wpshifty-condition-icon"><img src="<?php echo WP_SHIFTY_URI;?>/images/icons/everywhere.png"></div>
                                                <?php esc_html_e('Everywhere', 'wp-shifty');?></a>
                                          </li>
                                          <li><a href="#add-condition-query">
                                                <div class="wpshifty-condition-icon"><img src="<?php echo WP_SHIFTY_URI;?>/images/icons/query-string.png"></div>
                                                <?php esc_html_e('Query String', 'wp-shifty');?></a>
                                          </li>
                                          <li><a href="#add-condition-user">
                                                <div class="wpshifty-condition-icon"><img src="<?php echo WP_SHIFTY_URI;?>/images/icons/user.png"></div>
                                                <?php esc_html_e('User', 'wp-shifty');?></a>
                                          </li>
                                          <li><a href="#add-condition-post-data">
                                                <div class="wpshifty-condition-icon"><img src="<?php echo WP_SHIFTY_URI;?>/images/icons/post-data.png"></div>
                                                <?php esc_html_e('POST data', 'wp-shifty');?></a>
                                          </li>
                                          <li><a href="#add-condition-header">
                                                <div class="wpshifty-condition-icon"><img src="<?php echo WP_SHIFTY_URI;?>/images/icons/http-header.png"></div>
                                                <?php esc_html_e('HTTP header', 'wp-shifty');?></a>
                                          </li>
                                          <li><a href="#add-condition-cookie">
                                                <div class="wpshifty-condition-icon"><img src="<?php echo WP_SHIFTY_URI;?>/images/icons/cookie.png"></div>
                                                <?php esc_html_e('Cookie', 'wp-shifty');?></a>
                                          </li>
                                          <li><a href="#add-condition-useragent">
                                                <div class="wpshifty-condition-icon"><img src="<?php echo WP_SHIFTY_URI;?>/images/icons/useragent.png"></div>
                                                <?php esc_html_e('User agent', 'wp-shifty');?></a>
                                          </li>
                                          <li><a href="#add-condition-device">
                                                <div class="wpshifty-condition-icon"><img src="<?php echo WP_SHIFTY_URI;?>/images/icons/device.png"></div>
                                                <?php esc_html_e('Device', 'wp-shifty');?></a>
                                          </li>
                                          <li><a href="#add-condition-cronjob">
                                                <div class="wpshifty-condition-icon"><img src="<?php echo WP_SHIFTY_URI;?>/images/icons/cronjob.png"></div>
                                                <?php esc_html_e('Cronjob', 'wp-shifty');?></a>
                                          </li>
                                          <li><a href="#add-condition-cli">
                                                <div class="wpshifty-condition-icon"><img src="<?php echo WP_SHIFTY_URI;?>/images/icons/cli.png"></div>
                                                <?php esc_html_e('CLI', 'wp-shifty');?></a>
                                          </li>
                                    </ul>
                                    <div id="wpshifty-editor-rule-editors">
                                          <?php if (isset($wpshifty_editor->settings->conditions)):?>
                                                <?php foreach ((array)$wpshifty_editor->settings->conditions as $cid => $condition):?>
                                                      <?php WP_Shifty_Editor::get_condition_editor($condition, $cid);?>
                                                <?php endforeach;?>
                                          <?php endif;?>
                                    </div>
                              </div>
                              <div id="wpshifty-preview-inner">
                                    <div class="wpshifty-preview-notice-wrapper">
                                          <div id="wpshifty-should-refresh" class="wpshifty-preview-notice">
                                                <?php echo sprintf(esc_html__('Rules were updated, you should %srefresh preview%s to see results', 'wp-shifty'), '<a href="#" class="wpshifty-reload">', '</a>');?><br>
                                                <a href="#" class="wpshifty-preview-notice-close">&times;</a>
                                          </div>
                                          <div id="wpshifty-was-redirected" class="wpshifty-hidden wpshifty-preview-notice">
                                                <?php esc_html_e('The page has been redirected', 'wp-shifty');?><br>
                                                <a href="#" class="wpshifty-preview-notice-close">&times;</a>
                                          </div>
                                          <div id="wpshifty-url-not-match" class="wpshifty-hidden wpshifty-preview-notice">
                                                <?php esc_html_e('Conditions are not matching with the preview.', 'wp-shifty');?>
                                                <a href="#" id="wpshifty-backup-preview-url"><?php echo esc_html_e('Click here auto-configure preview.', 'wp-shifty');?></a>
                                          </div>
                                    </div>
                                    <ul class="wpshifty-preview-header">
                                          <li class="wpshifty-preview-url-wrapper">
                                                <div id="wpshifty-preview-autocomplete-set" class="wpshifty-autocomplete-set">
                                                      <a href="#" class="wpshifty-autocomplete-toggle">
                                                            <svg aria-hidden="true" focusable="false" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M80 368H16a16 16 0 0 0-16 16v64a16 16 0 0 0 16 16h64a16 16 0 0 0 16-16v-64a16 16 0 0 0-16-16zm0-320H16A16 16 0 0 0 0 64v64a16 16 0 0 0 16 16h64a16 16 0 0 0 16-16V64a16 16 0 0 0-16-16zm0 160H16a16 16 0 0 0-16 16v64a16 16 0 0 0 16 16h64a16 16 0 0 0 16-16v-64a16 16 0 0 0-16-16zm416 176H176a16 16 0 0 0-16 16v32a16 16 0 0 0 16 16h320a16 16 0 0 0 16-16v-32a16 16 0 0 0-16-16zm0-320H176a16 16 0 0 0-16 16v32a16 16 0 0 0 16 16h320a16 16 0 0 0 16-16V80a16 16 0 0 0-16-16zm0 160H176a16 16 0 0 0-16 16v32a16 16 0 0 0 16 16h320a16 16 0 0 0 16-16v-32a16 16 0 0 0-16-16z"></path></svg>
                                                      </a>
                                                      <input id="wpshifty-preview-url" class="wpshifty-preview-url" type="url" name="current_url" value="">
                                                      <div id="wpshifty-preview-autocomplete-wrapper" class="wpshifty-autocomplete-wrapper wpshifty-hidden">
                                                            <input type="text" class="wpshifty-autocomplete-search" placeholder="<?php esc_html_e('Search', 'wp-shifty');?>">
                                                            <ul class="wpshifty-autocomplete-list"></ul>
                                                      </div>
                                                </div>
                                                <a href="#" id="wpshifty-reload" class="wpshifty-reload">
                                                      <svg aria-hidden="true" focusable="false" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M500.33 0h-47.41a12 12 0 0 0-12 12.57l4 82.76A247.42 247.42 0 0 0 256 8C119.34 8 7.9 119.53 8 256.19 8.1 393.07 119.1 504 256 504a247.1 247.1 0 0 0 166.18-63.91 12 12 0 0 0 .48-17.43l-34-34a12 12 0 0 0-16.38-.55A176 176 0 1 1 402.1 157.8l-101.53-4.87a12 12 0 0 0-12.57 12v47.41a12 12 0 0 0 12 12h200.33a12 12 0 0 0 12-12V12a12 12 0 0 0-12-12z"></path></svg>
                                                </a>
                                          </li>
                                          <li>
                                          <li id="wpshifty-viewport-settings">
                                                <select id="wpshifty-viewport-quickselect" class="wpshifty-colorful-select wpshifty-autoreload" name="browser[device]">
                                                      <optgroup label="<?php esc_html_e('Desktop','wp-shifty');?>">
                                                            <option value="macbook18" data-width="2560" data-height="1600" data-ua="Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/93.0.4577.63 Safari/537.36"<?php WP_Shifty_Editor::maybe_selected($wpshifty_editor->preview->browser, 'device', 'desktop');?>><?php esc_html_e('Wide Screen', 'wp-shifty');?></option>
                                                            <option value="desktop" data-width="1920" data-height="1080" data-ua="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.159 Safari/537.36"<?php WP_Shifty_Editor::maybe_selected($wpshifty_editor->preview->browser, 'device', 'desktop');?>><?php esc_html_e('Desktop', 'wp-shifty');?></option>
                                                            <option value="laptop" data-width="1440" data-height="900" data-ua="Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/93.0.4577.63 Safari/537.36"<?php WP_Shifty_Editor::maybe_selected($wpshifty_editor->preview->browser, 'device', 'laptop');?>><?php esc_html_e('Laptop', 'wp-shifty');?></option>
                                                            <option value="notebook" data-width="1280" data-height="800" data-ua="Mozilla/5.0 (Linux; Android 9; Google Chromebook Pixel (2015)) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.35 Safari/537.36"<?php WP_Shifty_Editor::maybe_selected($wpshifty_editor->preview->browser, 'device', 'notebook');?>><?php esc_html_e('Notebook', 'wp-shifty');?></option>
                                                      </optgroup>
                                                      <optgroup label="<?php esc_html_e('Tablet','wp-shifty');?>">
                                                            <option value="ipad102" data-width="1620" data-height="2160" data-ua="Mozilla/5.0 (iPad; CPU OS 15_2_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.2 Mobile/15E148 Safari/604.1"<?php WP_Shifty_Editor::maybe_selected($wpshifty_editor->preview->browser, 'device', 'ipad102');?>><?php esc_html_e('iPad 10.2', 'wp-shifty');?></option>
                                                            <option value="lenovotabp11pro" data-width="1600" data-height="2560" data-ua=" Mozilla/5.0 (Linux; Android 10; Lenovo TB-J706F) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.101 Safari/537.36"<?php WP_Shifty_Editor::maybe_selected($wpshifty_editor->preview->browser, 'device', 'lenovotabp11pro');?>><?php esc_html_e('Lenovo Tab P11 Pro', 'wp-shifty');?></option>
                                                            <option value="htcnexus9" data-width="1536" data-height="2048" data-ua="Mozilla/5.0 (Linux; Android 7.1.1; Nexus 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.110 Safari/537.36"<?php WP_Shifty_Editor::maybe_selected($wpshifty_editor->preview->browser, 'device', 'htcnexus9');?>><?php esc_html_e('HTC Nexus 9', 'wp-shifty');?></option>
                                                            <option value="galaxys7plus" data-width="1440" data-height="2560" data-ua="Mozilla/5.0 (Linux; Android 11; SM-T970) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.152 Safari/537.36"<?php WP_Shifty_Editor::maybe_selected($wpshifty_editor->preview->browser, 'device', 'galaxys7plus');?>><?php esc_html_e('Samsung Galaxy tab S7 Plus', 'wp-shifty');?></option>
                                                            <option value="amazonfire10plus" data-width="1200" data-height="1920" data-ua="Mozilla/5.0 (Linux; Android 9; KFMAWI) AppleWebKit/537.36 (KHTML, like Gecko) Silk/92.2.11 like Chrome/92.0.4515.159 Safari/537.36"<?php WP_Shifty_Editor::maybe_selected($wpshifty_editor->preview->browser, 'device', 'amazonfire10plus');?>><?php esc_html_e('Amazon Fire HD 10 Plus', 'wp-shifty');?></option>
                                                            <option value="ipadmini" data-width="768" data-height="1024" data-ua="Mozilla/5.0 (iPad; CPU OS 15_2_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.2 Mobile/15E148 Safari/604.1"<?php WP_Shifty_Editor::maybe_selected($wpshifty_editor->preview->browser, 'device', 'ipadmini');?>><?php esc_html_e('iPad Mini', 'wp-shifty');?></option>

                                                      </optgroup>
                                                      <optgroup label="<?php esc_html_e('Phone','wp-shifty');?>">
                                                            <option value="galaxys21" data-width="384" data-height="854" data-ua="Mozilla/5.0 (Linux; Android 12; SM-G998B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/95.0.4638.74 Mobile Safari/537.36"<?php WP_Shifty_Editor::maybe_selected($wpshifty_editor->preview->browser, 'device', 'galaxys21');?>><?php esc_html_e('Samsung Galaxy S21', 'wp-shifty');?></option>
                                                            <option value="xiaomi9t" data-width="360" data-height="740" data-ua="Mozilla/5.0 (Linux; Android 6.0.1; RedMi Note 5 Build/RB3N5C; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/68.0.3440.91 Mobile Safari/537.36"<?php WP_Shifty_Editor::maybe_selected($wpshifty_editor->preview->browser, 'device', 'xiaomi9t');?>><?php esc_html_e('Xiaomi Redmi 9T', 'wp-shifty');?></option>
                                                            <option value="iphone12" data-width="390" data-height="844" data-ua="Mozilla/5.0 (iPhone; CPU iPhone OS 12_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Mobile/15E148 Safari/604.1"<?php WP_Shifty_Editor::maybe_selected($wpshifty_editor->preview->browser, 'device', 'iphone12');?>><?php esc_html_e('iPhone 12', 'wp-shifty');?></option>
                                                            <option value="iphonex" data-width="375" data-height="812" data-ua="Mozilla/5.0 (iPhone; CPU iPhone OS 12_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Mobile/15E148 Safari/604.1"<?php WP_Shifty_Editor::maybe_selected($wpshifty_editor->preview->browser, 'device', 'iphonex');?>><?php esc_html_e('iPhone X', 'wp-shifty');?></option>
                                                            <option value="iphone8" data-width="375" data-height="667" data-ua="Mozilla/5.0 (iPhone; CPU iPhone OS 12_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Mobile/15E148 Safari/604.1"<?php WP_Shifty_Editor::maybe_selected($wpshifty_editor->preview->browser, 'device', 'iphone8');?>><?php esc_html_e('iPhone 8', 'wp-shifty');?></option>
                                                            <option value="galaxys9" data-width="360" data-height="740" data-ua="Mozilla/5.0 (Linux; Android 6.0.1; RedMi Note 5 Build/RB3N5C; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/68.0.3440.91 Mobile Safari/537.36"<?php WP_Shifty_Editor::maybe_selected($wpshifty_editor->preview->browser, 'device', 'galaxys9');?>><?php esc_html_e('Samsung Galaxy S9', 'wp-shifty');?></option>
                                                      </optgroup>
                                                </select>
                                                <input type="number" class="wpshifty-preview-size" id="wpshifty-preview-width" value="1366">
                                                x
                                                <input type="number" class="wpshifty-preview-size" id="wpshifty-preview-height" value="768">
                                          </li>
                                          <li>
                                                <a href="#" id="wpshifty-new-tab">
                                                      <svg aria-hidden="true" focusable="false" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M432,320H400a16,16,0,0,0-16,16V448H64V128H208a16,16,0,0,0,16-16V80a16,16,0,0,0-16-16H48A48,48,0,0,0,0,112V464a48,48,0,0,0,48,48H400a48,48,0,0,0,48-48V336A16,16,0,0,0,432,320ZM488,0h-128c-21.37,0-32.05,25.91-17,41l35.73,35.73L135,320.37a24,24,0,0,0,0,34L157.67,377a24,24,0,0,0,34,0L435.28,133.32,471,169c15,15,41,4.5,41-17V24A24,24,0,0,0,488,0Z"></path></svg>
                                                </a>
                                          </li>
                                          <li>
                                                <a href="#" class="wpshifty-btn wpshifty-btn-s wpshifty-btn-brand wpshifty-preview-settings-toggle">
                                                      <?php esc_html_e('Advanced', 'wp-shifty')?>
                                                </a>
                                          </li>
                                    </ul>
                                    <ul id="wpshifty-preview-settings" class="wpshifty-invisible">
                                          <li class="wpshifty-preview-user_role">
                                                <label><?php esc_html_e('User role', 'wp-shifty');?></label>
                                                <select name="browser[user_role]" class="wpshifty-colorful-select">
                                                      <option value="not-logged-in"><?php esc_html_e('User not logged in', 'wp-shifty');?></option>
                                                      <?php foreach (WP_Shifty_Editor::get_user_roles() as $key => $role):?>
                                                            <option value="<?php echo esc_attr($key);?>"<?php WP_Shifty_Editor::maybe_selected($wpshifty_editor->preview->browser, 'user_role', $key);?>><?php echo esc_html($role['name']);?></option>
                                                      <?php endforeach;?>
                                                </select>
                                          </li>
                                          <li class="wpshifty-preview-useragent">
                                                <label><?php esc_html_e('Useragent', 'wp-shifty');?></label>
                                                <input class="wpshifty-rounded" type="text" name="browser[useragent]"<?php WP_Shifty_Editor::maybe_value($wpshifty_editor->preview->browser, 'useragent');?>>
                                          </li>
                                          <li class="wpshifty-preview-custom_headers">
                                                <label><?php esc_html_e('Custom headers', 'wp-shifty');?></label>
                                                <textarea class="wpshifty-rounded" placeholder="Custom-header: custom value" name="browser[headers]"><?php WP_Shifty_Editor::maybe_textarea($wpshifty_editor->preview->browser, 'headers');?></textarea>
                                          </li>
                                          <li class="wpshifty-preview-post_data">
                                                <label><?php esc_html_e('POST data', 'wp-shifty');?></label>
                                                <textarea class="wpshifty-rounded" placeholder="key=value&key2=value2" name="browser[postdata]"><?php WP_Shifty_Editor::maybe_textarea($wpshifty_editor->preview->browser, 'postdata');?></textarea>
                                          </li>
                                          <li class="wpshifty-preview-custom_headers">
                                                <label><?php esc_html_e('Cookies', 'wp-shifty');?></label>
                                                <textarea class="wpshifty-rounded" placeholder="cookieName=cookieValue&cookieName2=cookieValue2" name="browser[cookies]"><?php WP_Shifty_Editor::maybe_textarea($wpshifty_editor->preview->browser, 'cookies');?></textarea>
                                          </li>
                                    </ul>
                                    <div id="wpshifty-device">
                                          <iframe id="wpshifty-preview" data-src="<?php echo esc_url(add_query_arg(array('wpshifty-preview' => (int)$wpshifty_editor->id),home_url()))?>"></iframe>
                                    </div>
                              </div>
                        </div>
                        <div id="wpshifty-what" class="wpshifty-empty">
                              <ul id="wpshifty-what-header">
                                    <li><a href="#wpshifty-what-live" class="active"><?php esc_html_e('Live', 'wp-shifty');?></a></li>
                                    <li><a href="#wpshifty-what-editor"><?php esc_html_e('Editor', 'wp-shifty');?></a></li>
                              </ul>
                              <div id="wpshifty-what-body">
                                    <div id="wpshifty-what-live" class="wpshifty-what-body">
                                          <div class="wpshifty-what-live-actions">
                                                <div<?php echo (WP_Shifty_Helper::is_localhost() ? ' title="' . esc_attr__('Lighthouse test is not available on localhost', 'wp-shifty') . '"' : '');?>>
                                                      <a href="#" class="wpshifty-what-live-lighthouse<?php echo (WP_Shifty_Helper::is_localhost() ? ' wpshifty-disabled' : '');?>" id="wpshifty-lighthouse">
                                                            <?php esc_html_e('Lighthouse test', 'wp-shifty');?>
                                                            <img src="<?php echo WP_SHIFTY_URI;?>/images/lighthouse.png" width="20" height="20">
                                                      </a>
                                                </div>
                                                <div class="wpshifty-what-live-autorefresh">
                                                      <?php esc_html_e('Refresh preview automatically', 'wp-shifty');?>
                                                      <input type="checkbox" class="wpshifty-switch-checkbox" id="wpshifty-autorefresh" name="browser[autorefresh]"<?php WP_Shifty_Editor::maybe_checked($wpshifty_editor->preview->browser, 'autorefresh');?>><label class="wpshifty-switch" for="wpshifty-autorefresh"></label>
                                                </div>
                                          </div>
                                          <ul id="wpshifty-what-live-header" class="wpshifty-tabs wpshifty-what-header">
                                                <li class="active"><a href="#wpshifty-what-live-plugins"><?php esc_html_e('Plugins','wp-shifty');?></a></li>
                                                <li><a href="#wpshifty-what-live-css"><?php esc_html_e('CSS','wp-shifty');?></a></li>
                                                <li><a href="#wpshifty-what-live-js"><?php esc_html_e('JS','wp-shifty');?></a></li>
                                          </ul>
                                          <div id="wpshifty-what-live-body">
                                                <div id="wpshifty-what-placeholder">
                                                      <?php foreach((array)get_option('active_plugins') as $plugin):?>
                                                            <?php
                                                                  $data = get_plugin_data(trailingslashit(WP_PLUGIN_DIR) . $plugin);
                                                                  $slug = preg_replace('~([^\/]+)/([^\.]+)\.php~', "$1", $plugin);
                                                            ?>
                                                            <div class="wpshifty-live-plugin">
                                                                  <div class="wpshifty-live-plugin-inner">
                                                                        <img src="<?php echo WP_SHIFTY_API_URL;?>plugins/icon/<?php echo $slug;?>" width="45" height="45"><?php echo $data['Name'];?></div>
                                                                        <input class="wpshifty-plugin-checkbox wpshifty-switch-checkbox" type="checkbox" value="disabled">
                                                                        <label class="wpshifty-switch"></label>
                                                                  </div>
                                                      <?php endforeach;?>
                                                </div>
                                                <div id="wpshifty-what-live-plugins" class="wpshifty-what-list-container" data-fallback="<?php esc_attr_e('No plugins loaded', 'wp-shifty');?>"></div>
                                                <div id="wpshifty-what-live-css" class="wpshifty-hidden wpshifty-what-list-container" data-fallback="<?php esc_attr_e('No CSS loaded', 'wp-shifty');?>"></div>
                                                <div id="wpshifty-what-live-js" class="wpshifty-hidden wpshifty-what-list-container" data-fallback="<?php esc_attr_e('No scripts loaded', 'wp-shifty');?>"></div>
                                          </div>
                                    </div>
                                    <div id="wpshifty-what-editor" class="wpshifty-what-body wpshifty-hidden">
                                          <ul id="wpshifty-what-editor-header" class="wpshifty-tabs wpshifty-what-header">
                                                <li class="active"><a href="#wpshifty-what-editor-disable"><?php esc_html_e('Disable','wp-shifty');?></a></li>
                                                <li><a href="#wpshifty-what-editor-inject"><?php esc_html_e('Inject','wp-shifty');?></a></li>
                                                <li><a href="#wpshifty-what-editor-preload"><?php esc_html_e('Preload','wp-shifty');?></a></li>
                                                <li><a href="#wpshifty-what-editor-lazyload"><?php esc_html_e('Load behavior','wp-shifty');?></a></li>
                                          </ul>
                                          <div id="wpshifty-what-editor-body">
                                                <div id="wpshifty-what-editor-plugins" class="wpshifty-what-list-container wpshifty-hidden">
                                                      <?php if (isset($wpshifty_editor->settings->elements->plugins)):?>
                                                      <?php foreach ((array)$wpshifty_editor->settings->elements->plugins as $plugin => $disabled):?>
                                                            <input class="wpshifty-plugin-checkbox" type="hidden" name="elements[plugins][<?php echo esc_attr($plugin);?>]" value="disabled">
                                                      <?php endforeach;?>
                                                      <?php endif;?>
                                                </div>
                                                <div id="wpshifty-what-editor-disable" class="wpshifty-what-list-container">
                                                      <div class="wpshifty-what-editor-rule wpshifty-add-new" data-type="disable">
                                                            <span class="wpshifty-btn wpshifty-btn-success"><?php esc_html_e('Add new rule', 'wp-shifty');?></span>
                                                      </div>
                                                      <ul class="wpshifty-what-editor-rules">
                                                            <?php if (isset($wpshifty_editor->settings->elements->disable)):?>
                                                            <?php foreach ((array)$wpshifty_editor->settings->elements->disable as $rid => $rule):?>
                                                                  <?php WP_Shifty_Editor::get_rule_editor($rule, 'disable', $rid);?>
                                                            <?php endforeach;?>
                                                            <?php endif;?>
                                                      </ul>
                                                </div>
                                                <div id="wpshifty-what-editor-overwrite" class="wpshifty-hidden wpshifty-what-list-container">
                                                      <div class="wpshifty-what-editor-rule wpshifty-add-new" data-type="overwrite">
                                                            <span class="wpshifty-btn wpshifty-btn-success"><?php esc_html_e('Add new inject', 'wp-shifty');?></span>
                                                      </div>
                                                      <ul class="wpshifty-what-editor-rules">
                                                            <?php if (isset($wpshifty_editor->settings->elements->overwrite)):?>
                                                            <?php foreach ((array)$wpshifty_editor->settings->elements->overwrite as $rid => $rule):?>
                                                                  <?php WP_Shifty_Editor::get_rule_editor($rule, 'overwrite', $rid);?>
                                                            <?php endforeach;?>
                                                            <?php endif;?>
                                                      </ul>
                                                </div>
                                                <div id="wpshifty-what-editor-preload" class="wpshifty-hidden wpshifty-what-list-container">
                                                      <div class="wpshifty-what-editor-rule wpshifty-add-new" data-type="preload">
                                                            <span class="wpshifty-btn wpshifty-btn-success"><?php esc_html_e('Add new preload', 'wp-shifty');?></span>
                                                      </div>
                                                      <ul class="wpshifty-what-editor-rules">
                                                            <?php if (isset($wpshifty_editor->settings->elements->preload)):?>
                                                            <?php foreach ((array)$wpshifty_editor->settings->elements->preload as $rid => $rule):?>
                                                                  <?php WP_Shifty_Editor::get_rule_editor($rule, 'preload', $rid);?>
                                                            <?php endforeach;?>
                                                            <?php endif;?>
                                                      </ul>
                                                </div>
                                                <div id="wpshifty-what-editor-lazyload" class="wpshifty-hidden wpshifty-what-list-container">
                                                      <div class="wpshifty-what-editor-rule wpshifty-add-new" data-type="lazyload">
                                                            <span class="wpshifty-btn wpshifty-btn-success"><?php esc_html_e('Add new rule', 'wp-shifty');?></span>
                                                      </div>
                                                      <ul class="wpshifty-what-editor-rules">
                                                            <?php if (isset($wpshifty_editor->settings->elements->lazyload)):?>
                                                            <?php foreach ((array)$wpshifty_editor->settings->elements->lazyload as $rid => $rule):?>
                                                                  <?php WP_Shifty_Editor::get_rule_editor($rule, 'lazyload', $rid);?>
                                                            <?php endforeach;?>
                                                            <?php endif;?>
                                                      </ul>
                                                </div>
                                                <div id="wpshifty-what-editor-inject" class="wpshifty-hidden wpshifty-what-list-container">
                                                      <div class="wpshifty-what-editor-rule wpshifty-add-new" data-type="inject">
                                                            <span class="wpshifty-btn wpshifty-btn-success"><?php esc_html_e('Add new rule', 'wp-shifty');?></span>
                                                      </div>
                                                      <ul class="wpshifty-what-editor-rules">
                                                            <?php if (isset($wpshifty_editor->settings->elements->inject)):?>
                                                            <?php foreach ((array)$wpshifty_editor->settings->elements->inject as $rid => $rule):?>
                                                                  <?php WP_Shifty_Editor::get_rule_editor($rule, 'inject', $rid);?>
                                                            <?php endforeach;?>
                                                            <?php endif;?>
                                                      </ul>
                                                </div>
                                          </div>
                                    </div>
                              </div>
                        </div>
                  </div>
                  <?php wp_nonce_field('wpshifty-editor', 'wpshifty-nonce');?>
                  <input type="hidden" name="id" value="<?php echo esc_attr($wpshifty_editor->id);?>">
            </form>
            <div id="wpshifty-condition-samples" class="wpshifty-hidden">
            <?php
                  foreach (array('page', 'post-type', 'archive', 'author', 'search', 'everywhere', 'frontend', 'frontpage', 'url', 'admin', 'ajax', 'shop', 'query', 'user', 'post-data', 'header', 'cookie', 'useragent', 'device', 'cronjob', 'cli') as $template){
                        $_condition = new stdClass();
                        include WP_SHIFTY_DIR . 'templates/editor/condition-' . $template . '.tpl.php';
                  }
            ?>
            </div>
            <ul id="wpshifty-editor-samples" class="wpshifty-hidden">
            <?php
                  foreach (array('disable', 'overwrite', 'preload', 'lazyload', 'inject') as $template){
                        $_rule = new stdClass();
                        include WP_SHIFTY_DIR . 'templates/editor/rule-' . $template . '.tpl.php';
                  }
                  ?>
            </ul>

            <?php if (!isset($wpshifty_editor->settings->conditions) || empty($wpshifty_editor->settings->conditions)):?>
            <svg class="wpshifty-condition-arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 60 60">
                  <g fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10">
                        <path class="curve" d="M56.792 33.373c1.24 7.471-5.059 15.454-12.248 17.293-19.737 5.05-30.898-28.609-36.752-41.293"/>
                        <path class="point" d="M15.281 10.326c-4.28-.965-6.285-4.796-10.118-6.504.012 3.201-.255 9.697-2.112 12.511"/>
                  </g>
            </svg>
            <?php endif;?>

            <div id="wpshifty-lighthouse-wrapper" class="wpshifty-hidden wpshifty-fullscreen-editor">
                  <div id="wpshifty-lighthouse-outer" class="wpshifty-fullscreen-editor-outer">
                        <div id="wpshifty-lighthouse-header" class="wpshifty-editor-header wpshifty-fullscreen-editor-header">
                              <img src="<?php echo WP_SHIFTY_URI?>/images/lighthouse.png">
                              <a href="#" class="wpshifty-btn wpshifty-btn-secondary wpshifty-lighthouse-trigger" data-device="mobile"><?php esc_html_e('Run mobile test', 'wp-shifty')?></a>
                              <a href="#" class="wpshifty-btn wpshifty-btn-secondary wpshifty-lighthouse-trigger" data-device="desktop"><?php esc_html_e('Run desktop test', 'wp-shifty')?></a>
                              <a href="#" id="wpshifty-lighthouse-close" class="wpshifty-btn wpshifty-btn-brand"><?php esc_html_e('Close', 'wp-shifty')?></a>
                        </div>
                        <div id="wpshifty-lighthouse-results">
                              <ul data-type="render-blocking-resources"></ul>
                              <ul data-type="unused-css-rules"></ul>
                              <ul data-type="unused-javascript"></ul>
                        </div>
                  </div>
            </div>

            <div id="wpshifty-file-editor-wrapper" class="wpshifty-hidden wpshifty-fullscreen-editor">
                  <div id="wpshifty-file-editor-outer" class="wpshifty-fullscreen-editor-outer">
                        <div id="wpshifty-file-editor-header" class="wpshifty-editor-header wpshifty-fullscreen-editor-header">
                              <img src="<?php echo WP_SHIFTY_URI?>/images/logo.png">
                              <a href="#" class="wpshifty-btn wpshifty-btn-secondary wpshifty-format-code" data-format="beautify"><?php esc_html_e('Beautify code', 'wp-shifty')?></a>
                              <a href="#" class="wpshifty-btn wpshifty-btn-secondary wpshifty-format-code" data-format="minify"><?php esc_html_e('Minify code', 'wp-shifty')?></a>
                              <a href="#" id="wpshifty-file-editor-save" class="wpshifty-btn wpshifty-btn-success"><?php esc_html_e('Save', 'wp-shifty')?></a>
                              <a href="#" id="wpshifty-file-editor-close" class="wpshifty-btn wpshifty-btn-brand"><?php esc_html_e('Close', 'wp-shifty')?></a>
                        </div>
                        <div id="wpshifty-file-editor-current-file"></div>
                        <div id="wpshifty-file-editor-body">
                              <textarea id="wpshifty-file-editor"></textarea>
                        </div>
                  </div>
            </div>

            <div class="wpshifty-status-wrapper"></div>
            <div id="wpshifty-js-error" class="wpshifty-hidden">
                  <div id="wpshifty-js-error-header">
                        <span><?php esc_html_e('Page contains javascript errors', 'wp-shifty');?></span>
                        <a href="#" id="wpshifty-js-error-close">&times;</a>
                  </div>
                  <div id="wpshifty-js-error-inner"></div>
            </div>
            <?php do_action( 'admin_print_footer_scripts' ); ?>
      </body>
</html>
