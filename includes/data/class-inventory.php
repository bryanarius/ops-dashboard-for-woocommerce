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
		// Lowest stock first, so the first $limit products are the ones that matter.
		$products = wc_get_products(
			array(
				'limit'        => $limit,
				'status'       => 'publish',
				'manage_stock' => true,
				'meta_key'     => '_stock', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Needed to sort by stock.
				'orderby'      => 'meta_value_num',
				'order'        => 'ASC',
			)
		);

		$rows = array();

		foreach ( $products as $product ) {
			if ( $threshold < $product->get_stock_quantity() ) {
				continue;
			}

			$rows[] = array(
				'id'       => $product->get_id(),
				'name'     => $product->get_name(),
				'sku'      => $product->get_sku(),
				'stock'    => (int) $product->get_stock_quantity(),
				'edit_url' => get_edit_post_link( $product->get_id(), 'raw' ),
			);
		}

		return $rows;
	}
}
