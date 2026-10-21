<?php
/**
 * Dashboard view.
 *
 * Variables available from Admin_Page::render():
 *
 * @var \OpsDashboardWC\Settings $settings
 *
 * Escape everything you print: esc_html(), esc_attr(), esc_url(),
 * and wp_kses_post() for HTML that WooCommerce already formatted.
 *
 * @package OpsDashboardWC
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap odwc">
	<h1><?php esc_html_e( 'Ops Dashboard', 'ops-dashboard-for-woocommerce' ); ?></h1>

	<div class="odwc-grid">

		<section class="odwc-panel odwc-panel--sales">
			<h2><?php esc_html_e( 'Sales', 'ops-dashboard-for-woocommerce' ); ?></h2>
			<?php
			/*
			 * TODO (Days 5–6): show revenue and order count for today, 7 days, 30 days
			 * using Data\Sales_Summary::get_totals(). Format money with wc_price().
			 */
			?>
		</section>

		<section class="odwc-panel odwc-panel--low-stock">
			<h2>
				<?php
				printf(
					/* translators: %d: low-stock threshold. */
					esc_html__( 'Low stock (≤ %d)', 'ops-dashboard-for-woocommerce' ),
					(int) $settings->get( 'low_stock_threshold' )
				);
				?>
			</h2>
			<?php
			/*
			 * TODO (Days 3–4): render a table (class="widefat striped") of
			 * products from Data\Inventory::get_low_stock_products(), each
			 * name linking to its edit_url. Show a friendly empty state.
			 * Day 7: add a category / stock-status filter that calls the AJAX handler.
			 */
			?>
		</section>

		<section class="odwc-panel odwc-panel--orders">
			<h2><?php esc_html_e( 'Recent orders', 'ops-dashboard-for-woocommerce' ); ?></h2>
			<?php
			/*
			 * TODO (Days 5–6): render Data\Orders::get_recent_orders() with
			 * number (linked), date, status, customer, total.
			 */
			?>
		</section>

	</div>
</div>
