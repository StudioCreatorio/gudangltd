<?php
if (!defined('ABSPATH')){
      die('Keep calm and carry on!');
}

$scenario_list = (array)WP_Shifty_Scenario::get_list();

$license_status = WP_Shifty_Api::license();

$license_labels = array(
      'active'    => __('active', 'wp-shifty'),
      'pending'   => __('pending', 'wp-shifty'),
      'expired'   => __('expired', 'wp-shifty'),
      'inactive'  => __('inactive', 'wp-shifty')
);

?>
<div class="wpshifty-header">
      <div class="wpshifty-header-image">
            <img src="<?php echo WP_SHIFTY_URI;?>/images/logo.png">
            <div class="wpshifty-top-bar">
                  <div class="wpshifty-version">
                        <label><?php echo sprintf(esc_html__('version: %s', 'wp-shifty'), WP_SHIFTY_VER);?></label>
                  </div>
                  <div class="wpshifty-license">
                        <label><?php esc_html_e('License status', 'wp-shifty');?></label>
                        <span class="wpshifty-badge wpshifty-license-status <?php echo ($license_status == 'active' ? 'wpshifty-badge-success' : 'wpshifty-badge-brand')?>"<?php echo ($license_status == 'active' ? ' title="' . esc_attr__('Disconnect license', 'wp-shifty') . '"' : '')?>><?php echo esc_attr($license_labels[$license_status]);?></span>
                  </div>
            </div>
      </div>
      <div class="wpshifty-header-shape"></div>
</div>
<div class="wpshifty-top">
      <div class="wpshifty-top-start">
            <?php if (empty(WP_Shifty::$license) || $license_status != 'active'):?>
            <div class="wpshifty-activate-wrapper">
                  <div class="wpshifty-activate-icons">
                        <img src="<?php echo get_site_icon_url(512, site_url('/wp-includes/images/w-logo-blue-white-bg.png'));?>">
                        <svg class="wpshifty-icon-3" aria-hidden="true" data-icon="link" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M326.612 185.391c59.747 59.809 58.927 155.698.36 214.59-.11.12-.24.25-.36.37l-67.2 67.2c-59.27 59.27-155.699 59.262-214.96 0-59.27-59.26-59.27-155.7 0-214.96l37.106-37.106c9.84-9.84 26.786-3.3 27.294 10.606.648 17.722 3.826 35.527 9.69 52.721 1.986 5.822.567 12.262-3.783 16.612l-13.087 13.087c-28.026 28.026-28.905 73.66-1.155 101.96 28.024 28.579 74.086 28.749 102.325.51l67.2-67.19c28.191-28.191 28.073-73.757 0-101.83-3.701-3.694-7.429-6.564-10.341-8.569a16.037 16.037 0 0 1-6.947-12.606c-.396-10.567 3.348-21.456 11.698-29.806l21.054-21.055c5.521-5.521 14.182-6.199 20.584-1.731a152.482 152.482 0 0 1 20.522 17.197zM467.547 44.449c-59.261-59.262-155.69-59.27-214.96 0l-67.2 67.2c-.12.12-.25.25-.36.37-58.566 58.892-59.387 154.781.36 214.59a152.454 152.454 0 0 0 20.521 17.196c6.402 4.468 15.064 3.789 20.584-1.731l21.054-21.055c8.35-8.35 12.094-19.239 11.698-29.806a16.037 16.037 0 0 0-6.947-12.606c-2.912-2.005-6.64-4.875-10.341-8.569-28.073-28.073-28.191-73.639 0-101.83l67.2-67.19c28.239-28.239 74.3-28.069 102.325.51 27.75 28.3 26.872 73.934-1.155 101.96l-13.087 13.087c-4.35 4.35-5.769 10.79-3.783 16.612 5.864 17.194 9.042 34.999 9.69 52.721.509 13.906 17.454 20.446 27.294 10.606l37.106-37.106c59.271-59.259 59.271-155.699.001-214.959z"></path></svg>
                        <img src="<?php echo WP_SHIFTY_URI;?>images/fox.png">
                  </div>
                  <a href="<?php echo add_query_arg(array('site' => site_url(), 'token' => wp_create_nonce('wp-shifty-activate')), WP_SHIFTY_API_URL . 'user/connect/');?>" class="wpshifty-btn wpshifty-btn-success wpshifty-btn wpshifty-btn-l"><?php esc_html_e('Activate your license', 'wp-shifty');?></a>
            </div>
            <?php else:?>
                  <a href="<?php echo add_query_arg(array('editor' => 'add-new', 'nonce' => wp_create_nonce('wp-shifty-add')), WP_SHIFTY_ADMIN_URL);?>" class="wpshifty-btn wpshifty-btn-success wpshifty-btn-xl"><?php esc_html_e('Add new scenario', 'wp-shifty');?></a>
            <?php endif;?>
      </div>
      <div class="wpshifty-top-end">
            <h3><?php esc_html_e('Getting Started', 'wp-shifty')?></h3>
            <ul class="wpshifty-help-links">
                  <li><a href="https://docs.wp-shifty.com/knowledgebase/what-are-scenarios" target="_blank"><?php esc_html_e('What are scenarios?', 'wp-shifty');?></a></li>
                  <li><a href="https://docs.wp-shifty.com/knowledgebase/how-to-disable-plugins" target="_blank"><?php esc_html_e('How to disable plugins?', 'wp-shifty');?></a></li>
                  <li><a href="https://docs.wp-shifty.com/knowledgebase/disable-recources/" target="_blank"><?php esc_html_e('How to disable CSS/JS?', 'wp-shifty');?></a></li>
                  <li><a href="https://docs.wp-shifty.com/knowledgebase/overwrite-resources/" target="_blank"><?php esc_html_e('How to overwrite CSS/JS?', 'wp-shifty');?></a></li>
                  <li><a href="https://docs.wp-shifty.com/knowledgebase/load-behavior/" target="_blank"><?php esc_html_e('How to lazyload assets?', 'wp-shifty');?></a></li>
                  <li><a href="https://docs.wp-shifty.com/knowledgebase/how-to-test-rules" target="_blank"><?php esc_html_e('How to test rules?', 'wp-shifty');?></a></li>
            </ul>
            <div class="wpshifty-social">
                  <a href="https://wp-shifty.com/go/facebook" target="_blank"><svg aria-hidden="true" focusable="false" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="wpshifty-icon-125"><path fill="currentColor" d="M400 32H48A48 48 0 0 0 0 80v352a48 48 0 0 0 48 48h137.25V327.69h-63V256h63v-54.64c0-62.15 37-96.48 93.67-96.48 27.14 0 55.52 4.84 55.52 4.84v61h-31.27c-30.81 0-40.42 19.12-40.42 38.73V256h68.78l-11 71.69h-57.78V480H400a48 48 0 0 0 48-48V80a48 48 0 0 0-48-48z"></path></svg></a>
                  <a href="https://wp-shifty.com/go/youtube" target="_blank"><svg aria-hidden="true" focusable="false" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" class="wpshifty-icon-175"><path fill="currentColor" d="M549.655 124.083c-6.281-23.65-24.787-42.276-48.284-48.597C458.781 64 288 64 288 64S117.22 64 74.629 75.486c-23.497 6.322-42.003 24.947-48.284 48.597-11.412 42.867-11.412 132.305-11.412 132.305s0 89.438 11.412 132.305c6.281 23.65 24.787 41.5 48.284 47.821C117.22 448 288 448 288 448s170.78 0 213.371-11.486c23.497-6.321 42.003-24.171 48.284-47.821 11.412-42.867 11.412-132.305 11.412-132.305s0-89.438-11.412-132.305zm-317.51 213.508V175.185l142.739 81.205-142.739 81.201z"></path></svg></a>
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 95 20" class="wpshifty-follow-us"><g fill="none" stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-miterlimit="10"><path d="M3.471 11.53c1.522 1.408 3.577 1.145 5.935 1.174 9.331.121 18.768-.963 28.067-1.583 15.529-1.034 32.082-3.414 47.576-.777"/><path stroke-linejoin="round" d="M81.662 16.798c2.522-3.59 6.828-3.986 9.867-6.88-2.967-1.208-8.871-3.928-10.767-6.716"/></g></svg>
                  <strong><?php esc_html_e('Follow us!', 'wp-shifty');?></strong>
            </div>
      </div>
</div>
<div class="wpshifty-scenario-container<?php echo ($license_status !== 'active' ? ' wpshifty-disabled' : '');?>">
      <?php if (count($scenario_list) > 1):?>
      <div class="wpshifty-search-scenario wpshifty-box">
            <div class="wpshifty-box-start"></div>
            <div class="wpshifty-box-inner">
                  <input type="text" name="search" placeholder="<?php esc_html_e('SEARCH', 'wp-shifty');?>">
            </div>
            <div class="wpshifty-box-end"></div>
      </div>
      <?php endif;?>
      <ul class="wpshifty-scenario-list">
            <?php foreach($scenario_list as $scenario):?>
                  <?php echo WP_Shifty_Scenario::get_template($scenario);?>
            <?php endforeach;?>
      </ul>
</div>
<div class="wpshifty-status-wrapper"></div>