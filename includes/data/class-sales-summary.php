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
	 * Order statuses that count as a sale.
	 *
	 * @return string[]
	 */
	public static function paid_statuses(): array {
		return wc_get_is_paid_statuses();
	}

	/**
	 * Totals for a period ending now.
	 *
	 * @param int $days Number of days to look back (1 = today).
	 * @return array{revenue:float, order_count:int}
	 */
	public function get_totals( int $days ): array {
		// Midnight in the store's timezone, ( $days - 1 ) days ago. 1 = today only.
		$start = current_datetime()->setTime( 0, 0 )->modify( '-' . ( $days - 1 ) . ' days' );

		$orders = wc_get_orders(
			array(
				'status'       => self::paid_statuses(),
				'limit'        => -1,
				'date_created' => '>=' . $start->getTimestamp(),
			)
		);

		$revenue = 0.0;

		foreach ( $orders as $order ) {
			$revenue += (float) $order->get_total();
		}

		/*
		 * TODO: loading every order gets slow on large stores. Note it as a known
		 * limitation in the README, and consider caching the result in a
		 * transient (set_transient()) for a few minutes.
		 */
		return array(
			'revenue'     => $revenue,
			'order_count' => count( $orders ),
		);
	}
}
