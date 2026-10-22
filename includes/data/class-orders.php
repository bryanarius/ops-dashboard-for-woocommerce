<?php
/**
 * Order queries.
 *
 * @package OpsDashboardWC
 */

namespace OpsDashboardWC\Data;

defined( 'ABSPATH' ) || exit;

/**
 * Read-only access to recent orders.
 *
 * Rule for this class: use wc_get_orders() and WC_Order methods only.
 * This is what keeps the plugin HPOS-compatible.
 */
class Orders {

	/**
	 * Most recent orders.
	 *
	 * @param int $limit Number of orders to return.
	 * @return array<int, array{id:int, number:string, date:string, status:string, total:string, customer:string, edit_url:string}>
	 */
	public function get_recent_orders( int $limit ): array {
		$query  = new \WC_Order_Query(
			array(
				'limit'   => $limit,
				'orderby' => 'date',
				'order'   => 'DESC',
			)
		);
		$orders = $query->get_orders();

		$rows = array();

		foreach ( $orders as $order ) {
			$rows[] = array(
				'id'       => $order->get_id(),
				'number'   => $order->get_order_number(),
				'date'     => wc_format_datetime( $order->get_date_created() ),
				'status'   => wc_get_order_status_name( $order->get_status() ),
				'total'    => $order->get_formatted_order_total(),
				'customer' => $order->get_formatted_billing_full_name(),
				'edit_url' => $order->get_edit_order_url(),
			);
		}

		return $rows;
	}
}
