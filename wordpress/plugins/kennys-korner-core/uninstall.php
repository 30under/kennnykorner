<?php
/**
 * Content is kept when the plugin is deleted so a theme change or a reinstall
 * does not wipe the restaurant's edits. Use Kenny's Korner → Setup to remove
 * imported content on purpose.
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}
