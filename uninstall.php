<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @since      1.0.0
 */

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Clear any cached data that has been added by the plugin.
wp_cache_flush();

// Remove any options or settings stored by the plugin
delete_option('emargy_elements_settings');