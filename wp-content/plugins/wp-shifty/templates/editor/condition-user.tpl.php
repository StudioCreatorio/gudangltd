<?php
if (!defined('ABSPATH')){
      die('Keep calm and carry on!');
}
?>
<div data-condition-editor="user" data-condition-id="%ID" class="wpshifty-hidden">
      <input type="hidden" name="condition[%ID][type]" value="user">
      <div class="wpshifty-condition-kind">
            <input type="checkbox" class="condition-kind" name="condition[%ID][is-exception]" id="wps_label__%ID_is-exception"<?php WP_Shifty_Editor::maybe_checked($_condition, 'is-exception')?>>
            <label for="wps_label__%ID_is-exception"><?php esc_html_e('Apply', 'wp-shifty');?></label>
            <label for="wps_label__%ID_is-exception"><?php esc_html_e('Exception', 'wp-shifty');?></label>
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 152.9 43.4">
                  <path d="M151.9,13.6c0,0,3.3-9.5-85-8.3c-97,1.3-58.3,29-58.3,29s9.7,8.1,69.7,8.1c68.3,0,69.3-23.1,69.3-23.1 s1.7-10.5-14.7-18.4"></path>
            </svg>
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 152.9 43.4">
                  <path d="M151.9,13.6c0,0,3.3-9.5-85-8.3c-97,1.3-58.3,29-58.3,29s9.7,8.1,69.7,8.1c68.3,0,69.3-23.1,69.3-23.1 s1.7-10.5-14.7-18.4"></path>
            </svg>
      </div>
      <div class="wpshifty-nice-select-wrapper">
            <input type="text" class="wpshifty-nice-select-filter" placeholder="<?php esc_html_e('Start typing to filter', 'wp-shifty');?>">
            <a href="#" class="wpshifty-nice-select-clear-filter wpshifty-hidden">x</a>
            <ul class="wpshifty-nice-select">
                  <li>
                        <input type="checkbox" id="wps_label_%ID_not_logged_in" name="condition[%ID][roles][]" value="not-logged-in"<?php echo (isset($_condition->roles) && in_array('not-logged-in', $_condition->roles) ? ' checked' : '');?>>
                        <label for="wps_label_%ID_not_logged_in"><?php echo esc_html__('Not logged in', 'wp-shifty');?></label>
                  </li>
                  <?php foreach (WP_Shifty_Editor::get_user_roles() as $key => $role):?>
                        <li>
                              <input type="checkbox" id="wps_label_%ID_<?php echo esc_attr($key);?>" name="condition[%ID][roles][]" value="<?php echo esc_attr($key);?>"<?php WP_Shifty_Editor::maybe_checked($_condition, 'roles', $key, 'in');?>>
                              <label for="wps_label_%ID_<?php echo esc_attr($key);?>"><?php echo esc_html($role['name']);?></label>
                        </li>
                  <?php endforeach;?>
                  <li class="wpshifty-nice-select-noresult wpshifty-hidden"><?php esc_html_e('No result found', 'wp-shifty');?></li>
            </ul>
      </div>
      <div class="wpshifty-condition-info"><?php esc_html_e('The condition is met if the visitor has one of these user roles.', 'wp-shifty');?></div>
      <div class="wpshifty-buttonset wpshifty-pullright">
            <a href="#save-condition" class="wpshifty-btn wpshifty-btn-success"><?php esc_html_e('Save', 'wp-shifty');?></a>
            <a href="#cancel-condition" class="wpshifty-btn wpshifty-btn-brand"><?php esc_html_e('Cancel', 'wp-shifty');?></a>
            <a href="#delete-condition" class="wpshifty-btn wpshifty-btn-brand"><?php esc_html_e('Delete', 'wp-shifty');?></a>
      </div>
</div>