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
		/*
		 * TODO (Days 5–6): consider wc_get_is_paid_statuses() instead of
		 * hard-coding this list, so other plugins' custom statuses are respected.
		 */
		return array( 'processing', 'completed' );
	}

	/**
	 * Totals for a period ending now.
	 *
	 * @param int $days Number of days to look back (1 = today).
	 * @return array{revenue:float, order_count:int}
	 */
	public function get_totals( int $days ): array {
		/*
		 * TODO (Days 5–6): implement.
		 *
		 * 1. Build a date range: 'date_created' => '>' . ( time() - $days * DAY_IN_SECONDS )
		 *    (Think about "today" vs "last 24 hours" and the store's timezone:
		 *    wp_timezone(), current_datetime().)
		 * 2. wc_get_orders() with 'status' => self::paid_statuses(), 'limit' => -1,
		 *    'return' => 'ids' first, then load totals. Or research
		 *    'paginate' => true to get a total count cheaply.
		 * 3. Sum get_total(). Note: for large stores this gets slow. Note it as a
		 *    known limitation in the README, and look at caching the result in a
		 *    transient (set_transient()) for a few minutes.
		 */
		unset( $days );
		return array(
			'revenue'     => 0.0,
			'order_count' => 0,
		);
	}
}
