<?php $locaton_generator_url = admin_url("?page=wpcdd_admin_location_generator&ui_type=adder");?>

<form class="shortcode-generator location-override-generator" autocomplete="off" method="post" action="">
    <input type="text" autocomplete="false" style="display:none;" disabled="">

    <h1>Location Override Form Generator</h1>
    <p class="header-description">
        Allow users to select a different location than the one detected based on their IP.
        Any geotargeted content served by If-So will be displayed based on the user selection.
        <a href="https://www.if-so.com/dynamic-select-form/manual-user-location-selection/">
            Learn more
        </a>
    </p>

    <fieldset name="type">
        <legend>Form Type</legend>
        <div class="type-container">
            <div class="type-option selected" data-value="select">
                Select <?php include 'select-icon.php';?>
            </div>
            <div class="type-option" data-value="radio">
                Radio <?php include 'radio-icon.php';?>
            </div>
        </div>
        <input type="text" name="type" style="display: none;">

        <fieldset name="orientation" disabled>
            <label>Radio buttons orientation:</label>
            <div>
                <input type="radio" name="orientation" value="vertical" checked>
                <label>Vertical</label>
            </div>
            <div>
                <input type="radio" name="orientation" value="">
                <label>Horizontal</label>
            </div>
        </fieldset>
    </fieldset>

    <fieldset name="options">
        <legend>Locations</legend>
        <p class="description">
            Select the options you want to appear in the override selction form.
        </p>

        <div class="dissonant-geo-types-error" style="display: none;">
            <p class="red-noticebox">Your location-override selection form contains multiple location types (country, state, or city). Chosen locations must be of the same type.</p>
        </div>
            
        <a  class="add-locations-button"
            href='<?php echo esc_url($locaton_generator_url); ?>'
            title='Open Location Finder Helper' rel='permalink'
            onclick="window.open('<?php echo esc_url($locaton_generator_url); ?>', 'newwindow', 'width=800,height=600'); return false;"
        ><span>+</span><span>Add Locations</span></a>

        <input type="text" name="options" style="display: none; visibility: hidden;">

        <div class="drag-table-wrapper">
            <div class="drag-table-content">
                <div class="drag-table-header">
                    <span></span>
                    <span>LOCATION</span>
                    <span>DISPLAY NAME</span>
                    <span>REDIRECTION URL</span>
                    <span></span>
                </div>
                <div class="drag-table"></div>
            </div>
        </div>
            
        <fieldset name="default-option">
            <label>Default selection field label:</label>
            <input type="text" name="default-option" value="Your Location">
        </fieldset>
    </fieldset>

    <fieldset name="autodetect-location">
        <legend>Auto-detect location:</legend>
        <p class="description">If the user's location is among the selection field options, it will be set as the default label.</p>
        <input type="checkbox" name="autodetect-location">
        <label for="autodetect-location">Auto-detect location</label>
    </fieldset>

    <fieldset name="button" onchange="locationOverrideGenerator.toggleFieldsetsByMultipleRadio('button-value', [['button', 'value']])">
        <legend>Button</legend>
        <p class="description">
            Choose if you want to submit the user selection automaticaly after the user selects a location or only after clicking a button.
        </p>
        <div>
            <input type="radio" name="button" value="" checked="">
            <label>Do not include a button</label>
        </div>
        <div>
            <input type="radio" name="button" value="value">
            <label>Include a button</label>
        </div>
        <fieldset name="button-value" onchange="event.preventDefault()" disabled>
            <label>Button text:</label>
            <input type="text" name="button-value" value="Submit">
        </fieldset>
    </fieldset>

    <input type="hidden" name="geo-type">

    <fieldset name="redirect" onchange="
        let checkbox = document.querySelector(`input[name='redirect']`)
        let field = document.querySelector(`fieldset[name='redirect-value']`)
        let table = document.querySelector(`.drag-table-wrapper`)
        field.disabled = !checkbox.checked
        table.classList.toggle('redirects', checkbox.checked)
    ">
        <legend>Redirect After Submission</legend>
        <p class="description">
            Check this option if you would like to direct users to a different page after their selection. Leave unchecked if you do not want the page where the form is embedded to be reloaded.
        </p>

        <input type="checkbox" name="redirect">
        <label for="redirect">Redirect After Submission</label>

        <fieldset name="redirect-value" disabled>
            <p class="description" style="border-bottom:none;">
                Specify the redirection URL (You can override it with unique "per-option" redirection URLs for each location in the table above).
            </p>
            <input type="text" name="redirect-value" value="">
        </fieldset>
    </fieldset>

    <fieldset name="ajax-render">
        <legend>Page Caching Compatiblity</legend>
        <p class="description">
            Check this box if you are using a caching plugin.
            The form will be loaded using Ajax to ensure that the user's selection is properly set.
        </p>
        <input type="checkbox" name="ajax-render" checked>
        <label for="ajax-render">Page Caching Compatiblity (Ajax loading)</label>
    </fieldset>

    <fieldset name="show-flags">
        <legend>Show Country Flag</legend>
        <p class="description">
            The country flag will be displayed alongside the location name in the selection field (<a href="https://www.if-so.com/faq-items/countries-flag-do-not-appear-on-my-location-override-form/?utm_source=Plugin&utm_medium=GeoOverrideGen&utm_campaign=geolocation_ext" target="_blank">on browsers that support emoji display</a>).
        </p>
        <input type="checkbox" name="show-flags" checked>
        <label for="show-flags">Show country flag</label>
    </fieldset>

    <fieldset name="classname">
        <legend>Class</legend>
        <p class="description">
            Optional - assign a CSS Class
        </p>
        <input type="text" name="classname" value="">
    </fieldset>

</form>