# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.4] - 2026-05-08

### Fixed
- Load admin editor script in footer to prevent Gravity Forms layout editor timing errors

## [1.0.3] - 2026-02-25

### Added
- WordPress.org plugin directory assets (icon and banners)
- Brand-aligned design featuring regex pattern with Gravity Forms colors

## [1.0.2] - 2026-02-19

### Fixed
- Load admin scripts properly in Gravity Forms noconflict mode
- Fix script dependency timing by adding `gform_form_editor` dependency
- Wrap field settings modification in DOM ready handler

### Changed
- Extract inline JavaScript to separate `assets/js/admin-field-editor.js` file
- Use `wp_add_inline_script()` with IIFE pattern for passing data to avoid global scope pollution
- Format JavaScript to WordPress Coding Standards with tabs

### Added
- PHPStan level 6 static analysis with Gravity Forms stubs
- Prettier with WordPress config for consistent JavaScript formatting
- EditorConfig for consistent code style across editors
- WordPress plugin development guidelines document
- Add `gform_noconflict_scripts` filter to whitelist admin script

## [1.0.1] - 2026-02-12

### Fixed
- Escape all output per WordPress security guidelines
- Replace `error_log` with `_doing_it_wrong` for invalid regex patterns
- Add direct file access protection to source files
- Remove deprecated `load_plugin_textdomain` call
- Fix "Tested up to" version format in readme

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
