<?php
/**
 * Dashboard view.
 *
 * Variables available from Admin_Page::render():
 *
 * @var \OpsDashboardWC\Settings $settings
 * @var array<int, array<string, mixed>> $low_stock     Rows from Data\Inventory::get_low_stock_products().
 * @var array<int, array<string, mixed>> $recent_orders Rows from Data\Orders::get_recent_orders().
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
			<?php // TODO (Day 7): add a category / stock-status filter that calls the AJAX handler. ?>
			<?php if ( empty( $low_stock ) ) : ?>
				<p><?php esc_html_e( 'All products are above the low-stock threshold.', 'ops-dashboard-for-woocommerce' ); ?></p>
			<?php else : ?>
				<table class="widefat striped">
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'Product', 'ops-dashboard-for-woocommerce' ); ?></th>
							<th scope="col"><?php esc_html_e( 'SKU', 'ops-dashboard-for-woocommerce' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Stock', 'ops-dashboard-for-woocommerce' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $low_stock as $odwc_product ) : ?>
							<tr>
								<td><a href="<?php echo esc_url( $odwc_product['edit_url'] ); ?>"><?php echo esc_html( $odwc_product['name'] ); ?></a></td>
								<td><?php echo esc_html( $odwc_product['sku'] ); ?></td>
								<td><?php echo esc_html( $odwc_product['stock'] ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</section>

		<section class="odwc-panel odwc-panel--orders">
			<h2><?php esc_html_e( 'Recent orders', 'ops-dashboard-for-woocommerce' ); ?></h2>
			<?php if ( empty( $recent_orders ) ) : ?>
				<p><?php esc_html_e( 'No orders yet.', 'ops-dashboard-for-woocommerce' ); ?></p>
			<?php else : ?>
				<table class="widefat striped">
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'Order', 'ops-dashboard-for-woocommerce' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Date', 'ops-dashboard-for-woocommerce' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Status', 'ops-dashboard-for-woocommerce' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Customer', 'ops-dashboard-for-woocommerce' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Total', 'ops-dashboard-for-woocommerce' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $recent_orders as $odwc_order ) : ?>
							<tr>
								<td><a href="<?php echo esc_url( $odwc_order['edit_url'] ); ?>">#<?php echo esc_html( $odwc_order['number'] ); ?></a></td>
								<td><?php echo esc_html( $odwc_order['date'] ); ?></td>
								<td><?php echo esc_html( $odwc_order['status'] ); ?></td>
								<td><?php echo esc_html( $odwc_order['customer'] ); ?></td>
								<td><?php echo wp_kses_post( $odwc_order['total'] ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</section>

	</div>
</div>
