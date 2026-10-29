<?php
/**
 * Sales summary calculations.
 *
 * @package OpsDashboardWC
 */

namespace OpsDashboardWC\Data;

defined( 'ABSPATH' ) || exit;

/**
 * Revenue and order counts for recent periods.
 */
class Sales_Summary {

	/**
	 * Transient key prefix. The number of days is appended, e.g. odwc_sales_7.
	 */
	const CACHE_PREFIX = 'odwc_sales_';

	/**
	 * How long totals are cached, in seconds.
	 */
	const CACHE_TTL = 5 * MINUTE_IN_SECONDS;

	/**
	 * Periods shown on the dashboard, in days. Each one has its own cached total.
	 */
	const PERIODS = array( 1, 7, 30 );

	/**
	 * Order events that can change the totals.
	 */
	const ORDER_HOOKS = array(
		'woocommerce_new_order',
		'woocommerce_update_order', // Also fires on status changes, e.g. processing to refunded.
		'woocommerce_trash_order',
		'woocommerce_delete_order',
	);

	/**
	 * Clear the cached totals whenever an order changes, so new sales show up right away.
	 *
	 * @return void
	 */
	public function register(): void {
		foreach ( self::ORDER_HOOKS as $hook ) {
			add_action( $hook, array( $this, 'flush_cache' ) );
		}
	}

	/**
	 * Delete every cached total. The next dashboard load recalculates them.
	 *
	 * @return void
	 */
	public function flush_cache(): void {
		foreach ( self::PERIODS as $days ) {
			delete_transient( self::CACHE_PREFIX . $days );
		}
	}

	/**
	 * Order statuses that count as a sale.
	 *
	 * @return string[]
	 */
	public static function paid_statuses(): array {
		return wc_get_is_paid_statuses();
	}

	/**
	 * Totals for a period ending now, cached for a few minutes.
	 *
	 * @param int $days Number of days to look back (1 = today).
	 * @return array{revenue:float, order_count:int}
	 */
	public function get_totals( int $days ): array {
		$cache_key = self::CACHE_PREFIX . $days;
		$cached    = get_transient( $cache_key );

		// get_transient() returns false when nothing is saved or it has expired.
		if ( is_array( $cached ) ) {
			return $cached;
		}

		// Midnight in the store's timezone, ( $days - 1 ) days ago. 1 = today only.
		$start = current_datetime()->setTime( 0, 0 )->modify( '-' . ( $days - 1 ) . ' days' );

		$orders = wc_get_orders(
			array(
				'type'         => 'shop_order', // Exclude refunds, which have negative totals.
				'status'       => self::paid_statuses(),
				'limit'        => -1,
				'date_created' => '>=' . $start->getTimestamp(),
			)
		);

		$revenue = 0.0;

		foreach ( $orders as $order ) {
			$revenue += (float) $order->get_total();
		}

		$totals = array(
			'revenue'     => $revenue,
			'order_count' => count( $orders ),
		);

		// Loading every order is slow on large stores, so save the result for next time.
		set_transient( $cache_key, $totals, self::CACHE_TTL );

		return $totals;
	}
}
