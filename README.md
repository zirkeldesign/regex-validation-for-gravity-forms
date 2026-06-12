# Regex Validation for Gravity Forms

Adds custom regex validation with Unicode support and presets to Gravity Forms fields. Includes both server-side (PHP) and client-side (JavaScript) validation.

## Features

-   **Custom regex patterns** per field with a UI in the form editor
-   **Unicode support** — `\p{L}`, `\p{N}` character classes for international text
-   **Built-in presets** — Name, Email, US/International Phone, Alphanumeric, No Special Characters
-   **Dual validation** — Server-side PHP + client-side JavaScript (on `change` event)
-   **Compound field support** — Name and Address fields with per-input validation
-   **Advanced mode** — Configure different patterns for each input in compound fields
-   **Address field support** — US, German, Canadian, and International address validation
-   **Extensible** — Filters for custom presets and field types
-   **Accessible** — Error messages with `role="alert"`

## Requirements

-   PHP 8.3+
-   WordPress 6.0+
-   Gravity Forms 2.5+

## Installation

### Composer

```bash
composer require zirkeldesign/regex-validation-for-gravity-forms
```

### Manual

Download the latest release and upload to `/wp-content/plugins/`.

## Usage

### Simple Mode (Single Pattern)

For simple fields or when you want the same validation for all inputs:

1. Edit a form in Gravity Forms
2. Select a supported field (Text, Name, Address, Email, Phone, Website, Textarea)
3. Find **Regex Validation** in the field settings
4. Choose a preset or enter a custom regex pattern
5. Optionally set a custom validation message

### Advanced Mode (Per-Input Patterns)

For compound fields like Name or Address where different inputs need different validation:

1. Edit a form in Gravity Forms
2. Select a Name or Address field
3. Find **Regex Validation** in the field settings
4. Click **Advanced Mode (Per-Input)**
5. Choose a compound field preset or configure each input individually
6. Save the form

## Built-in Presets

### Single Field Presets

| Preset              | Pattern                                                       | Description                                   |
| ------------------- | ------------------------------------------------------------- | --------------------------------------------- |
| Name                | `/^[\p{L}\s'-]+$/u`                                           | Unicode letters, spaces, hyphens, apostrophes |
| Email               | `/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/u`         | RFC 5322 compliant                            |
| US Phone            | `/^(\+?1)?[\s.-]?\(?[2-9]\d{2}\)?[\s.-]?\d{3}[\s.-]?\d{4}$/u` | Common US formats                             |
| International Phone | `/^\+?[1-9]\d{1,14}$/u`                                       | E.164 format                                  |
| Alphanumeric        | `/^[\p{L}\p{N}]+$/u`                                          | Unicode letters and numbers                   |
| No Special Chars    | `/^[\p{L}\p{N}\s]+$/u`                                        | Unicode letters, numbers, spaces              |

### Compound Field Presets

#### Name Field Presets

-   **Name - Standard**: Validates first, middle, and last name inputs with letters, spaces, hyphens, apostrophes
-   **Name - With Title**: Includes prefix/title validation (Mr., Mrs., Dr., etc.) plus name validation

#### Address Field Presets

-   **Address - US (Complete)**: Street, City, State (2-letter code), ZIP (5 or 9 digit)
-   **Address - US (Basic)**: Street, City, State, ZIP only (skips Address Line 2 and Country)
-   **Address - German**: Street, City, PLZ (5 digits)
-   **Address - Canadian**: Street, City, Province, Postal Code (e.g., K1A 0B1)
-   **Address - International**: Flexible patterns for global addresses

## Advanced Mode Examples

### Example 1: US Address with Custom Validation

Configure an Address field to validate:

-   Street: Letters, numbers, common punctuation
-   City: Letters only
-   State: 2-letter uppercase code (e.g., CA, NY)
-   ZIP: 5 or 9 digit format

```
Advanced Mode → Select "Address - US (Complete)" preset
```

### Example 2: Name Field with Title Requirement

Configure a Name field to require a title and validate names:

-   Prefix: Must be Mr., Mrs., Ms., Dr., or Prof.
-   First Name: Letters, spaces, hyphens, apostrophes
-   Last Name: Letters, spaces, hyphens, apostrophes

```
Advanced Mode → Select "Name - With Title" preset
```

### Example 3: Custom Per-Input Configuration

For a Name field where you want:

-   First Name: Required, letters only
-   Middle Initial: Optional, single letter
-   Last Name: Required, letters with hyphens allowed

```php
Advanced Mode → Manual Configuration:
- Input 3 (First): Pattern /^[\p{L}]+$/u, Message "First name: letters only"
- Input 4 (Middle): Pattern /^[\p{L}]\.?$/u, Message "Middle: single letter or initial"
- Input 6 (Last): Pattern /^[\p{L}\-]+$/u, Message "Last name: letters and hyphens"
```

## Filters

### `gf_regex_validation_presets`

Add or modify single-field validation presets:

```php
add_filter('gf_regex_validation_presets', function (array $presets): array {
    $presets['zip_code'] = [
        'label'   => 'US Zip Code',
        'pattern' => '/^\d{5}(-\d{4})?$/',
        'message' => 'Please enter a valid US zip code.',
    ];

    return $presets;
});
```

### `gf_regex_validation_compound_presets`

Add or modify compound field presets:

```php
add_filter('gf_regex_validation_compound_presets', function (array $presets): array {
    $presets['address_uk'] = [
        'label' => 'Address - UK',
        'type' => 'address',
        'patterns' => [
            '1' => [ // Street
                'pattern' => '/^[\p{L}\d\s\'-,\.]+$/u',
                'message' => 'Please enter a valid street address',
            ],
            '3' => [ // City
                'pattern' => '/^[\p{L}\s\'-]+$/u',
                'message' => 'Please enter a valid city',
            ],
            '5' => [ // Postcode
                'pattern' => '/^[A-Z]{1,2}\d{1,2}[A-Z]?\s?\d[A-Z]{2}$/i',
                'message' => 'Please enter a valid UK postcode',
            ],
        ],
    ];

    return $presets;
});
```

### `gf_regex_validation_input_presets`

Add presets for individual inputs (used in advanced mode dropdown):

```php
add_filter('gf_regex_validation_input_presets', function (array $presets): array {
    $presets['uk_postcode'] = [
        'label'   => 'UK Postcode',
        'pattern' => '/^[A-Z]{1,2}\d{1,2}[A-Z]?\s?\d[A-Z]{2}$/i',
        'message' => 'Please enter a valid UK postcode',
    ];

    return $presets;
});
```

### `gf_regex_validation_field_types`

Add support for additional field types:

```php
add_filter('gf_regex_validation_field_types', function (array $types): array {
    $types[] = 'number';

    return $types;
});
```

## How It Works

### Simple Mode

Uses a single regex pattern that applies to all inputs in the field. For compound fields (like Name), each non-empty sub-input is validated individually with the same pattern.

### Advanced Mode

For compound fields only (Name, Address). Allows configuring different regex patterns for each sub-input. Only validates visible inputs (hidden inputs are skipped).

### Server-side (PHP)

Uses `preg_match()` with the stored regex pattern. For compound fields, validates each input according to its configured pattern. Hidden inputs are automatically skipped.

### Client-side (JavaScript)

Registers via Gravity Forms' `GFFormDisplay::add_init_script()` with `ON_PAGE_RENDER`. Creates `RegExp` with the `u` flag and validates on the `change` event. In advanced mode, applies different patterns to specific inputs based on their IDs. Shows GF-native error styling (`gfield_error` class + `.validation_message`).

### PHP-to-JS regex conversion

Strips PHP delimiters and flags from the pattern string. The inner pattern (`\p{L}`, `\s`, etc.) is compatible between PHP PCRE and JavaScript ES2018+ with the `u` flag.

## Field Input Structure

### Name Field Inputs

-   Input 2: Prefix (e.g., Mr., Mrs., Dr.)
-   Input 3: First Name
-   Input 4: Middle Name
-   Input 6: Last Name
-   Input 8: Suffix (e.g., Jr., Sr., III)

### Address Field Inputs

-   Input 1: Street Address
-   Input 2: Address Line 2
-   Input 3: City
-   Input 4: State / Province / Region
-   Input 5: ZIP / Postal Code
-   Input 6: Country

## License

GPL-2.0-or-later
