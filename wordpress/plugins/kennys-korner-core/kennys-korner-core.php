<?php
/**
 * Plugin Name: Kenny's Korner Core
 * Description: Content model, editing screens, and demo importer for the Kenny's Korner website.
 * Version: 1.3.2
 * Requires at least: 6.4
 * Tested up to: 7.1
 * Requires PHP: 8.0
 * Author: Kenny's Korner
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: kennys-korner-core
 */

if (!defined('ABSPATH')) {
    exit;
}

define('KK_CORE_VERSION', '1.3.2');
define('KK_CORE_FILE', __FILE__);
define('KK_CORE_DIR', plugin_dir_path(__FILE__));
define('KK_CORE_URL', plugin_dir_url(__FILE__));
define('KK_SCHEMA_VERSION', 2);

require_once KK_CORE_DIR . 'includes/content.php';
require_once KK_CORE_DIR . 'includes/schema.php';
require_once KK_CORE_DIR . 'includes/fields.php';
require_once KK_CORE_DIR . 'includes/admin.php';
require_once KK_CORE_DIR . 'includes/importer.php';
require_once KK_CORE_DIR . 'includes/system.php';
require_once KK_CORE_DIR . 'includes/catering.php';

register_activation_hook(KK_CORE_FILE, 'kk_activate');
register_deactivation_hook(KK_CORE_FILE, 'kk_deactivate');
add_action('plugins_loaded', 'kk_boot');

function kk_boot(): void {
    kk_maybe_upgrade_schema();
    kk_fill_blank_contact_settings();
    kk_repair_stored_video_fields();
    KK_Admin::init();
    KK_Importer::init();
    if (defined('WP_CLI') && WP_CLI) {
        require_once KK_CORE_DIR . 'includes/cli.php';
        WP_CLI::add_command('kk', 'KK_CLI');
    }
}

function kk_activate(): void {
    kk_maybe_upgrade_schema();
    kk_fill_blank_contact_settings();
    kk_repair_stored_video_fields();
    kk_ensure_catering();
    flush_rewrite_rules();
}

function kk_deactivate(): void {
    flush_rewrite_rules();
}

function kk_maybe_upgrade_schema(): void {
    $current = (int) get_option('kk_schema_version', 0);
    if ($current >= KK_SCHEMA_VERSION) {
        return;
    }
    update_option('kk_schema_version', KK_SCHEMA_VERSION, false);
}
