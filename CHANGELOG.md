# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-02-12

### Added
- Custom regex pattern field in Gravity Forms field editor
- Built-in presets: Name, Email, US Phone, International Phone, Alphanumeric, No Special Characters
- Server-side validation via `gform_field_validation` filter
- Client-side validation via `GFFormDisplay::add_init_script()` with `change` event
- Compound field support (Name field sub-inputs validated individually)
- Unicode support with `\p{L}` and `\p{N}` character classes
- Custom validation messages per field
- `gf_regex_validation_presets` filter for adding/modifying presets
- `gf_regex_validation_field_types` filter for adding field type support
- Accessible error messages with `role="alert"`
