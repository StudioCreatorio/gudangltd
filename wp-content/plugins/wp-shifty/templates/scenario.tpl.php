<li class="wpshifty-scenario wpshifty-scenario-<?php echo esc_attr($scenario->status);?>" data-id="<?php echo (int)$scenario->id;?>">
      <div class="wpshifty-box">
            <div class="wpshifty-box-start"></div>
            <div class="wpshifty-box-inner">
                  <div class="wpshifty-scenario-number">#<?php echo (int)$scenario->id;?></div>
                  <ul class="wpshifty-scenario-actions">
                        <li>
                              <a href="<?php echo esc_url(add_query_arg('editor', (int)$scenario->id, WP_SHIFTY_ADMIN_URL));?>">
                                    <span><?php esc_html_e('Edit', 'wp-shifty');?></span>
                                    <svg class="wpshifty-icon wpshifty-icon-125" aria-hidden="true" focusable="false" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 576"><path fill="currentColor" d="M402.6 83.2l90.2 90.2c3.8 3.8 3.8 10 0 13.8L274.4 405.6l-92.8 10.3c-12.4 1.4-22.9-9.1-21.5-21.5l10.3-92.8L388.8 83.2c3.8-3.8 10-3.8 13.8 0zm162-22.9l-48.8-48.8c-15.2-15.2-39.9-15.2-55.2 0l-35.4 35.4c-3.8 3.8-3.8 10 0 13.8l90.2 90.2c3.8 3.8 10 3.8 13.8 0l35.4-35.4c15.2-15.3 15.2-40 0-55.2zM384 346.2V448H64V128h229.8c3.2 0 6.2-1.3 8.5-3.5l40-40c7.6-7.6 2.2-20.5-8.5-20.5H48C21.5 64 0 85.5 0 112v352c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V306.2c0-10.7-12.9-16-20.5-8.5l-40 40c-2.2 2.3-3.5 5.3-3.5 8.5z"></path></svg>
                              </a>
                        </li>
                        <li>
                              <a href="#" class="wpshifty-scenario-update-status wpshifty-pause-scenario">
                                    <span><?php esc_html_e('Pause', 'wp-shifty');?></span>
                                    <svg class="wpshifty-icon wpshifty-icon-125" aria-hidden="true" focusable="false" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm0 448c-110.5 0-200-89.5-200-200S145.5 56 256 56s200 89.5 200 200-89.5 200-200 200zm96-280v160c0 8.8-7.2 16-16 16h-48c-8.8 0-16-7.2-16-16V176c0-8.8 7.2-16 16-16h48c8.8 0 16 7.2 16 16zm-112 0v160c0 8.8-7.2 16-16 16h-48c-8.8 0-16-7.2-16-16V176c0-8.8 7.2-16 16-16h48c8.8 0 16 7.2 16 16z"></path></svg>
                              </a>
                              <a href="#" class="wpshifty-scenario-update-status wpshifty-activate-scenario">
                                    <span><?php esc_html_e('Activate', 'wp-shifty');?></span>
                                    <svg class="wpshifty-icon wpshifty-icon-125" aria-hidden="true" focusable="false" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M371.7 238l-176-107c-15.8-8.8-35.7 2.5-35.7 21v208c0 18.4 19.8 29.8 35.7 21l176-101c16.4-9.1 16.4-32.8 0-42zM504 256C504 119 393 8 256 8S8 119 8 256s111 248 248 248 248-111 248-248zm-448 0c0-110.5 89.5-200 200-200s200 89.5 200 200-89.5 200-200 200S56 366.5 56 256z"></path></svg>
                              </a>
                        </li>
                        <li>
                              <a class="wpshifty-delete-scenario" href="#">
                                    <span><?php esc_html_e('Delete', 'wp-shifty');?></span>
                                    <svg class="wpshifty-icon wpshifty-icon-125" aria-hidden="true" focusable="false" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M325.8 193.8L263.6 256l62.2 62.2c4.7 4.7 4.7 12.3 0 17l-22.6 22.6c-4.7 4.7-12.3 4.7-17 0L224 295.6l-62.2 62.2c-4.7 4.7-12.3 4.7-17 0l-22.6-22.6c-4.7-4.7-4.7-12.3 0-17l62.2-62.2-62.2-62.2c-4.7-4.7-4.7-12.3 0-17l22.6-22.6c4.7-4.7 12.3-4.7 17 0l62.2 62.2 62.2-62.2c4.7-4.7 12.3-4.7 17 0l22.6 22.6c4.7 4.7 4.7 12.3 0 17zM448 80v352c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V80c0-26.5 21.5-48 48-48h352c26.5 0 48 21.5 48 48zm-48 346V86c0-3.3-2.7-6-6-6H54c-3.3 0-6 2.7-6 6v340c0 3.3 2.7 6 6 6h340c3.3 0 6-2.7 6-6z"></path></svg>

                              </a>
                        </li>
                        <li>
                              <a class="wpshifty-duplicate-scenario" href="#">
                                    <span><?php esc_html_e('Duplicate', 'wp-shifty');?></span>
                                    <svg class="wpshifty-icon wpshifty-icon-125" aria-hidden="true" focusable="false" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M0 224C0 188.7 28.65 160 64 160H128V288C128 341 170.1 384 224 384H352V448C352 483.3 323.3 512 288 512H64C28.65 512 0 483.3 0 448V224zM224 352C188.7 352 160 323.3 160 288V64C160 28.65 188.7 0 224 0H448C483.3 0 512 28.65 512 64V288C512 323.3 483.3 352 448 352H224z"/></svg>

                              </a>
                        </li>
                  </ul>
                  <div class="wpshifty-scenario-inner">
                        <div class="wpshifty-rule-where">
                              <?php echo (!empty($condition_summary) ? $condition_summary : esc_html__('No condition set', 'wp-shifty'));?>
                        </div>
                        <div class="wpshifty-rule-what">
                              <div class="wpshifty-short-summary">
                                    <?php echo (!empty($rule_summary['short']) ? $rule_summary['short'] : esc_html__('No rule set', 'wp-shifty'));?>
                              </div>
                              <div class="wpshifty-full-summary">
                                    <?php echo (!empty($rule_summary['full']) ? $rule_summary['full'] : esc_html__('No rule set', 'wp-shifty'));?>
                              </div>
                        </div>
                        <?php if (!empty($rule_summary['full'])):?>
                        <div class="wpshifty-scenario-buttonset">
                              <a href="#" class="wpshifty-btn wpshifty-btn-secondary wpshifty-btn-top-rounded wpshifty-show-more">
                                    <span class="wpshifty-btn-inner">
                                          <?php esc_html_e('More')?>
                                          <svg aria-hidden="true" class="wpshifty-icon-065" focusable="false" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 512"><path fill="currentColor" d="M168 345.941V44c0-6.627-5.373-12-12-12h-56c-6.627 0-12 5.373-12 12v301.941H41.941c-21.382 0-32.09 25.851-16.971 40.971l86.059 86.059c9.373 9.373 24.569 9.373 33.941 0l86.059-86.059c15.119-15.119 4.411-40.971-16.971-40.971H168z"></path></svg>
                                    </span>
                              </a>
                              <a href="#" class="wpshifty-btn wpshifty-btn-secondary wpshifty-btn-top-rounded wpshifty-show-less">
                                    <span class="wpshifty-btn-inner">
                                          <?php esc_html_e('Less')?>
                                          <svg aria-hidden="true" class="wpshifty-icon-065" focusable="false" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 512"><path fill="currentColor" d="M88 166.059V468c0 6.627 5.373 12 12 12h56c6.627 0 12-5.373 12-12V166.059h46.059c21.382 0 32.09-25.851 16.971-40.971l-86.059-86.059c-9.373-9.373-24.569-9.373-33.941 0l-86.059 86.059c-15.119 15.119-4.411 40.971 16.971 40.971H88z"></path></svg>
                                    </span>
                              </a>
                        </div>
                        <?php endif;?>
                  </div>
            </div>
            <div class="wpshifty-box-end"></div>
      </div>
</li>