<li class="wpshifty-what-editor-rule" data-type="lazyload" data-rule-id="%ID">
      <a href="#" class="wpshifty-editor-rule-remove"><?php esc_html_e('Delete', 'wp-shifty');?></a>
      <input type="checkbox" name="elements[lazyload][%ID][readonly]" class="wpshifty-readonly-rule wpshifty-hidden"<?php WP_Shifty_Editor::maybe_checked($_rule, 'readonly')?>>
      <div class="wpshifty-rule-editor-area">
            <div class="wpshifty-buttonset wpshifty-buttonset-rule-type">
                  <span class="wpshifty-rule-title"><img src="<?php echo WP_SHIFTY_URI;?>images/what.png"> <?php esc_html_e('Type', 'wp-shifty');?></span>
                  <input type="radio" class="wpshifty-radio-square-input" name="elements[lazyload][%ID][type]" id="%ID_css" value="css"<?php WP_Shifty_Editor::maybe_checked($_rule, 'type', 'css')?>><label for="%ID_css" class="wpshifty-radio-square"></label><label for="%ID_css"><?php esc_html_e('CSS', 'wp-shifty');?></label>
                  <input type="radio" class="wpshifty-radio-square-input" name="elements[lazyload][%ID][type]" id="%ID_css_inline" value="css_inline"<?php WP_Shifty_Editor::maybe_checked($_rule, 'type', 'css_inline')?>><label for="%ID_css_inline" class="wpshifty-radio-square"></label><label for="%ID_css_inline"><?php esc_html_e('Inline CSS', 'wp-shifty');?></label>
                  <input type="radio" class="wpshifty-radio-square-input" name="elements[lazyload][%ID][type]" id="%ID_js" value="js"<?php WP_Shifty_Editor::maybe_checked($_rule, 'type', 'js')?>><label for="%ID_js" class="wpshifty-radio-square"></label><label for="%ID_js"><?php esc_html_e('JS', 'wp-shifty');?></label>
                  <input type="radio" class="wpshifty-radio-square-input" name="elements[lazyload][%ID][type]" id="%ID_js_inline" value="js_inline"<?php WP_Shifty_Editor::maybe_checked($_rule, 'type', 'js_inline')?>><label for="%ID_js_inline" class="wpshifty-radio-square"></label><label for="%ID_js_inline"><?php esc_html_e('Inline JS', 'wp-shifty');?></label>
            </div>
            <div class="wpshifty-buttonset wpshifty-buttonset-match-type">
                  <span class="wpshifty-rule-title"><img src="<?php echo WP_SHIFTY_URI;?>images/match.png"> <?php esc_html_e('Match', 'wp-shifty');?></span>
                  <input type="radio" class="wpshifty-radio-square-input" name="elements[lazyload][%ID][match]" id="%ID_exact" value="exact"<?php WP_Shifty_Editor::maybe_checked($_rule, 'match', 'exact')?>><label for="%ID_exact" class="wpshifty-radio-square"></label><label for="%ID_exact"><?php esc_html_e('Exact match', 'wp-shifty');?></label>
                  <input type="radio" class="wpshifty-radio-square-input" name="elements[lazyload][%ID][match]" id="%ID_partial" value="partial"<?php WP_Shifty_Editor::maybe_checked($_rule, 'match', 'partial')?>><label for="%ID_partial" class="wpshifty-radio-square"></label><label for="%ID_partial"><?php esc_html_e('Partial match', 'wp-shifty');?></label>
                  <input type="radio" class="wpshifty-radio-square-input" name="elements[lazyload][%ID][match]" id="%ID_regex" value="regex"<?php WP_Shifty_Editor::maybe_checked($_rule, 'match', 'regex')?>><label for="%ID_regex" class="wpshifty-radio-square"></label><label for="%ID_regex"><?php esc_html_e('Regex', 'wp-shifty');?></label>
            </div>
            <div class="wpshifty-buttonset wpshifty-buttonset-load-type">
                  <span class="wpshifty-rule-title"><img src="<?php echo WP_SHIFTY_URI;?>images/load.png"> <?php esc_html_e('Load', 'wp-shifty');?></span>
                  <input type="radio" class="wpshifty-radio-square-input" name="elements[lazyload][%ID][load]" id="%ID_async" value="async"<?php WP_Shifty_Editor::maybe_checked($_rule, 'load', 'async')?>><label for="%ID_async" class="wpshifty-radio-square"></label><label for="%ID_async"><?php esc_html_e('Async', 'wp-shifty');?></label>
                  <input type="radio" class="wpshifty-radio-square-input" name="elements[lazyload][%ID][load]" id="%ID_defer" value="defer"<?php WP_Shifty_Editor::maybe_checked($_rule, 'load', 'defer')?>><label for="%ID_defer" class="wpshifty-radio-square"></label><label for="%ID_defer"><?php esc_html_e('Defer', 'wp-shifty');?></label>
                  <input type="radio" class="wpshifty-radio-square-input" name="elements[lazyload][%ID][load]" id="%ID_lazy" value="lazy"<?php WP_Shifty_Editor::maybe_checked($_rule, 'load', 'lazy')?>><label for="%ID_lazy" class="wpshifty-radio-square"></label><label for="%ID_lazy"><?php esc_html_e('Lazy', 'wp-shifty');?></label>
            </div>
            <div class="wpshifty-autocomplete-set" data-source="[&quote;css&quote;,&quote;js&quote;]">
                  <a href="#" class="wpshifty-autocomplete-toggle">
                        <svg aria-hidden="true" focusable="false" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M80 368H16a16 16 0 0 0-16 16v64a16 16 0 0 0 16 16h64a16 16 0 0 0 16-16v-64a16 16 0 0 0-16-16zm0-320H16A16 16 0 0 0 0 64v64a16 16 0 0 0 16 16h64a16 16 0 0 0 16-16V64a16 16 0 0 0-16-16zm0 160H16a16 16 0 0 0-16 16v64a16 16 0 0 0 16 16h64a16 16 0 0 0 16-16v-64a16 16 0 0 0-16-16zm416 176H176a16 16 0 0 0-16 16v32a16 16 0 0 0 16 16h320a16 16 0 0 0 16-16v-32a16 16 0 0 0-16-16zm0-320H176a16 16 0 0 0-16 16v32a16 16 0 0 0 16 16h320a16 16 0 0 0 16-16V80a16 16 0 0 0-16-16zm0 160H176a16 16 0 0 0-16 16v32a16 16 0 0 0 16 16h320a16 16 0 0 0 16-16v-32a16 16 0 0 0-16-16z"></path></svg>
                  </a>
                  <input type="text" class="wpshifty-rule-value" name="elements[lazyload][%ID][rule]" value="<?php echo (isset($_rule->rule) ? esc_attr($_rule->rule) : '');?>" placeholder="<?php esc_html_e('« Search resource or start typing', 'wp-shifty');?>">
                  <div class="wpshifty-autocomplete-wrapper wpshifty-hidden">
                        <input type="text" class="wpshifty-autocomplete-search" placeholder="<?php esc_html_e('Search', 'wp-shifty');?>">
                        <ul class="wpshifty-autocomplete-list"></ul>
                  </div>
            </div>
      </div>
      <div class="wpshifty-readonly-rule-summary"><?php echo esc_html(isset($_rule->summary) && !empty($_rule->summary) ? $_rule->summary : (isset($_rule->rule) ? $_rule->rule : ''))?></div>
      <textarea name="elements[lazyload][%ID][summary]" class="wpshifty-hidden wpshifty-readonly-rule-summary-preview"><?php echo esc_textarea(isset($_rule->summary) ? $_rule->summary : '')?></textarea>
</li>