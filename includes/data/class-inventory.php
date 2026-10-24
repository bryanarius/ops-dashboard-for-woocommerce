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
	 * @param int    $threshold Stock level at or below which a product counts as low.
	 * @param int    $limit     Maximum number of products to return.
	 * @param string $category  Product category slug to filter by. Empty for all categories.
	 * @return array<int, array{id:int, name:string, sku:string, stock:int, edit_url:string}>
	 */
	public function get_low_stock_products( int $threshold, int $limit = 20, string $category = '' ): array {
		// Lowest stock first, so the first $limit products are the ones that matter.
		$args = array(
			'limit'        => $limit,
			'status'       => 'publish',
			'manage_stock' => true,
			'meta_key'     => '_stock', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Needed to sort by stock.
			'orderby'      => 'meta_value_num',
			'order'        => 'ASC',
		);

		if ( '' !== $category ) {
			$args['category'] = array( $category );
		}

		$products = wc_get_products( $args );

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

	/**
	 * Product categories that contain at least one product, for the filter dropdown.
	 *
	 * @return array<string, string> Category slug => category name.
	 */
	public function get_categories(): array {
		$terms = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => true,
				'orderby'    => 'name',
			)
		);

		// get_terms() returns a WP_Error instead of an array if something goes wrong.
		if ( is_wp_error( $terms ) ) {
			return array();
		}

		$categories = array();

		foreach ( $terms as $term ) {
			$categories[ $term->slug ] = $term->name;
		}

		return $categories;
	}
}
