<?php
/**
 * Dashboard view.
 *
 * Variables available from Admin_Page::render():
 *
 * @var \OpsDashboardWC\Settings $settings
 * @var array<int, array{revenue:float, order_count:int}> $sales Totals from Data\Sales_Summary::get_totals(), keyed by days.
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

	<?php settings_errors(); // Shows "Settings saved." after the form below is submitted. ?>

	<div class="odwc-grid">

		<section class="odwc-panel odwc-panel--sales">
			<h2><?php esc_html_e( 'Sales', 'ops-dashboard-for-woocommerce' ); ?></h2>
			<?php
			$odwc_periods = array(
				1  => __( 'Today', 'ops-dashboard-for-woocommerce' ),
				7  => __( 'Last 7 days', 'ops-dashboard-for-woocommerce' ),
				30 => __( 'Last 30 days', 'ops-dashboard-for-woocommerce' ),
			);
			?>
			<div class="odwc-stats">
				<?php foreach ( $odwc_periods as $odwc_days => $odwc_label ) : ?>
					<div class="odwc-stat">
						<span class="odwc-stat__label"><?php echo esc_html( $odwc_label ); ?></span>
						<span class="odwc-stat__value"><?php echo wp_kses_post( wc_price( $sales[ $odwc_days ]['revenue'] ) ); ?></span>
						<span class="odwc-stat__meta">
							<?php
							printf(
								/* translators: %s: number of orders. */
								esc_html( _n( '%s order', '%s orders', $sales[ $odwc_days ]['order_count'], 'ops-dashboard-for-woocommerce' ) ),
								esc_html( number_format_i18n( $sales[ $odwc_days ]['order_count'] ) )
							);
							?>
						</span>
					</div>
				<?php endforeach; ?>
			</div>
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

		<section class="odwc-panel odwc-panel--settings">
			<h2><?php esc_html_e( 'Settings', 'ops-dashboard-for-woocommerce' ); ?></h2>
			<?php
			$odwc_ranges = \OpsDashboardWC\Settings::ranges();
			$odwc_name   = \OpsDashboardWC\Settings::OPTION_NAME;
			?>
			<form method="post" action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>">
				<?php settings_fields( \OpsDashboardWC\Settings::GROUP ); // Nonce, action and redirect back here. ?>

				<p>
					<label for="odwc-low-stock-threshold"><?php esc_html_e( 'Low-stock threshold', 'ops-dashboard-for-woocommerce' ); ?></label><br>
					<input
						type="number"
						id="odwc-low-stock-threshold"
						class="small-text"
						name="<?php echo esc_attr( $odwc_name . '[low_stock_threshold]' ); ?>"
						value="<?php echo esc_attr( $settings->get( 'low_stock_threshold' ) ); ?>"
						min="<?php echo esc_attr( $odwc_ranges['low_stock_threshold'][0] ); ?>"
						max="<?php echo esc_attr( $odwc_ranges['low_stock_threshold'][1] ); ?>"
					>
				</p>
				<p class="description"><?php esc_html_e( 'Products at or below this stock level are listed as low stock.', 'ops-dashboard-for-woocommerce' ); ?></p>

				<p>
					<label for="odwc-recent-orders-limit"><?php esc_html_e( 'Recent orders to show', 'ops-dashboard-for-woocommerce' ); ?></label><br>
					<input
						type="number"
						id="odwc-recent-orders-limit"
						class="small-text"
						name="<?php echo esc_attr( $odwc_name . '[recent_orders_limit]' ); ?>"
						value="<?php echo esc_attr( $settings->get( 'recent_orders_limit' ) ); ?>"
						min="<?php echo esc_attr( $odwc_ranges['recent_orders_limit'][0] ); ?>"
						max="<?php echo esc_attr( $odwc_ranges['recent_orders_limit'][1] ); ?>"
					>
				</p>

				<?php submit_button( __( 'Save settings', 'ops-dashboard-for-woocommerce' ) ); ?>
			</form>
		</section>

	</div>
</div>
