# Changelog

All notable changes to this project will be documented in this file.

## [1.1.1] - 2026-08-13

### Changed

- **`Tested up to: 7.1`.** Verified against WordPress 7.1-RC3 with real Gravity Forms 2.10.5: Plugin Check on the built zip reports 0 errors and 0 warnings, and the field-editor and validation hooks run without a notice of our own. None of the 7.1 breaking changes apply — the plugin registers no blocks, ships no editor JS beyond what Gravity Forms itself loads, has no runtime `@wordpress/*` dependency (`@wordpress/prettier-config` is a formatting devDependency), and does not use jQuery UI.

### Fixed

- **The unit suite ran zero tests and reported success.** Every class in `src/` guards against direct file access with `if (! defined('ABSPATH')) { exit; }`, but `tests/bootstrap.php` never defined `ABSPATH` — so the first autoloaded class terminated the runner with exit code 0 before a single test executed. Pest printed nothing, PHPUnit's JUnit log came out empty, and CI went green on an empty run. `tests/bootstrap.php` now defines `ABSPATH`.
- **`tests/Unit/RegexFieldValidatorTest.php` never imported `CompoundFieldPresets`.** Invisible while the suite was a no-op; 19 of the 36 tests errored the moment it actually ran. All 36 pass now.

### Added

- `WP_VERSION` override in `bin/plugin-check.sh` (`WP_VERSION=7.1-RC3 composer pcp`), so a release candidate can be tested before readme.txt claims compatibility with it. Previously the target core was read from readme.txt's `Tested up to:` and could therefore never run ahead of it.

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
