/**
 * Regex Validation for Gravity Forms - Admin Field Editor
 *   
 * Handles the field editor UI for regex validation settings in the Gravity Forms form builder.
 */
(function($) {
    'use strict';

    // Add setting to supported field types
    window.gfRegexValidation = window.gfRegexValidation || {};
    
    // Initialize when field settings are loaded
    $(document).on('gform_load_field_settings', function(event, field, form) {
        $('#field_regex_pattern').val(field.regexPattern || '');
        $('#field_regex_message').val(field.regexMessage || '');

        // Set preset dropdown
        const presets = window.gfRegexValidation.presets || {};
        let selectedPreset = '';

        for (const [key, preset] of Object.entries(presets)) {
            if (field.regexPattern === preset.pattern) {
                selectedPreset = key;
                break;
            }
        }

        $('#field_regex_preset').val(selectedPreset);
    });

    // Function to set preset values
    window.SetRegexPreset = function(presetKey) {
        if (!presetKey) {
            return;
        }

        const presets = window.gfRegexValidation.presets || {};
        const preset = presets[presetKey];

        if (preset) {
            SetFieldProperty('regexPattern', preset.pattern);
            SetFieldProperty('regexMessage', preset.message);
            $('#field_regex_pattern').val(preset.pattern);
            $('#field_regex_message').val(preset.message);
        }
    };

    // Add settings to field types
    window.gfRegexValidation.fieldTypes = window.gfRegexValidation.fieldTypes || [];
    window.gfRegexValidation.fieldTypes.forEach(function(type) {
        if (typeof fieldSettings[type] !== 'undefined') {
            fieldSettings[type] += ', .regex_validation_setting';
        }
    });

})(jQuery);
