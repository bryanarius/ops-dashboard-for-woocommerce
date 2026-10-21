<?php
/**
 * Inventory queries.
 *
 * @package OpsDashboardWC
 */

namespace OpsDashboardWC\Data;

defined( 'ABSPATH' ) || exit;

/**
 * Read-only access to product stock data.
 *
 * Rule for this class: use WooCommerce's product API only
 * (wc_get_products(), WC_Product). No $wpdb, no direct postmeta queries.
 */
class Inventory {

	/**
	 * Products whose managed stock is at or below the threshold.
	 *
	 * @param int $threshold Stock level at or below which a product counts as low.
	 * @param int $limit     Maximum number of products to return.
	 * @return array<int, array{id:int, name:string, sku:string, stock:int, edit_url:string}>
	 */
	public function get_low_stock_products( int $threshold, int $limit = 20 ): array {
		/*
		 * TODO (Days 3–4): implement.
		 *
		 * 1. Query with wc_get_products(). Useful args: 'limit', 'status',
		 *    'manage_stock' => true, 'orderby', 'order'.
		 *    Docs: https://github.com/woocommerce/woocommerce/wiki/wc_get_products-and-WC_Product_Query
		 * 2. Filter the result to products where get_stock_quantity() <= $threshold.
		 *    (Bonus: research the 'stock_quantity' query arg and whether it can
		 *    do the comparison for you.)
		 * 3. Map each WC_Product to the array shape documented above. Use
		 *    get_edit_post_link() for edit_url.
		 * 4. Decide: should variations count? (Hint: 'type' => array( 'simple', 'variation' ).)
		 *
		 * See also: https://rudrastyh.com/tag/product-inventory
		 */
		unset( $threshold, $limit );
		return array();
	}
}
