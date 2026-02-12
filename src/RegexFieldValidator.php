<?php

declare(strict_types=1);

namespace ZirkelDesign\GFRegexValidation;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Gravity Forms Regex Field Validator
 *
 * Adds custom regex validation to Gravity Forms fields with preset patterns
 * for common use cases like names, emails, and phone numbers.
 * Provides both server-side (PHP) and client-side (JavaScript) validation.
 */
class RegexFieldValidator
{
    private const DEFAULT_FIELD_TYPES = [
        'text',
        'name',
        'email',
        'phone',
        'website',
        'textarea',
    ];

    /**
     * Get preset regex patterns for common validation scenarios
     *
     * @return array<string, array{label: string, pattern: string, message: string}>
     */
    public static function getPresets(): array
    {
        $presets = [
            'name' => [
                'label' => __('Name (Unicode letters, spaces, hyphens, apostrophes)', 'regex-validation-for-gravity-forms'),
                'pattern' => '/^[\p{L}\s\'-]+$/u',
                'message' => __('Please enter a valid name (letters, spaces, hyphens, and apostrophes only)', 'regex-validation-for-gravity-forms'),
            ],
            'email' => [
                'label' => __('Email Address (RFC 5322 compliant)', 'regex-validation-for-gravity-forms'),
                'pattern' => '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/u',
                'message' => __('Please enter a valid email address', 'regex-validation-for-gravity-forms'),
            ],
            'phone_us' => [
                'label' => __('US Phone Number', 'regex-validation-for-gravity-forms'),
                'pattern' => '/^(\+?1)?[\s.-]?\(?[2-9]\d{2}\)?[\s.-]?\d{3}[\s.-]?\d{4}$/u',
                'message' => __('Please enter a valid US phone number', 'regex-validation-for-gravity-forms'),
            ],
            'phone_international' => [
                'label' => __('International Phone Number', 'regex-validation-for-gravity-forms'),
                'pattern' => '/^\+?[1-9]\d{1,14}$/u',
                'message' => __('Please enter a valid international phone number (E.164 format)', 'regex-validation-for-gravity-forms'),
            ],
            'alphanumeric' => [
                'label' => __('Alphanumeric (Unicode)', 'regex-validation-for-gravity-forms'),
                'pattern' => '/^[\p{L}\p{N}]+$/u',
                'message' => __('Please enter only letters and numbers', 'regex-validation-for-gravity-forms'),
            ],
            'no_special_chars' => [
                'label' => __('No Special Characters (Unicode)', 'regex-validation-for-gravity-forms'),
                'pattern' => '/^[\p{L}\p{N}\s]+$/u',
                'message' => __('Please enter only letters, numbers, and spaces', 'regex-validation-for-gravity-forms'),
            ],
        ];

        /** @var array<string, array{label: string, pattern: string, message: string}> */
        return apply_filters('gf_regex_validation_presets', $presets);
    }

    /**
     * Get supported field types
     *
     * @return array<int, string>
     */
    private function getSupportedFieldTypes(): array
    {
        /** @var array<int, string> */
        return apply_filters('gf_regex_validation_field_types', self::DEFAULT_FIELD_TYPES);
    }

    public function __construct()
    {
        $this->registerHooks();
    }

    private function registerHooks(): void
    {
        add_action('gform_field_standard_settings', [$this, 'addRegexSettings'], 10, 2);
        add_action('gform_editor_js', [$this, 'editorScript']);
        add_filter('gform_tooltips', [$this, 'addRegexTooltip']);
        add_filter('gform_field_validation', [$this, 'validateRegex'], 10, 4);
        add_filter('gform_pre_render', [$this, 'enqueueClientValidation']);
    }

    /**
     * Add regex settings to field editor
     */
    public function addRegexSettings(int $position, int $formId): void
    {
        if ($position !== 50) {
            return;
        }

        ?>
        <li class="regex_validation_setting field_setting">
            <label for="field_regex_preset" class="section_label">
                <?php esc_html_e('Regex Validation', 'regex-validation-for-gravity-forms'); ?>
                <?php gform_tooltip('form_field_regex_validation'); ?>
            </label>

            <select id="field_regex_preset" onchange="SetRegexPreset(this.value);">
                <option value=""><?php esc_html_e('Custom Regex', 'regex-validation-for-gravity-forms'); ?></option>
                <?php foreach (self::getPresets() as $key => $preset) { ?>
                    <option value="<?php echo esc_attr($key); ?>">
                        <?php echo esc_html($preset['label']); ?>
                    </option>
                <?php } ?>
            </select>

            <label for="field_regex_pattern" style="margin-top: 10px; display: block;">
                <?php esc_html_e('Regex Pattern', 'regex-validation-for-gravity-forms'); ?>
            </label>
            <input
                type="text"
                id="field_regex_pattern"
                class="fieldwidth-3"
                placeholder="/^[\p{L}\s]+$/u"
                onkeyup="SetFieldProperty('regexPattern', this.value);"
            />
            <small style="display: block; margin-top: 5px; color: #666;">
                <?php esc_html_e('Use Unicode flag (/u) for international character support', 'regex-validation-for-gravity-forms'); ?>
            </small>

            <label for="field_regex_message" style="margin-top: 10px; display: block;">
                <?php esc_html_e('Validation Message', 'regex-validation-for-gravity-forms'); ?>
            </label>
            <input
                type="text"
                id="field_regex_message"
                class="fieldwidth-3"
                placeholder="<?php esc_attr_e('Please enter a valid value', 'regex-validation-for-gravity-forms'); ?>"
                onkeyup="SetFieldProperty('regexMessage', this.value);"
            />
        </li>
        <?php
    }

    /**
     * Add JavaScript for field editor
     */
    public function editorScript(): void
    {
        $presets = self::getPresets();
        $fieldTypes = $this->getSupportedFieldTypes();
        ?>
        <script type='text/javascript'>
            // Add setting to supported field types
            <?php foreach ($fieldTypes as $type) { ?>
            if (typeof fieldSettings.<?php echo esc_js($type); ?> !== 'undefined') {
                fieldSettings.<?php echo esc_js($type); ?> += ', .regex_validation_setting';
            }
            <?php } ?>

            // Bind to the load field settings event
            jQuery(document).on('gform_load_field_settings', function(event, field, form) {
                jQuery('#field_regex_pattern').val(field.regexPattern || '');
                jQuery('#field_regex_message').val(field.regexMessage || '');

                // Set preset dropdown
                const presets = <?php echo wp_json_encode($presets); ?>;
                let selectedPreset = '';

                for (const [key, preset] of Object.entries(presets)) {
                    if (field.regexPattern === preset.pattern) {
                        selectedPreset = key;
                        break;
                    }
                }

                jQuery('#field_regex_preset').val(selectedPreset);
            });

            // Function to set preset values
            function SetRegexPreset(presetKey) {
                if (!presetKey) {
                    return;
                }

                const presets = <?php echo wp_json_encode($presets); ?>;
                const preset = presets[presetKey];

                if (preset) {
                    SetFieldProperty('regexPattern', preset.pattern);
                    SetFieldProperty('regexMessage', preset.message);
                    jQuery('#field_regex_pattern').val(preset.pattern);
                    jQuery('#field_regex_message').val(preset.message);
                }
            }
        </script>
        <?php
    }

    /**
     * Add tooltip for regex validation
     *
     * @param array<string, string> $tooltips
     * @return array<string, string>
     */
    public function addRegexTooltip(array $tooltips): array
    {
        $tooltips['form_field_regex_validation'] = sprintf(
            '<h6>%s</h6>%s',
            esc_html__('Regex Validation', 'regex-validation-for-gravity-forms'),
            esc_html__('Add custom regular expression validation to this field. Select a preset or enter your own regex pattern with Unicode support.', 'regex-validation-for-gravity-forms')
        );

        return $tooltips;
    }

    /**
     * Validate field against regex pattern (server-side)
     *
     * @param array{is_valid: bool, message: string} $result
     * @param array<string, mixed> $form
     * @return array{is_valid: bool, message: string}
     */
    public function validateRegex(array $result, mixed $value, array $form, object $field): array
    {
        if (! $result['is_valid'] || ! $this->shouldValidateField($field)) {
            return $result;
        }

        $pattern = $field->regexPattern ?? '';

        if (empty($pattern)) {
            return $result;
        }

        if (! self::isValidRegex($pattern)) {
            _doing_it_wrong(
                __METHOD__,
                sprintf(
                    /* translators: 1: regex pattern, 2: field ID */
                    esc_html__('Invalid regex pattern "%1$s" on field ID %2$d.', 'regex-validation-for-gravity-forms'),
                    esc_html($pattern),
                    absint($field->id)
                ),
                '1.0.0'
            );

            return $result;
        }

        $valuesToTest = is_array($value)
            ? array_filter(array_map('trim', $value), fn (string $v) => $v !== '')
            : [(string) $value];

        foreach ($valuesToTest as $singleValue) {
            if ($singleValue !== '' && ! preg_match($pattern, $singleValue)) {
                $result['is_valid'] = false;
                $result['message'] = ! empty($field->regexMessage)
                    ? $field->regexMessage
                    : esc_html__('Please enter a valid value', 'regex-validation-for-gravity-forms');

                break;
            }
        }

        return $result;
    }

    /**
     * Enqueue client-side regex validation script
     *
     * @param array<string, mixed> $form
     * @return array<string, mixed>
     */
    public function enqueueClientValidation(array $form): array
    {
        $fieldsWithRegex = [];

        foreach ($form['fields'] as $field) {
            if (! in_array($field->type, $this->getSupportedFieldTypes(), true)) {
                continue;
            }

            $pattern = $field->regexPattern ?? '';

            if (empty($pattern)) {
                continue;
            }

            $jsPattern = self::phpRegexToJs($pattern);

            if ($jsPattern === null) {
                continue;
            }

            $fieldsWithRegex[] = [
                'id' => $field->id,
                'type' => $field->type,
                'pattern' => $jsPattern,
                'message' => ! empty($field->regexMessage)
                    ? $field->regexMessage
                    : esc_html__('Please enter a valid value', 'regex-validation-for-gravity-forms'),
            ];
        }

        if (empty($fieldsWithRegex)) {
            return $form;
        }

        $formId = $form['id'];

        add_action('gform_register_init_scripts', function () use ($formId, $fieldsWithRegex): void {
            ob_start();
            ?>
            var fields = <?php echo wp_json_encode($fieldsWithRegex); ?>;

            fields.forEach(function(fieldConfig) {
                var fieldWrapper = document.querySelector('#field_<?php echo absint($formId); ?>_' + fieldConfig.id);
                if (!fieldWrapper) return;

                var inputs = fieldWrapper.querySelectorAll('input:not([type=hidden])');
                var regex;

                try {
                    regex = new RegExp(fieldConfig.pattern, 'u');
                } catch (e) {
                    return;
                }

                inputs.forEach(function(input) {
                    input.addEventListener('change', function() {
                        var wrapper = this.closest('.gfield');
                        var msgId = 'regex_validation_message_' + fieldConfig.id + '_' + this.id;
                        var existingMsg = document.getElementById(msgId);

                        if (this.value === '' || regex.test(this.value)) {
                            if (existingMsg) existingMsg.remove();
                            if (wrapper && !wrapper.querySelector('.validation_message')) {
                                wrapper.classList.remove('gfield_error');
                            }
                        } else {
                            if (!existingMsg) {
                                var msg = document.createElement('div');
                                msg.id = msgId;
                                msg.className = 'validation_message gfield_description';
                                msg.setAttribute('role', 'alert');
                                msg.textContent = fieldConfig.message;
                                this.parentNode.insertBefore(msg, this.nextSibling);
                            }
                            if (wrapper) wrapper.classList.add('gfield_error');
                        }
                    });
                });
            });
            <?php
            $script = ob_get_clean();

            \GFFormDisplay::add_init_script($formId, "regex_validation_{$formId}", \GFFormDisplay::ON_PAGE_RENDER, $script);
        });

        return $form;
    }

    private function shouldValidateField(object $field): bool
    {
        if (! in_array($field->type, $this->getSupportedFieldTypes(), true)) {
            return false;
        }

        if (empty($field->regexPattern)) {
            return false;
        }

        return true;
    }

    /**
     * Validate regex pattern
     */
    public static function isValidRegex(string $pattern): bool
    {
        return @preg_match($pattern, '') !== false;
    }

    /**
     * Convert PHP regex to JavaScript-compatible pattern string
     *
     * Strips delimiters and flags. Returns null if the pattern can't be safely converted.
     */
    public static function phpRegexToJs(string $phpPattern): ?string
    {
        if (strlen($phpPattern) < 2) {
            return null;
        }

        $delimiter = $phpPattern[0];

        $lastDelimiterPos = strrpos($phpPattern, $delimiter, 1);

        if ($lastDelimiterPos === false) {
            return null;
        }

        return substr($phpPattern, 1, $lastDelimiterPos - 1);
    }
}
