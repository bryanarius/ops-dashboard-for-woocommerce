<?php
/**
 * Runs when the plugin is deleted from the Plugins screen (not on deactivation).
 *
 * @package OpsDashboardWC
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'odwc_settings' );

/*
 * TODO: if you add transients or a cron event later, clean them up here too
 * (delete_transient(), wp_clear_scheduled_hook()).
 */
