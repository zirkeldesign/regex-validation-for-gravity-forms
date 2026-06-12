<?php

declare(strict_types=1);

namespace ZirkelDesign\GFRegexValidation;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Compound Field Presets
 *
 * Provides preset regex patterns for multi-input fields like Name and Address.
 * Includes patterns for individual inputs that can be configured separately.
 */
class CompoundFieldPresets
{
    /**
     * Get presets for compound fields (Name and Address)
     * These apply patterns to all relevant inputs at once
     *
     * @return array<string, array{label: string, type: string, patterns: array<string, array{pattern: string, message: string}>}>
     */
    public static function getCompoundPresets(): array
    {
        // Define reusable error messages
        $msgInvalidName = __('Invalid name', 'regex-validation-for-gravity-forms');
        $msgInvalidStreet = __('Invalid street address', 'regex-validation-for-gravity-forms');
        $msgInvalidCity = __('Invalid city', 'regex-validation-for-gravity-forms');
        $msgInvalidPostal = __('Invalid postal code', 'regex-validation-for-gravity-forms');
        $msgInvalidState = __('Invalid state code', 'regex-validation-for-gravity-forms');

        $presets = [
            // Name Field Presets
            'name_standard' => [
                'label' => __('Name - Standard', 'regex-validation-for-gravity-forms'),
                'type' => 'name',
                'patterns' => [
                    '3' => [ // First Name
                        'pattern' => '/^[\p{L}\s\'-]+$/u',
                        'message' => $msgInvalidName,
                    ],
                    '4' => [ // Middle Name
                        'pattern' => '/^[\p{L}\s\'-]+$/u',
                        'message' => $msgInvalidName,
                    ],
                    '6' => [ // Last Name
                        'pattern' => '/^[\p{L}\s\'-]+$/u',
                        'message' => $msgInvalidName,
                    ],
                ],
            ],
            'name_with_title' => [
                'label' => __('Name - With Title', 'regex-validation-for-gravity-forms'),
                'type' => 'name',
                'patterns' => [
                    '2' => [ // Prefix
                        'pattern' => '/^(Mr\.?|Mrs\.?|Ms\.?|Miss|Dr\.?|Prof\.?|Rev\.?)$/i',
                        'message' => __('Invalid title', 'regex-validation-for-gravity-forms'),
                    ],
                    '3' => [ // First Name
                        'pattern' => '/^[\p{L}\s\'-]+$/u',
                        'message' => $msgInvalidName,
                    ],
                    '6' => [ // Last Name
                        'pattern' => '/^[\p{L}\s\'-]+$/u',
                        'message' => $msgInvalidName,
                    ],
                ],
            ],

            // Address Field Presets
            'address_us' => [
                'label' => __('Address - US Complete', 'regex-validation-for-gravity-forms'),
                'type' => 'address',
                'patterns' => [
                    '1' => [ // Street Address
                        'pattern' => '/^[\p{L}\d\s\'-,\.#]+$/u',
                        'message' => $msgInvalidStreet,
                    ],
                    '2' => [ // Address Line 2
                        'pattern' => '/^[\p{L}\d\s\'-,\.#]*$/u',
                        'message' => $msgInvalidStreet,
                    ],
                    '3' => [ // City
                        'pattern' => '/^[\p{L}\s\'-]+$/u',
                        'message' => $msgInvalidCity,
                    ],
                    '4' => [ // State
                        'pattern' => '/^[A-Z]{2}$/u',
                        'message' => $msgInvalidState,
                    ],
                    '5' => [ // ZIP Code
                        'pattern' => '/^\d{5}(-\d{4})?$/u',
                        'message' => __('Invalid ZIP code', 'regex-validation-for-gravity-forms'),
                    ],
                ],
            ],
            'address_us_basic' => [
                'label' => __('Address - US Basic', 'regex-validation-for-gravity-forms'),
                'type' => 'address',
                'patterns' => [
                    '1' => [ // Street Address
                        'pattern' => '/^[\p{L}\d\s\'-,\.#]+$/u',
                        'message' => $msgInvalidStreet,
                    ],
                    '3' => [ // City
                        'pattern' => '/^[\p{L}\s\'-]+$/u',
                        'message' => $msgInvalidCity,
                    ],
                    '4' => [ // State
                        'pattern' => '/^[A-Z]{2}$/u',
                        'message' => $msgInvalidState,
                    ],
                    '5' => [ // ZIP Code
                        'pattern' => '/^\d{5}(-\d{4})?$/u',
                        'message' => __('Invalid ZIP code', 'regex-validation-for-gravity-forms'),
                    ],
                ],
            ],
            'address_german' => [
                'label' => __('Address - German', 'regex-validation-for-gravity-forms'),
                'type' => 'address',
                'patterns' => [
                    '1' => [ // Street Address
                        'pattern' => '/^[\p{L}\d\s\'-,\.]+$/u',
                        'message' => $msgInvalidStreet,
                    ],
                    '3' => [ // City
                        'pattern' => '/^[\p{L}\s\'-]+$/u',
                        'message' => $msgInvalidCity,
                    ],
                    '5' => [ // PLZ (Postal Code)
                        'pattern' => '/^\d{5}$/u',
                        'message' => $msgInvalidPostal,
                    ],
                ],
            ],
            'address_international' => [
                'label' => __('Address - International', 'regex-validation-for-gravity-forms'),
                'type' => 'address',
                'patterns' => [
                    '1' => [ // Street Address
                        'pattern' => '/^[\p{L}\d\s\'-,\.#\/]+$/u',
                        'message' => $msgInvalidStreet,
                    ],
                    '3' => [ // City
                        'pattern' => '/^[\p{L}\s\'-]+$/u',
                        'message' => $msgInvalidCity,
                    ],
                    '4' => [ // State/Province/Region
                        'pattern' => '/^[\p{L}\s\'-]+$/u',
                        'message' => __('Invalid region', 'regex-validation-for-gravity-forms'),
                    ],
                    '5' => [ // Postal Code
                        'pattern' => '/^[\p{L}\d\s\-]+$/u',
                        'message' => $msgInvalidPostal,
                    ],
                ],
            ],
            'address_canadian' => [
                'label' => __('Address - Canadian', 'regex-validation-for-gravity-forms'),
                'type' => 'address',
                'patterns' => [
                    '1' => [ // Street Address
                        'pattern' => '/^[\p{L}\d\s\'-,\.#]+$/u',
                        'message' => $msgInvalidStreet,
                    ],
                    '3' => [ // City
                        'pattern' => '/^[\p{L}\s\'-]+$/u',
                        'message' => $msgInvalidCity,
                    ],
                    '4' => [ // Province
                        'pattern' => '/^[A-Z]{2}$/u',
                        'message' => __('Invalid province code', 'regex-validation-for-gravity-forms'),
                    ],
                    '5' => [ // Postal Code
                        'pattern' => '/^[A-Z]\d[A-Z]\s?\d[A-Z]\d$/iu',
                        'message' => $msgInvalidPostal,
                    ],
                ],
            ],
        ];

        /** @var array<string, array{label: string, type: string, patterns: array<string, array{pattern: string, message: string}>}> */
        return apply_filters('gf_regex_validation_compound_presets', $presets);
    }

    /**
     * Get presets for individual inputs that can be used in advanced mode
     * Users can select these when configuring specific inputs
     *
     * @return array<string, array{label: string, pattern: string, message: string}>
     */
    public static function getInputPresets(): array
    {
        $presets = [
            // Name-related patterns
            'name_text' => [
                'label' => __('Name', 'regex-validation-for-gravity-forms'),
                'pattern' => '/^[\p{L}\s\'-]+$/u',
                'message' => __('Invalid name', 'regex-validation-for-gravity-forms'),
            ],
            'name_prefix' => [
                'label' => __('Title (Mr., Mrs., Dr., etc.)', 'regex-validation-for-gravity-forms'),
                'pattern' => '/^(Mr\.?|Mrs\.?|Ms\.?|Miss|Dr\.?|Prof\.?|Rev\.?)$/i',
                'message' => __('Invalid title', 'regex-validation-for-gravity-forms'),
            ],
            'name_initial' => [
                'label' => __('Initial', 'regex-validation-for-gravity-forms'),
                'pattern' => '/^[\p{L}]\.?$/u',
                'message' => __('Invalid initial', 'regex-validation-for-gravity-forms'),
            ],

            // Address-related patterns
            'street_address' => [
                'label' => __('Street Address', 'regex-validation-for-gravity-forms'),
                'pattern' => '/^[\p{L}\d\s\'-,\.#]+$/u',
                'message' => __('Invalid street address', 'regex-validation-for-gravity-forms'),
            ],
            'city_name' => [
                'label' => __('City', 'regex-validation-for-gravity-forms'),
                'pattern' => '/^[\p{L}\s\'-]+$/u',
                'message' => __('Invalid city', 'regex-validation-for-gravity-forms'),
            ],
            'us_state_code' => [
                'label' => __('US State Code', 'regex-validation-for-gravity-forms'),
                'pattern' => '/^[A-Z]{2}$/u',
                'message' => __('Invalid state code', 'regex-validation-for-gravity-forms'),
            ],
            'us_zip_code' => [
                'label' => __('US ZIP Code', 'regex-validation-for-gravity-forms'),
                'pattern' => '/^\d{5}(-\d{4})?$/u',
                'message' => __('Invalid ZIP code', 'regex-validation-for-gravity-forms'),
            ],
            'german_plz' => [
                'label' => __('German PLZ (5 digits)', 'regex-validation-for-gravity-forms'),
                'pattern' => '/^\d{5}$/u',
                'message' => __('Invalid postal code', 'regex-validation-for-gravity-forms'),
            ],
            'canadian_postal' => [
                'label' => __('Canadian Postal Code', 'regex-validation-for-gravity-forms'),
                'pattern' => '/^[A-Z]\d[A-Z]\s?\d[A-Z]\d$/iu',
                'message' => __('Invalid postal code', 'regex-validation-for-gravity-forms'),
            ],
            'postal_code_flexible' => [
                'label' => __('Postal Code (International)', 'regex-validation-for-gravity-forms'),
                'pattern' => '/^[\p{L}\d\s\-]+$/u',
                'message' => __('Invalid postal code', 'regex-validation-for-gravity-forms'),
            ],
        ];

        /** @var array<string, array{label: string, pattern: string, message: string}> */
        return apply_filters('gf_regex_validation_input_presets', $presets);
    }

    /**
     * Get the default input structure for Name fields
     * Used to populate the UI with correct input labels and IDs
     *
     * @return array<int, string>
     */
    public static function getNameInputStructure(): array
    {
        return [
            '2' => __('Prefix', 'regex-validation-for-gravity-forms'),
            '3' => __('First Name', 'regex-validation-for-gravity-forms'),
            '4' => __('Middle Name', 'regex-validation-for-gravity-forms'),
            '6' => __('Last Name', 'regex-validation-for-gravity-forms'),
            '8' => __('Suffix', 'regex-validation-for-gravity-forms'),
        ];
    }

    /**
     * Get the default input structure for Address fields
     * Used to populate the UI with correct input labels and IDs
     *
     * @return array<int, string>
     */
    public static function getAddressInputStructure(): array
    {
        return [
            '1' => __('Street Address', 'regex-validation-for-gravity-forms'),
            '2' => __('Address Line 2', 'regex-validation-for-gravity-forms'),
            '3' => __('City', 'regex-validation-for-gravity-forms'),
            '4' => __('State / Province / Region', 'regex-validation-for-gravity-forms'),
            '5' => __('ZIP / Postal Code', 'regex-validation-for-gravity-forms'),
            '6' => __('Country', 'regex-validation-for-gravity-forms'),
        ];
    }
}
