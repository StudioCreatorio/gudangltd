<li class="wpshifty-what-editor-rule" data-type="overwrite" data-rule-id="%ID">
      <a href="#" class="wpshifty-editor-rule-remove"><?php esc_html_e('Delete', 'wp-shifty');?></a>
      <input type="checkbox" name="elements[overwrite][%ID][readonly]" class="wpshifty-readonly-rule wpshifty-hidden"<?php WP_Shifty_Editor::maybe_checked($_rule, 'readonly')?>>
      <div class="wpshifty-rule-editor-area">
            <div class="wpshifty-autocomplete-set" data-source="[&quote;css&quote;,&quote;js&quote;]">
                  <a href="#" class="wpshifty-autocomplete-toggle">
                        <svg aria-hidden="true" focusable="false" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M80 368H16a16 16 0 0 0-16 16v64a16 16 0 0 0 16 16h64a16 16 0 0 0 16-16v-64a16 16 0 0 0-16-16zm0-320H16A16 16 0 0 0 0 64v64a16 16 0 0 0 16 16h64a16 16 0 0 0 16-16V64a16 16 0 0 0-16-16zm0 160H16a16 16 0 0 0-16 16v64a16 16 0 0 0 16 16h64a16 16 0 0 0 16-16v-64a16 16 0 0 0-16-16zm416 176H176a16 16 0 0 0-16 16v32a16 16 0 0 0 16 16h320a16 16 0 0 0 16-16v-32a16 16 0 0 0-16-16zm0-320H176a16 16 0 0 0-16 16v32a16 16 0 0 0 16 16h320a16 16 0 0 0 16-16V80a16 16 0 0 0-16-16zm0 160H176a16 16 0 0 0-16 16v32a16 16 0 0 0 16 16h320a16 16 0 0 0 16-16v-32a16 16 0 0 0-16-16z"></path></svg>
                  </a>
                  <input type="text" class="wpshifty-rule-value wpshifty-file-editor-source" name="elements[overwrite][%ID][source]" value="<?php echo (isset($_rule->source) ? esc_attr($_rule->source) : '');?>">
                  <div class="wpshifty-autocomplete-wrapper wpshifty-hidden">
                        <input type="text" class="wpshifty-autocomplete-search" placeholder="<?php esc_html_e('Search', 'wp-shifty');?>">
                        <ul class="wpshifty-autocomplete-list"></ul>
                  </div>
            </div>
            <select name="elements[overwrite][%ID][type]" class="wpshifty-file-type">
                  <option value="css">CSS</option>
                  <option value="js">JS</option>
            </select>
      </div>
      <div class="wpshifty-readonly-rule-summary"><?php echo esc_html(isset($_rule->summary) && !empty($_rule->summary) ? $_rule->summary : (isset($_rule->source) ? $_rule->source : ''))?></div>
      <textarea name="elements[overwrite][%ID][summary]" class="wpshifty-hidden wpshifty-readonly-rule-summary-preview"><?php echo esc_textarea(isset($_rule->summary) ? $_rule->summary : '')?></textarea>
      <div class="wpshifty-overwirte-new-resource"><?php echo (isset($_rule->overwrite) ? esc_html($_rule->overwrite) : '');?></div>
      <a href="#" class="wpshifty-btn wpshifty-file-editor-trigger" data-file-name="%ID"><?php esc_html_e('Edit', 'wp-shifty');?></a>
      <input type="text" class="wpshifty-hidden wpshifty-file-editor-url" name="elements[overwrite][%ID][overwrite]" value="<?php echo (isset($_rule->overwrite) ? esc_attr($_rule->overwrite) : '');?>">
</li>