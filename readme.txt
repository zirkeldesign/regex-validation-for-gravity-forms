=== Regex Validation for Gravity Forms ===
Contributors: dsturm
Tags: gravity forms, regex, validation, unicode, pattern
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 8.3
Stable tag: 1.1.1
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds custom regex validation with Unicode support and presets to Gravity Forms fields.

== Description ==

**Regex Validation for Gravity Forms** adds a powerful regex validation option to your Gravity Forms fields. Define custom regular expression patterns or choose from built-in presets to validate user input — both server-side and client-side.

= Features =

* **Custom regex patterns** — Add any regular expression to validate field input
* **Unicode support** — Full Unicode character class support (`\p{L}`, `\p{N}`) for international names and text
* **Built-in presets** — Quick-select common patterns for names, emails, phone numbers, and more
* **Server-side validation** — Secure PHP validation that can't be bypassed
* **Client-side validation** — Instant feedback on input change using JavaScript
* **Compound field support** — Works with Name fields (validates each sub-input individually)
* **Custom error messages** — Define user-friendly validation messages per field
* **Extensible** — Add your own presets and field types via filters
* **Accessible** — Error messages use `role="alert"` for screen readers

= Supported Field Types =

* Text
* Name (with individual sub-input validation)
* Email
* Phone
* Website
* Textarea

= Built-in Presets =

* **Name** — Unicode letters, spaces, hyphens, apostrophes
* **Email** — RFC 5322 compliant
* **US Phone Number** — Common US formats
* **International Phone Number** — E.164 format
* **Alphanumeric** — Unicode letters and numbers only
* **No Special Characters** — Unicode letters, numbers, and spaces only

== Installation ==

= From WordPress Admin =

1. Upload the `regex-validation-for-gravity-forms` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress

= Via Composer =

    composer require zirkeldesign/regex-validation-for-gravity-forms

= Requirements =

* WordPress 6.0 or later
* PHP 8.2 or later
* Gravity Forms 2.5 or later

== Frequently Asked Questions ==

= How do I add a regex pattern to a field? =

Edit your form in Gravity Forms, select a supported field, and look for the "Regex Validation" section under the field settings. You can either select a preset pattern or enter your own custom regex.

= Does it support Unicode characters? =

Yes! All patterns use the `/u` flag for PHP and the `u` flag for JavaScript regex, enabling full Unicode property support like `\p{L}` for any letter in any language.

= Can I add my own presets? =

Yes, use the `gf_regex_validation_presets` filter:

    add_filter('gf_regex_validation_presets', function (array $presets): array {
        $presets['zip_code'] = [
            'label'   => 'US Zip Code',
            'pattern' => '/^\d{5}(-\d{4})?$/',
            'message' => 'Please enter a valid US zip code.',
        ];

        return $presets;
    });

= Can I add support for additional field types? =

Yes, use the `gf_regex_validation_field_types` filter:

    add_filter('gf_regex_validation_field_types', function (array $types): array {
        $types[] = 'number';

        return $types;
    });

= Is client-side validation secure? =

Client-side validation provides instant user feedback but should never be relied upon for security. This plugin always performs server-side validation as well, which cannot be bypassed.

== Screenshots ==

1. Regex validation settings in the Gravity Forms field editor
2. Preset selection dropdown
3. Client-side validation error message

== Changelog ==

= 1.1.1 =
* Improved: tested and confirmed compatible with WordPress 7.1.

= 1.1.0 =
* Added: Advanced Mode for Name and Address fields — set a different validation rule for each input (e.g. validate ZIP codes differently than city names)
* Added: Support for Address fields, with presets for US, German, Canadian, and International addresses
* Added: Smart preset suggestions based on the field type you're editing
* Added: More built-in presets for ZIP codes, postal codes, state abbreviations and other common patterns
* Fixed: Name fields no longer apply the same rule to every input (Prefix, First Name, Last Name, etc.)
* Fixed: Hidden inputs in Name and Address fields are no longer validated
* Improved: Field editor styling now matches the Gravity Forms design, and admin scripts load faster

= 1.0.4 =
* Fixed: Prevented a form editor JavaScript error by loading plugin admin scripts at the correct time

= 1.0.2 =
* Fixed: Admin field settings not appearing in Gravity Forms editor
* Improved: Code quality and WordPress coding standards compliance
* Enhanced: Development tooling for better maintainability

= 1.0.1 =
* Fixed: WordPress Plugin Check compliance issues
* Improved: Security and code standards

= 1.0.0 =
* Initial release
* Server-side and client-side regex validation
* Built-in presets for common patterns
* Unicode support with `\p{L}` and `\p{N}` character classes
* Name field compound input support
* Extensible via `gf_regex_validation_presets` and `gf_regex_validation_field_types` filters
