# Changelog

All notable changes to this project will be documented in this file.

## [1.1.0] - 2026-06-12

### New Features

-   **Advanced Mode for Name and Address Fields** - Configure different validation rules for each input (like validating ZIP codes differently than city names)
-   **Smart Preset Suggestions** - Automatically suggests the right validation preset based on your field type
-   **Address Field Support** - Now works with Address fields, with presets for US, German, Canadian, and International addresses
-   **More Validation Presets** - Added presets for common patterns like ZIP codes, postal codes, state abbreviations, and more

### Improvements

-   Better styling that matches Gravity Forms' design
-   Validation now skips hidden inputs automatically
-   Minified JavaScript files for faster page loading

### Bug Fixes

-   Fixed: Name fields were applying the same validation to all inputs (Title, First Name, Last Name, etc.)
-   Fixed: Address fields couldn't be configured because different inputs need different validation
-   Fixed: Hidden inputs in Name/Address fields were incorrectly validated

---

**For Developers:**

-   Build system now uses Vite for asset compilation (both `.js` and `.min.js` versions)
-   New `CompoundFieldPresets` class for managing Name and Address field presets
-   New filters: `gf_regex_validation_compound_presets` and `gf_regex_validation_input_presets`
-   Comprehensive unit tests for new preset functionality
-   Translation files updated (.pot file regenerated)

## [1.0.4] - 2026-05-08

### Fixed

-   Fixed Gravity Forms form editor JavaScript timing issue by loading the admin editor script in the footer

## [1.0.4] - 2026-05-08

### Fixed
- Load admin editor script in footer to prevent Gravity Forms layout editor timing errors

## [1.0.3] - 2026-02-25

### Added

-   WordPress.org plugin directory assets (icon and banners)

## [1.0.2] - 2026-02-19

### Fixed

-   Admin scripts now load properly in Gravity Forms
-   Fixed timing issue with field settings

### Improvements

-   Cleaner JavaScript code structure
-   Better code quality with static analysis tools

## [1.0.1] - 2026-02-12

### Fixed

-   Security improvements following WordPress guidelines
-   Better error handling

## [1.0.0] - 2026-02-12

### Initial Release

-   Add custom regex validation to Gravity Forms fields
-   Built-in presets for common patterns (names, emails, phone numbers)
-   Both server-side and client-side validation
-   Support for Name fields with individual input validation
-   Unicode support for international characters
-   Custom error messages
