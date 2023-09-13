<!-- NUMBER OF VIEWS UI BEGIN !-->
<div class="ifso-form-group">
    <input class="page-visit-autocomplete ifso-input-autocomplete form-control referrer-custom <?php echo (isset($rule['trigger_type']) && $rule['trigger_type'] == 'numberOfViews') ? 'show-selection' : '';?>" value="<?php if(isset($rule['numberofviews-value']) && !empty($rule['numberofviews-value']))  echo $rule['numberofviews-value']; ?>" type="int" name="repeater[<?php echo $current_version_index; ?>][numberofviews-value]"  placeholder="<?php _e('Number of Views', 'if-so'); ?>" data-field="numberofviews-value">
</div>

<!-- NUMBER OF VIEWS UI END !-->


