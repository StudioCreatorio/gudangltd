<?php
if (!defined('ABSPATH')){
      die('Keep calm and carry on!');
}
?>
<div data-condition-editor="everywhere" data-condition-id="%ID" class="wpshifty-condition-autosave wpshifty-hidden">
      <input type="hidden" name="condition[%ID][type]" value="everywhere">
      <?php esc_html_e('Apply Everywhere', 'wp-shifty');?>
      <div class="wpshifty-condition-info"><?php esc_html_e('Every request will met with this condition, use it with exceptions.', 'wp-shifty');?></div>
      <div class="wpshifty-buttonset wpshifty-pullright">
            <a href="#save-condition" class="wpshifty-btn wpshifty-btn-success"><?php esc_html_e('Save', 'wp-shifty');?></a>
            <a href="#delete-condition" class="wpshifty-btn wpshifty-btn-brand"><?php esc_html_e('Delete', 'wp-shifty');?></a>
      </div>
</div>