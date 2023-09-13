<?php
      if (isset($id)){
            $inject_file      = trailingslashit(WP_Shifty_Helper::upload_dir()->dir) . $id . '.txt';
            $content          = file_get_contents($inject_file);
            $summary          = '';
            if (file_exists($inject_file)){
                  $summary = substr($content, 0, WP_SHIFTY_RESOURCE_MAX_LENGTH) . (strlen($content) > WP_SHIFTY_RESOURCE_MAX_LENGTH ? '...' : '');
            }
            else {
                  echo $inject_file;
            }
      }
      else {
            $summary = __('Click here to edit', 'wp-shifty');
      }
?>
<li class="wpshifty-what-editor-rule" data-type="inject" data-rule-id="%ID">
      <a href="#" class="wpshifty-editor-rule-remove"><?php esc_html_e('Delete', 'wp-shifty');?></a>
      <div class="wpshifty-rule-editor-area">
            <div class="wpshifty-container">
                  <label><?php esc_html_e('Location', 'wp-shifty');?></label>
                  <select class="wpshifty-colorful-select" name="elements[inject][%ID][location]">
                        <option value="head_beginning"<?php WP_Shifty_Editor::maybe_selected($_rule, 'location', 'head_top');?>><?php esc_html_e('Beginning of &lt;head&gt;', 'wp-shifty');?></option>
                        <option value="head_after_styles"<?php WP_Shifty_Editor::maybe_selected($_rule, 'location', 'head_after_styles');?>><?php esc_html_e('After styles in &lt;head&gt;', 'wp-shifty');?></option>
                        <option value="head_end"<?php WP_Shifty_Editor::maybe_selected($_rule, 'location', 'head_end');?>><?php esc_html_e('End of &lt;head&gt;', 'wp-shifty');?></option>
                        <option value="footer_beginning"<?php WP_Shifty_Editor::maybe_selected($_rule, 'location', 'footer_beginning');?>><?php esc_html_e('Beginning of footer', 'wp-shifty');?></option>
                        <option value="footer_end"<?php WP_Shifty_Editor::maybe_selected($_rule, 'location', 'footer_end');?>><?php esc_html_e('End of footer', 'wp-shifty');?></option>
                  </select>
            </div>
            <div class="wpshifty-file-preview wpshifty-file-editor-trigger" data-file-name="%ID"><?php echo esc_html($summary);?></div>
            <input type="text" class="wpshifty-hidden wpshifty-file-editor-url" name="elements[inject][%ID][inject]" value="<?php echo (isset($_rule->inject) ? esc_attr($_rule->inject) : '');?>">
      </div>
</li>