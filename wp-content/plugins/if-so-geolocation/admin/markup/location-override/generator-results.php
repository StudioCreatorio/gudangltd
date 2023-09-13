<div class="shortcode-generator-results">
    <div class="shortcode-error"></div>

    <div class="shortcode-preview">
        <label>Preview</label>
        <div class="preview-container">
            <label>Your live preview will appear here</label>
        </div>
    </div>

    <label>Shortcode</label>
    <textarea class="result-shortcode" placeholder="Your generated shortcode will appear here"></textarea>

    <button class="copy-all-button have-copy-indicator button-primary" alt="copy" onclick="
        event.preventDefault()
        locationOverrideGenerator.copyShortcode()
    "><span>🗊</span>Copy Shortcode</button>

    <p class="instructions">
        Paste the shortcode anywhere on your site to display the self-selction form.
        <a class="instructions-link" target="_blank" href="https://www.if-so.com/dynamic-select-form/manual-user-location-selection/">
            Learn more &gt;
        </a>
    </p>
</div>

<script>
    document.querySelectorAll('.have-copy-indicator').forEach(el => {
        el.addEventListener('click', function() {
            el.classList.add('active')
            setTimeout(function() { el.classList.remove('active') } , 1000)
        })
    })
</script>
