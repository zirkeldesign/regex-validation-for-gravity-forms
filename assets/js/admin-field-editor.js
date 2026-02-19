/**
 * Regex Validation for Gravity Forms - Admin Field Editor
 *   
 * Handles the field editor UI for regex validation settings in the Gravity Forms form builder.
 */
(function($) {
    'use strict';

    console.log('GF Regex Validation: Script loaded');
    
    // Wait for DOM to be ready before modifying fieldSettings
    $(document).ready(function() {
        console.log('GF Regex Validation: DOM ready');
        
        // Add setting to supported field types
        window.gfRegexValidation = window.gfRegexValidation || {};
        
        console.log('GF Regex Validation: Data available:', window.gfRegexValidation);
        console.log('GF Regex Validation: fieldSettings available:', typeof fieldSettings !== 'undefined');
        
        // Add settings to field types
        const fieldTypes = window.gfRegexValidation.fieldTypes || [];
        console.log('GF Regex Validation: Field types to configure:', fieldTypes);
        
        fieldTypes.forEach(function(type) {
            if (typeof fieldSettings !== 'undefined' && typeof fieldSettings[type] !== 'undefined') {
                console.log('GF Regex Validation: Adding settings to field type:', type);
                fieldSettings[type] += ', .regex_validation_setting';
            } else {
                console.log('GF Regex Validation: Field type not found or fieldSettings undefined:', type);
            }
        });
        
        console.log('GF Regex Validation: fieldSettings after modification:', typeof fieldSettings !== 'undefined' ? fieldSettings : 'undefined');
    });
    
    // Initialize when field settings are loaded
    $(document).on('gform_load_field_settings', function(event, field, form) {
        console.log('GF Regex Validation: gform_load_field_settings triggered for field:', field);
        
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
        console.log('GF Regex Validation: SetRegexPreset called with:', presetKey);
        
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

})(jQuery);
