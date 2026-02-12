# Regex Validation for Gravity Forms

Adds custom regex validation with Unicode support and presets to Gravity Forms fields. Includes both server-side (PHP) and client-side (JavaScript) validation.

## Features

- **Custom regex patterns** per field with a UI in the form editor
- **Unicode support** — `\p{L}`, `\p{N}` character classes for international text
- **Built-in presets** — Name, Email, US/International Phone, Alphanumeric, No Special Characters
- **Dual validation** — Server-side PHP + client-side JavaScript (on `change` event)
- **Compound field support** — Name fields validate each sub-input individually
- **Extensible** — Filters for custom presets and field types
- **Accessible** — Error messages with `role="alert"`

## Requirements

- PHP 8.2+
- WordPress 6.0+
- Gravity Forms 2.5+

## Installation

### Composer

```bash
composer require zirkeldesign/regex-validation-for-gravity-forms
```

### Manual

Download the latest release and upload to `/wp-content/plugins/`.

## Usage

1. Edit a form in Gravity Forms
2. Select a supported field (Text, Name, Email, Phone, Website, Textarea)
3. Find **Regex Validation** in the field settings
4. Choose a preset or enter a custom regex pattern
5. Optionally set a custom validation message

## Built-in Presets

| Preset | Pattern | Description |
|--------|---------|-------------|
| Name | `/^[\p{L}\s'-]+$/u` | Unicode letters, spaces, hyphens, apostrophes |
| Email | `/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/u` | RFC 5322 compliant |
| US Phone | `/^(\+?1)?[\s.-]?\(?[2-9]\d{2}\)?[\s.-]?\d{3}[\s.-]?\d{4}$/u` | Common US formats |
| International Phone | `/^\+?[1-9]\d{1,14}$/u` | E.164 format |
| Alphanumeric | `/^[\p{L}\p{N}]+$/u` | Unicode letters and numbers |
| No Special Chars | `/^[\p{L}\p{N}\s]+$/u` | Unicode letters, numbers, spaces |

## Filters

### `gf_regex_validation_presets`

Add or modify validation presets:

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

### `gf_regex_validation_field_types`

Add support for additional field types:

```php
add_filter('gf_regex_validation_field_types', function (array $types): array {
    $types[] = 'number';

    return $types;
});
```

## How It Works

### Server-side (PHP)
Uses `preg_match()` with the stored regex pattern. For compound fields (like Name), each non-empty sub-input is validated individually.

### Client-side (JavaScript)
Registers via Gravity Forms' `GFFormDisplay::add_init_script()` with `ON_PAGE_RENDER`. Creates `RegExp` with the `u` flag and validates on the `change` event. Shows GF-native error styling (`gfield_error` class + `.validation_message`).

### PHP-to-JS regex conversion
Strips PHP delimiters and flags from the pattern string. The inner pattern (`\p{L}`, `\s`, etc.) is compatible between PHP PCRE and JavaScript ES2018+ with the `u` flag.

## License

GPL-2.0-or-later
