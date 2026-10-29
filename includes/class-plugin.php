<?php
/**
 * Main plugin class: wires everything together.
 *
 * @package OpsDashboardWC
 */

namespace OpsDashboardWC;

use OpsDashboardWC\Admin\Admin_Page;

defined( 'ABSPATH' ) || exit;

/**
 * Plugin bootstrap.
 *
 * Keep this class thin. It only creates the other objects and registers
 * their hooks; real logic lives in the classes it wires up.
 */
final class Plugin {

	/**
	 * Single instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Get the single instance.
	 *
	 * @return Plugin
	 */
	public static function instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Register hooks. Called once WooCommerce is confirmed active.
	 *
	 * @return void
	 */
	public function init(): void {
		$settings = new Settings();
		$settings->register();

		// Outside is_admin(): orders are also created at checkout, on the front end.
		$sales_summary = new Data\Sales_Summary();
		$sales_summary->register();

		if ( is_admin() ) {
			$admin_page = new Admin_Page( $settings );
			$admin_page->register();
		}

		/*
		 * TODO (stretch, Day 8): schedule a daily WP-Cron event that emails the
		 * admin a low-stock report. Look up wp_schedule_event() and remember to
		 * unschedule it on deactivation (register_deactivation_hook()).
		 */
	}
}
