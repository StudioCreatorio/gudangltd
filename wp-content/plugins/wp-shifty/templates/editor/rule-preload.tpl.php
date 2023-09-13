<li class="wpshifty-what-editor-rule" data-type="preload" data-rule-id="%ID">
      <a href="#" class="wpshifty-editor-rule-remove"><?php esc_html_e('Delete', 'wp-shifty');?></a>
      <div class="wpshifty-rule-editor-area">
            <div class="wpshifty-autocomplete-set" data-source="[&quote;css&quote;,&quote;js&quote;,&quote;image&quote;,&quote;font&quote;]">
                  <a href="#" class="wpshifty-autocomplete-toggle">
                        <svg aria-hidden="true" focusable="false" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M80 368H16a16 16 0 0 0-16 16v64a16 16 0 0 0 16 16h64a16 16 0 0 0 16-16v-64a16 16 0 0 0-16-16zm0-320H16A16 16 0 0 0 0 64v64a16 16 0 0 0 16 16h64a16 16 0 0 0 16-16V64a16 16 0 0 0-16-16zm0 160H16a16 16 0 0 0-16 16v64a16 16 0 0 0 16 16h64a16 16 0 0 0 16-16v-64a16 16 0 0 0-16-16zm416 176H176a16 16 0 0 0-16 16v32a16 16 0 0 0 16 16h320a16 16 0 0 0 16-16v-32a16 16 0 0 0-16-16zm0-320H176a16 16 0 0 0-16 16v32a16 16 0 0 0 16 16h320a16 16 0 0 0 16-16V80a16 16 0 0 0-16-16zm0 160H176a16 16 0 0 0-16 16v32a16 16 0 0 0 16 16h320a16 16 0 0 0 16-16v-32a16 16 0 0 0-16-16z"></path></svg>
                  </a>
                  <input type="text" class="wpshifty-rule-value wpshifty-preload-url" name="elements[preload][%ID][source]" value="<?php echo (isset($_rule->source) ? esc_attr($_rule->source) : '');?>" placeholder="<?php esc_html_e('« Search resource or start typing', 'wp-shifty');?>">
                  <div class="wpshifty-autocomplete-wrapper wpshifty-hidden">
                        <input type="text" class="wpshifty-autocomplete-search" placeholder="<?php esc_html_e('Search', 'wp-shifty');?>">
                        <ul class="wpshifty-autocomplete-list"></ul>
                  </div>
            </div>
            <div class="wpshifty-container">
                  <label><?php esc_html_e('Preload as', 'wp-shifty');?></label>
                  <select class="wpshifty-preload-as wpshifty-colorful-select" name="elements[preload][%ID][as]">
                        <option value="style"<?php WP_Shifty_Editor::maybe_selected($_rule, 'as', 'style');?>><?php esc_html_e('style', 'wp-shifty');?></option>
                        <option value="script"<?php WP_Shifty_Editor::maybe_selected($_rule, 'as', 'script');?>><?php esc_html_e('script', 'wp-shifty');?></option>
                        <option value="font"<?php WP_Shifty_Editor::maybe_selected($_rule, 'as', 'font');?>><?php esc_html_e('font', 'wp-shifty');?></option>
                        <option value="image"<?php WP_Shifty_Editor::maybe_selected($_rule, 'as', 'image');?>><?php esc_html_e('image', 'wp-shifty');?></option>
                        <option value="video"<?php WP_Shifty_Editor::maybe_selected($_rule, 'as', 'video');?>><?php esc_html_e('video', 'wp-shifty');?></option>
                        <option value="audio"<?php WP_Shifty_Editor::maybe_selected($_rule, 'as', 'audio');?>><?php esc_html_e('audio', 'wp-shifty');?></option>
                        <option value="embed"<?php WP_Shifty_Editor::maybe_selected($_rule, 'as', 'embed');?>><?php esc_html_e('embed', 'wp-shifty');?></option>
                        <option value="document"<?php WP_Shifty_Editor::maybe_selected($_rule, 'as', 'document');?>><?php esc_html_e('document', 'wp-shifty');?></option>
                  </select>
            </div>
            <div class="wpshifty-container">
                  <label><?php esc_html_e('Media', 'wp-shifty');?></label>
                  <input type="text" class="wpshifty-fullwidth" name="elements[preload][%ID][media]" value="<?php echo (isset($_rule->media) ? esc_attr($_rule->media) : '');?>" placeholder="<?php esc_html_e('(min-width:768px) or leave empty', 'wp-shifty')?>">
            </div>

      </div>
</li>