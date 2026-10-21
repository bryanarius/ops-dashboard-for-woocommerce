<?php
/**
 * Plugin Name:          Ops Dashboard for WooCommerce
 * Plugin URI:           https://github.com/bryanarius/ops-dashboard-for-woocommerce
 * Description:          One admin screen for store managers: low-stock products, recent orders, and sales at a glance.
 * Version:              0.1.0
 * Requires at least:    6.5
 * Requires PHP:         8.1
 * Requires Plugins:     woocommerce
 * Author:               Bryan Arius
 * Author URI:           https://github.com/bryanarius
 * License:              GPL-2.0-or-later
 * License URI:          https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:          ops-dashboard-for-woocommerce
 * WC requires at least: 8.2
 * WC tested up to:      10.8
 *
 * @package OpsDashboardWC
 */

defined( 'ABSPATH' ) || exit;

define( 'ODWC_VERSION', '0.1.0' );
define( 'ODWC_FILE', __FILE__ );
define( 'ODWC_PATH', plugin_dir_path( __FILE__ ) );
define( 'ODWC_URL', plugin_dir_url( __FILE__ ) );

require_once ODWC_PATH . 'includes/class-settings.php';
require_once ODWC_PATH . 'includes/data/class-inventory.php';
require_once ODWC_PATH . 'includes/data/class-orders.php';
require_once ODWC_PATH . 'includes/data/class-sales-summary.php';
require_once ODWC_PATH . 'admin/class-admin-page.php';
require_once ODWC_PATH . 'includes/class-plugin.php';

/*
 * Declare compatibility with High-Performance Order Storage (HPOS).
 * This promise only holds if every order query in this plugin goes through
 * WooCommerce APIs (wc_get_orders(), WC_Order) and never touches wp_posts or
 * wp_postmeta directly.
 */
add_action(
	'before_woocommerce_init',
	static function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', ODWC_FILE, true );
		}
	}
);

add_action(
	'plugins_loaded',
	static function () {
		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action(
				'admin_notices',
				static function () {
					echo '<div class="notice notice-error"><p>' . esc_html__( 'Ops Dashboard for WooCommerce requires WooCommerce to be installed and active.', 'ops-dashboard-for-woocommerce' ) . '</p></div>';
				}
			);
			return;
		}

		\OpsDashboardWC\Plugin::instance()->init();
	}
);
