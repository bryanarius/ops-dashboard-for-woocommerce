<?php
/**
 * Runs when the plugin is deleted from the Plugins screen (not on deactivation).
 *
 * @package OpsDashboardWC
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'odwc_settings' );

// Cached sales totals. The plugin's classes aren't loaded here, so these match
// Sales_Summary::CACHE_PREFIX and Sales_Summary::PERIODS by hand.
foreach ( array( 1, 7, 30 ) as $odwc_days ) {
	delete_transient( 'odwc_sales_' . $odwc_days );
}

/*
 * TODO: if you add a cron event later, clean it up here too (wp_clear_scheduled_hook()).
 */
