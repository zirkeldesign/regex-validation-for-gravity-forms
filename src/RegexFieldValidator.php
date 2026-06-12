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
        'address',
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
        // Register scripts first on init
        add_action('init', [$this, 'registerScripts']);

        // Enqueue on admin pages
        add_action('admin_enqueue_scripts', [$this, 'enqueueEditorAssets'], 20);

        // Add to Gravity Forms noconflict mode whitelist
        add_filter('gform_noconflict_scripts', [$this, 'addNoconflictScripts']);

        add_action('gform_field_standard_settings', [$this, 'addRegexSettings'], 10, 2);
        add_filter('gform_tooltips', [$this, 'addRegexTooltip']);
        add_filter('gform_field_validation', [$this, 'validateRegex'], 10, 4);
        add_filter('gform_pre_render', [$this, 'enqueueClientValidation']);
    }

    /**
     * Register scripts and styles early
     */
    public function registerScripts(): void
    {
        // Ensure Gravity Forms is loaded before registering scripts
        if (! class_exists('GFCommon')) {
            return;
        }

        // Register admin field editor script
        // Depend on gform_form_editor to ensure fieldSettings is available
        $script_suffix = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';
        wp_register_script(
            'gf-regex-validation-admin',
            \GF_REGEX_VALIDATION_URL."dist/js/admin-field-editor{$script_suffix}.js",
            ['jquery', 'gform_form_editor'],
            \GF_REGEX_VALIDATION_VERSION,
            true
        );
    }

    /**
     * Add script to Gravity Forms noconflict mode whitelist
     *
     * Gravity Forms uses noconflict mode to prevent plugin conflicts.
     * We need to whitelist our script so it loads on GF admin pages.
     *
     * @param  array<int, string>  $scripts
     * @return array<int, string>
     */
    public function addNoconflictScripts(array $scripts): array
    {
        $scripts[] = 'gf-regex-validation-admin';

        return $scripts;
    }

    /**
     * Add regex settings to field editor
     */
    public function addRegexSettings(int $position, int $formId): void
    {
        if ($position !== 50) {
            return;
        }

        $compoundPresets = CompoundFieldPresets::getCompoundPresets();
        $inputPresets = CompoundFieldPresets::getInputPresets();
        $nameInputs = CompoundFieldPresets::getNameInputStructure();
        $addressInputs = CompoundFieldPresets::getAddressInputStructure();

        ?>
        <li class="regex_validation_setting field_setting">
            <label for="field_regex_mode" class="section_label">
                <?php esc_html_e('Regex Validation', 'regex-validation-for-gravity-forms'); ?>
                <?php gform_tooltip('form_field_regex_validation'); ?>
            </label>

            <!-- Mode Toggle -->
            <div class="regex_mode_toggle" style="margin-bottom: 15px;">
                <input type="radio" id="regex_mode_simple" name="regex_mode" value="simple" checked onclick="SetRegexMode('simple');" />
                <label for="regex_mode_simple" style="display: inline; margin-right: 15px;">
                    <?php esc_html_e('Simple Mode', 'regex-validation-for-gravity-forms'); ?>
                </label>
                
                <input type="radio" id="regex_mode_advanced" name="regex_mode" value="advanced" onclick="SetRegexMode('advanced');" />
                <label for="regex_mode_advanced" style="display: inline;">
                    <?php esc_html_e('Advanced Mode (Per-Input)', 'regex-validation-for-gravity-forms'); ?>
                </label>
            </div>

            <!-- Simple Mode Container -->
            <div id="regex_simple_mode_container" style="display: block;">
                <select id="field_regex_preset" onchange="SetRegexPreset(this.value);">
                    <option value=""><?php esc_html_e('Custom Regex', 'regex-validation-for-gravity-forms'); ?></option>
                    <optgroup label="<?php esc_attr_e('Single Field Presets', 'regex-validation-for-gravity-forms'); ?>">
                        <?php foreach (self::getPresets() as $key => $preset) { ?>
                            <option value="<?php echo esc_attr($key); ?>">
                                <?php echo esc_html($preset['label']); ?>
                            </option>
                        <?php } ?>
                    </optgroup>
                    <optgroup label="<?php esc_attr_e('Compound Field Presets (All Inputs)', 'regex-validation-for-gravity-forms'); ?>">
                        <?php foreach ($compoundPresets as $key => $preset) { ?>
                            <option value="compound_<?php echo esc_attr($key); ?>" data-type="<?php echo esc_attr($preset['type']); ?>">
                                <?php echo esc_html($preset['label']); ?>
                            </option>
                        <?php } ?>
                    </optgroup>
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
            </div>

            <!-- Advanced Mode Container -->
            <div id="regex_advanced_mode_container" style="display: none;">
                <p style="margin: 10px 0; color: #666;">
                    <?php esc_html_e('Configure different validation patterns for each input in this field.', 'regex-validation-for-gravity-forms'); ?>
                </p>

                <!-- Compound Field Preset Selector for Advanced Mode -->
                <div style="margin-bottom: 15px;">
                    <label for="field_regex_advanced_preset" style="display: block; margin-bottom: 5px;">
                        <?php esc_html_e('Quick Start Preset', 'regex-validation-for-gravity-forms'); ?>
                    </label>
                    <select id="field_regex_advanced_preset" onchange="ApplyAdvancedPreset(this.value);">
                        <option value=""><?php esc_html_e('-- Select a preset --', 'regex-validation-for-gravity-forms'); ?></option>
                        <?php foreach ($compoundPresets as $key => $preset) { ?>
                            <option value="<?php echo esc_attr($key); ?>" data-type="<?php echo esc_attr($preset['type']); ?>">
                                <?php echo esc_html($preset['label']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <!-- Dynamic Input Configuration Area -->
                <div id="regex_advanced_inputs_container">
                    <!-- This will be populated dynamically by JavaScript based on field type -->
                </div>

                <button type="button" class="button" onclick="AddAdvancedInput();" style="margin-top: 10px;">
                    <?php esc_html_e('+ Add Input Pattern', 'regex-validation-for-gravity-forms'); ?>
                </button>
            </div>

            <!-- Hidden field to store advanced patterns as JSON -->
            <input type="hidden" id="field_regex_patterns" value="" />
        </li>

        <script type="text/javascript">
            // Store presets and input structures for JavaScript
            window.gfRegexValidation = window.gfRegexValidation || {};
            window.gfRegexValidation.compoundPresets = <?php echo wp_json_encode($compoundPresets); ?>;
            window.gfRegexValidation.inputPresets = <?php echo wp_json_encode($inputPresets); ?>;
            window.gfRegexValidation.nameInputs = <?php echo wp_json_encode($nameInputs); ?>;
            window.gfRegexValidation.addressInputs = <?php echo wp_json_encode($addressInputs); ?>;
        </script>
        <?php
    }

    /**
     * Enqueue admin field editor assets
     */
    public function enqueueEditorAssets(string $hook): void
    {
        // Only load on Gravity Forms form editor pages
        if ($hook !== 'toplevel_page_gf_edit_forms' && $hook !== 'forms_page_gf_edit_forms') {
            return;
        }

        // Check if we're on the form editor (not form list)
        // We only check if 'id' exists to determine page context for script loading.
        // The value is not used or processed - Gravity Forms handles all validation/nonces.
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (! isset($_GET['id']) || empty($_GET['id'])) {
            return;
        }

        $presets = self::getPresets();
        $fieldTypes = $this->getSupportedFieldTypes();

        // Enqueue the already-registered script
        wp_enqueue_script('gf-regex-validation-admin');

        // Add inline data before the script using IIFE to avoid global scope pollution
        wp_add_inline_script(
            'gf-regex-validation-admin',
            sprintf(
                '(function() {
                    const presets = %s;
                    const fieldTypes = %s;
                    window.gfRegexValidation = window.gfRegexValidation || {};
                    window.gfRegexValidation.presets = presets;
                    window.gfRegexValidation.fieldTypes = fieldTypes;
                })();',
                wp_json_encode($presets),
                wp_json_encode($fieldTypes)
            ),
            'before'
        );
    }

    /**
     * Add tooltip for regex validation
     *
     * @param  array<string, string>  $tooltips
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
     * Supports both simple mode (single pattern for all inputs) and advanced mode
     * (per-input patterns for compound fields like Name and Address).
     *
     * @param  array{is_valid: bool, message: string}  $result
     * @param  array<string, mixed>  $form
     * @return array{is_valid: bool, message: string}
     */
    public function validateRegex(array $result, mixed $value, array $form, object $field): array
    {
        if (! $result['is_valid'] || ! $this->shouldValidateField($field)) {
            return $result;
        }

        // Check if using advanced mode (per-input patterns)
        $advancedPatterns = $field->regexPatterns ?? null;

        if (! empty($advancedPatterns) && is_array($advancedPatterns)) {
            return $this->validateAdvancedMode($result, $value, $form, $field, $advancedPatterns);
        }

        // Fall back to simple mode (single pattern for all inputs)
        return $this->validateSimpleMode($result, $value, $form, $field);
    }

    /**
     * Validate using simple mode (single pattern for all inputs)
     *
     * @param  array{is_valid: bool, message: string}  $result
     * @param  array<string, mixed>  $form
     * @return array{is_valid: bool, message: string}
     */
    private function validateSimpleMode(array $result, mixed $value, array $form, object $field): array
    {
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
     * Validate using advanced mode (per-input patterns for compound fields)
     *
     * @param  array{is_valid: bool, message: string}  $result
     * @param  array<string, mixed>  $form
     * @param  array<string, array{pattern: string, message: string}>  $patterns
     * @return array{is_valid: bool, message: string}
     */
    private function validateAdvancedMode(array $result, mixed $value, array $form, object $field, array $patterns): array
    {
        // For non-array values (shouldn't happen with compound fields, but handle gracefully)
        if (! is_array($value)) {
            return $result;
        }

        $hasError = false;
        $errorMessage = '';

        // Iterate through each configured input pattern
        foreach ($patterns as $inputNumber => $config) {
            // Get the input value using the full input ID (field.input)
            $inputId = $field->id.'.'.$inputNumber;
            $inputValue = rgar($value, $inputId);

            // Skip validation if:
            // 1. Input is hidden
            // 2. Input value is empty (we don't validate empty values)
            if ($this->isInputHidden($field, $inputNumber) || empty($inputValue)) {
                continue;
            }

            $pattern = $config['pattern'];
            $message = $config['message'];

            // Validate the pattern syntax
            if (empty($pattern) || ! self::isValidRegex($pattern)) {
                _doing_it_wrong(
                    __METHOD__,
                    sprintf(
                        /* translators: 1: input number, 2: field ID */
                        esc_html__('Invalid regex pattern for input %1$s on field ID %2$d.', 'regex-validation-for-gravity-forms'),
                        esc_html($inputNumber),
                        absint($field->id)
                    ),
                    '1.0.0'
                );

                continue;
            }

            // Trim and validate the input value
            $inputValue = trim($inputValue);

            if ($inputValue !== '' && ! preg_match($pattern, $inputValue)) {
                $hasError = true;
                $errorMessage = $message;

                // Mark this specific input as invalid (GF 2.5.10+)
                if (method_exists($field, 'set_input_validation_state')) {
                    $field->set_input_validation_state($inputNumber, false);
                }

                // Stop on first error
                break;
            }
        }

        if ($hasError) {
            $result['is_valid'] = false;
            $result['message'] = $errorMessage;
        }

        return $result;
    }

    /**
     * Check if a specific input within a compound field is hidden
     *
     * @param  object  $field  The field object
     * @param  string  $inputNumber  The input number (e.g., '1', '3', '5')
     */
    private function isInputHidden(object $field, string $inputNumber): bool
    {
        if (! isset($field->inputs) || ! is_array($field->inputs)) {
            return false;
        }

        foreach ($field->inputs as $input) {
            // Extract the input number from the full ID (e.g., '5.1' -> '1')
            $inputId = (string) ($input['id'] ?? '');
            $parts = explode('.', $inputId);
            $currentInputNumber = end($parts);

            if ($currentInputNumber === $inputNumber) {
                return ! empty($input['isHidden']);
            }
        }

        return false;
    }

    /**
     * Enqueue client-side regex validation script
     *
     * Supports both simple mode (single pattern) and advanced mode (per-input patterns).
     *
     * @param  array<string, mixed>  $form
     * @return array<string, mixed>
     */
    public function enqueueClientValidation(array $form): array
    {
        $fieldsWithRegex = [];

        foreach ($form['fields'] as $field) {
            if (! in_array($field->type, $this->getSupportedFieldTypes(), true)) {
                continue;
            }

            // Check for advanced mode (per-input patterns)
            $advancedPatterns = $field->regexPatterns ?? null;

            if (! empty($advancedPatterns) && is_array($advancedPatterns)) {
                $fieldConfig = $this->prepareAdvancedFieldConfig($field, $advancedPatterns);
                if ($fieldConfig !== null) {
                    $fieldsWithRegex[] = $fieldConfig;
                }

                continue;
            }

            // Fall back to simple mode
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
                'mode' => 'simple',
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

                if (fieldConfig.mode === 'advanced') {
                    // Advanced mode: per-input validation
                    fieldConfig.inputs.forEach(function(inputConfig) {
                        var input = document.getElementById('input_<?php echo absint($formId); ?>_' + fieldConfig.id + '_' + inputConfig.inputNumber);
                        if (!input) return;

                        var regex;
                        try {
                            regex = new RegExp(inputConfig.pattern, 'u');
                        } catch (e) {
                            return;
                        }

                        input.addEventListener('change', function() {
                            var wrapper = this.closest('.gfield');
                            var msgId = 'regex_validation_message_' + fieldConfig.id + '_' + inputConfig.inputNumber;
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
                                    msg.textContent = inputConfig.message;
                                    this.parentNode.insertBefore(msg, this.nextSibling);
                                }
                                if (wrapper) wrapper.classList.add('gfield_error');
                            }
                        });
                    });
                } else {
                    // Simple mode: all inputs use the same pattern
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
                }
            });
            <?php
            $script = ob_get_clean();

            \GFFormDisplay::add_init_script($formId, "regex_validation_{$formId}", \GFFormDisplay::ON_PAGE_RENDER, $script);
        });

        return $form;
    }

    /**
     * Prepare field configuration for advanced mode client-side validation
     *
     * @param  array<string, array{pattern: string, message: string}>  $patterns
     * @return array<string, mixed>|null
     */
    private function prepareAdvancedFieldConfig(object $field, array $patterns): ?array
    {
        $inputs = [];

        foreach ($patterns as $inputNumber => $config) {
            // Skip if input is hidden
            if ($this->isInputHidden($field, $inputNumber)) {
                continue;
            }

            $pattern = $config['pattern'];
            $message = $config['message'];

            $jsPattern = self::phpRegexToJs($pattern);

            if ($jsPattern === null) {
                continue;
            }

            $inputs[] = [
                'inputNumber' => $inputNumber,
                'pattern' => $jsPattern,
                'message' => $message,
            ];
        }

        if (empty($inputs)) {
            return null;
        }

        return [
            'id' => $field->id,
            'type' => $field->type,
            'mode' => 'advanced',
            'inputs' => $inputs,
        ];
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
