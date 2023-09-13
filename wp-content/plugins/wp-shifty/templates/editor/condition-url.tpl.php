<?php
if (!defined('ABSPATH')){
      die('Keep calm and carry on!');
}

$urls = array(site_url());
foreach (get_posts(array('posts_per_page' => 100, 'post_type' => 'any')) as $post) {
      $urls[] = get_permalink($post);
}
?>
<div data-condition-editor="url" data-condition-id="%ID" class="wpshifty-hidden">
      <input type="hidden" name="condition[%ID][type]" value="url">
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
      <div class="wpshifty-url-editor">
            <label>
                  <?php esc_html_e('Match', 'wp-shifty');?>
                  <select class="wpshifty-colorful-select" name="condition[%ID][match]">
                        <option value="exact"<?php WP_Shifty_Editor::maybe_selected($_condition, 'match', 'exact')?>><?php esc_html_e('Exact', 'wp-shifty');?></option>
                        <option value="partial"<?php WP_Shifty_Editor::maybe_selected($_condition, 'match', 'partial')?>><?php esc_html_e('Partial', 'wp-shifty');?></option>
                        <option value="regex"<?php WP_Shifty_Editor::maybe_selected($_condition, 'match', 'regex')?>><?php esc_html_e('Regex', 'wp-shifty');?></option>
                  </select>
            </label>
            <div class="wpshifty-autocomplete-set wpshifty-autocomplete-url-editor">
                  <a href="#" class="wpshifty-autocomplete-toggle<?php echo (isset($_condition->match) && $_condition->match != 'exact' ? ' wpshifty-hidden' : '');?>">
                        <svg aria-hidden="true" focusable="false" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M80 368H16a16 16 0 0 0-16 16v64a16 16 0 0 0 16 16h64a16 16 0 0 0 16-16v-64a16 16 0 0 0-16-16zm0-320H16A16 16 0 0 0 0 64v64a16 16 0 0 0 16 16h64a16 16 0 0 0 16-16V64a16 16 0 0 0-16-16zm0 160H16a16 16 0 0 0-16 16v64a16 16 0 0 0 16 16h64a16 16 0 0 0 16-16v-64a16 16 0 0 0-16-16zm416 176H176a16 16 0 0 0-16 16v32a16 16 0 0 0 16 16h320a16 16 0 0 0 16-16v-32a16 16 0 0 0-16-16zm0-320H176a16 16 0 0 0-16 16v32a16 16 0 0 0 16 16h320a16 16 0 0 0 16-16V80a16 16 0 0 0-16-16zm0 160H176a16 16 0 0 0-16 16v32a16 16 0 0 0 16 16h320a16 16 0 0 0 16-16v-32a16 16 0 0 0-16-16z"></path></svg>
                  </a>
                  <input type="<?php echo (isset($_condition->match) && $_condition->match == 'regex' ? 'text' : 'url');?>" class="wpshifty-url-input" name="condition[%ID][url]"<?php WP_Shifty_Editor::maybe_value($_condition, 'url');?>>
                  <div class="wpshifty-autocomplete-wrapper wpshifty-hidden">
                        <input type="text" class="wpshifty-autocomplete-search" placeholder="<?php esc_html_e('Search', 'wp-shifty');?>">
                        <ul class="wpshifty-autocomplete-list">
                              <?php foreach($urls as $url):?>
                                    <li><?php echo esc_html($url);?></li>
                              <?php endforeach;?>
                        </ul>
                  </div>
            </div>

      </div>
      <div class="wpshifty-condition-info"><?php esc_html_e('The condition is met if specified URL is match with the condition. You can use Exact, Partial or Regex mode', 'wp-shifty');?></div>
      <div class="wpshifty-buttonset wpshifty-pullright">
            <a href="#save-condition" class="wpshifty-btn wpshifty-btn-success"><?php esc_html_e('Save', 'wp-shifty');?></a>
            <a href="#cancel-condition" class="wpshifty-btn wpshifty-btn-brand"><?php esc_html_e('Cancel', 'wp-shifty');?></a>
            <a href="#delete-condition" class="wpshifty-btn wpshifty-btn-brand"><?php esc_html_e('Delete', 'wp-shifty');?></a>
      </div>
</div>