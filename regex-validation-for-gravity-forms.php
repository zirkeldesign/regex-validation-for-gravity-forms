<?php

/**
 * Plugin Name:       Regex Validation for Gravity Forms
 * Plugin URI:        https://github.com/zirkeldesign/regex-validation-for-gravity-forms
 * Description:       Adds custom regex validation with Unicode support and presets to Gravity Forms fields. Includes both server-side and client-side validation.
 * Version:           1.0.0
 * Author:            zirkel.design
 * Author URI:        https://zirkel.design
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       regex-validation-for-gravity-forms
 * Domain Path:       /languages
 * Requires PHP:      8.2
 * Requires at least: 6.0
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

define('GF_REGEX_VALIDATION_VERSION', '1.0.0');
define('GF_REGEX_VALIDATION_FILE', __FILE__);
define('GF_REGEX_VALIDATION_DIR', plugin_dir_path(__FILE__));

/**
 * Load Composer autoloader.
 */
if (file_exists($autoloader = GF_REGEX_VALIDATION_DIR . 'vendor/autoload.php')) {
    require_once $autoloader;
}

/**
 * Load plugin textdomain.
 */
add_action('init', static function (): void {
    load_plugin_textdomain(
        'regex-validation-for-gravity-forms',
        false,
        dirname(plugin_basename(GF_REGEX_VALIDATION_FILE)) . '/languages'
    );
});

/**
 * Initialize the plugin after all plugins are loaded.
 */
add_action('plugins_loaded', static function (): void {
    if (! class_exists('GFForms') && ! class_exists('GFAPI')) {
        add_action('admin_notices', static function (): void {
            if (! current_user_can('activate_plugins')) {
                return;
            }

            printf(
                '<div class="notice notice-error"><p>%s</p></div>',
                esc_html__(
                    'Regex Validation for Gravity Forms requires Gravity Forms to be installed and activated.',
                    'regex-validation-for-gravity-forms'
                )
            );
        });

        return;
    }

    new \ZirkelDesign\GFRegexValidation\RegexFieldValidator();
});
