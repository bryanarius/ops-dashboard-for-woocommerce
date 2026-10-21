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
		/*
		 * TODO (Days 5–6): implement.
		 *
		 * 1. wc_get_orders( array( 'limit' => $limit, 'orderby' => 'date', 'order' => 'DESC' ) )
		 *    Docs: https://developer.woocommerce.com/docs/features/orders/wc-get-orders/
		 * 2. For each WC_Order, collect:
		 *    - get_id(), get_order_number()
		 *    - get_date_created() (a WC_DateTime; format with wc_format_datetime())
		 *    - wc_get_order_status_name( $order->get_status() )
		 *    - get_formatted_order_total() (already HTML; escape with wp_kses_post() in the view)
		 *    - get_formatted_billing_full_name()
		 *    - get_edit_order_url()  (works with and without HPOS)
		 *
		 * See also: https://rudrastyh.com/tag/orders
		 */
		unset( $limit );
		return array();
	}
}
