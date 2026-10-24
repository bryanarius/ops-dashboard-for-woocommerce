<?php
/**
 * Fill a LOCAL store with demo products and orders for the Ops Dashboard.
 *
 * Usage (from the plugin folder, with the PHP that can reach your database):
 *   /Applications/XAMPP/xamppfiles/bin/php bin/seed-demo-data.php           Replace demo data
 *   /Applications/XAMPP/xamppfiles/bin/php bin/seed-demo-data.php --reset   Only remove demo data
 *
 * Everything this script creates is tagged with the _odwc_seed meta key, so
 * re-running it or --reset only touches demo data, never your own products/orders.
 * Set WP_LOAD=/path/to/wp-load.php if the plugin folder is symlinked.
 *
 * This is a developer tool. It is not loaded by the plugin.
 *
 * @package OpsDashboardWC
 */

// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_fwrite -- CLI output, not file writes.

namespace OpsDashboardWC\Dev;

const SEED_META = '_odwc_seed';

if ( 'cli' !== PHP_SAPI ) {
	exit( 1 );
}

main( $argv ?? array() );

/**
 * Entry point.
 *
 * @param string[] $args Command-line arguments.
 * @return void
 */
function main( array $args ): void {
	$wp_load = getenv( 'WP_LOAD' ) ? getenv( 'WP_LOAD' ) : dirname( __DIR__, 4 ) . '/wp-load.php';

	if ( ! file_exists( $wp_load ) ) {
		fail( "Can't find wp-load.php at $wp_load. Set WP_LOAD=/path/to/wp-load.php." );
	}

	require $wp_load;

	if ( ! is_local_site() ) {
		fail( 'Refusing to run: ' . home_url() . ' does not look like a local site.' );
	}

	if ( ! function_exists( 'wc_create_order' ) ) {
		fail( 'WooCommerce is not active.' );
	}

	// Seeding must not email anyone or change the stock levels we set on purpose.
	add_filter( 'pre_wp_mail', '__return_false' );
	add_filter( 'woocommerce_can_reduce_order_stock', '__return_false' );

	remove_demo_data();

	if ( in_array( '--reset', $args, true ) ) {
		out( 'Done. Demo data removed.' );
		return;
	}

	$products = create_products();
	$orders   = create_orders( $products );

	out( sprintf( 'Done. Created %d products and %d orders.', count( $products ), $orders ) );
}

/**
 * Only allow sites WordPress marks as local/development, or that run on a local hostname.
 *
 * @return bool
 */
function is_local_site(): bool {
	if ( in_array( wp_get_environment_type(), array( 'local', 'development' ), true ) ) {
		return true;
	}

	$host = (string) wp_parse_url( home_url(), PHP_URL_HOST );

	return in_array( $host, array( 'localhost', '127.0.0.1' ), true )
		|| str_ends_with( $host, '.test' )
		|| str_ends_with( $host, '.local' )
		|| str_ends_with( $host, '.localhost' );
}

/**
 * Delete every product and order this script created earlier.
 *
 * @return void
 */
function remove_demo_data(): void {
	$orders = wc_get_orders(
		array(
			'limit'      => -1,
			'status'     => array_keys( wc_get_order_statuses() ),
			'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Dev tool, runs rarely.
				array(
					'key'   => SEED_META,
					'value' => '1',
				),
			),
		)
	);

	foreach ( $orders as $order ) {
		$order->delete( true );
	}

	$products = wc_get_products(
		array(
			'limit'      => -1,
			'status'     => array( 'publish', 'draft', 'private', 'trash' ),
			'meta_key'   => SEED_META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Dev tool, runs rarely.
			'meta_value' => '1', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Dev tool, runs rarely.
		)
	);

	foreach ( $products as $product ) {
		$product->delete( true );
	}

	out( sprintf( 'Removed %d demo orders and %d demo products.', count( $orders ), count( $products ) ) );
}

/**
 * Create demo products with a spread of stock levels across categories.
 *
 * @return \WC_Product_Simple[]
 */
function create_products(): array {
	// Name, category slug, category name, stock, price.
	$items = array(
		array( 'Demo Tee - Black', 'tshirts', 'Tshirts', 0, 18 ),
		array( 'Demo Tee - White', 'tshirts', 'Tshirts', 3, 18 ),
		array( 'Demo Tee - Navy', 'tshirts', 'Tshirts', 12, 20 ),
		array( 'Demo Hoodie - Grey', 'hoodies', 'Hoodies', 2, 45 ),
		array( 'Demo Hoodie - Olive', 'hoodies', 'Hoodies', 7, 45 ),
		array( 'Demo Zip Hoodie', 'hoodies', 'Hoodies', 15, 55 ),
		array( 'Demo Cap', 'accessories', 'Accessories', 1, 16 ),
		array( 'Demo Tote Bag', 'accessories', 'Accessories', 4, 14 ),
		array( 'Demo Sunglasses', 'accessories', 'Accessories', 9, 25 ),
		array( 'Demo Beanie', 'accessories', 'Accessories', 20, 15 ),
		array( 'Demo Vinyl LP', 'music', 'Music', 0, 30 ),
		array( 'Demo Cassette', 'music', 'Music', 5, 12 ),
		array( 'Demo Poster Pack', 'decor', 'Decor', 6, 22 ),
		array( 'Demo Mug', 'decor', 'Decor', 3, 11 ),
		array( 'Demo Candle', 'decor', 'Decor', 18, 19 ),
	);

	$products = array();

	foreach ( $items as $item ) {
		list( $name, $cat_slug, $cat_name, $stock, $price ) = $item;

		$product = new \WC_Product_Simple();
		$product->set_name( $name );
		$product->set_sku( 'demo-' . sanitize_title( $name ) );
		$product->set_regular_price( (string) $price );
		$product->set_manage_stock( true );
		$product->set_stock_quantity( $stock );
		$product->set_category_ids( array( category_id( $cat_slug, $cat_name ) ) );
		$product->set_status( 'publish' );
		$product->update_meta_data( SEED_META, '1' );
		$product->save();

		$products[] = $product;
	}

	out( sprintf( 'Created %d demo products.', count( $products ) ) );

	return $products;
}

/**
 * Create demo orders spread over the last 45 days, a few of them today.
 *
 * @param \WC_Product_Simple[] $products Products to put in the orders.
 * @return int Number of orders created.
 */
function create_orders( array $products ): int {
	$customers = array(
		array( 'Alex', 'Rivera' ),
		array( 'Sam', 'Chen' ),
		array( 'Jordan', 'Patel' ),
		array( 'Taylor', 'Nguyen' ),
		array( 'Morgan', 'Okafor' ),
		array( 'Casey', 'Silva' ),
		array( 'Riley', 'Kim' ),
		array( 'Jamie', 'Novak' ),
	);

	// Weighted: mostly paid statuses, plus some that must NOT count as sales.
	$statuses = array( 'completed', 'completed', 'completed', 'completed', 'processing', 'processing', 'processing', 'pending', 'on-hold', 'cancelled', 'refunded' );

	$now      = time();
	$midnight = current_datetime()->setTime( 0, 0 )->getTimestamp();
	$count    = 30;

	for ( $i = 0; $i < $count; $i++ ) {
		// First three orders are today, so the "Today" card has data.
		$created = $i < 3
			? wp_rand( $midnight, $now )
			: $now - wp_rand( 1, 44 ) * DAY_IN_SECONDS - wp_rand( 0, DAY_IN_SECONDS - 1 );

		list( $first, $last ) = $customers[ array_rand( $customers ) ];

		$order = wc_create_order( array( 'created_via' => 'odwc-seed' ) );

		foreach ( (array) array_rand( $products, wp_rand( 1, 3 ) ) as $index ) {
			$order->add_product( $products[ $index ], wp_rand( 1, 2 ) );
		}

		$order->set_address(
			array(
				'first_name' => $first,
				'last_name'  => $last,
				'email'      => strtolower( $first ) . '@example.com',
			),
			'billing'
		);
		$order->calculate_totals();
		// Today's orders are always paid, so the "Today" card never ends up empty by chance.
		$order->set_status( $i < 3 ? 'processing' : $statuses[ array_rand( $statuses ) ] );
		$order->set_date_created( $created );
		$order->update_meta_data( SEED_META, '1' );
		$order->save();

		// Marking an order "refunded" makes WooCommerce create a refund dated now. Backdate it to match.
		foreach ( $order->get_refunds() as $refund ) {
			$refund->set_date_created( $created );
			$refund->save();
		}
	}

	out( sprintf( 'Created %d demo orders.', $count ) );

	return $count;
}

/**
 * Get a product category ID, creating the category if it doesn't exist.
 *
 * @param string $slug Category slug.
 * @param string $name Category name, used if it has to be created.
 * @return int
 */
function category_id( string $slug, string $name ): int {
	$term = get_term_by( 'slug', $slug, 'product_cat' );

	if ( $term ) {
		return (int) $term->term_id;
	}

	$created = wp_insert_term( $name, 'product_cat', array( 'slug' => $slug ) );

	return is_wp_error( $created ) ? 0 : (int) $created['term_id'];
}

/**
 * Print a line to the terminal.
 *
 * @param string $message Message.
 * @return void
 */
function out( string $message ): void {
	fwrite( STDOUT, $message . PHP_EOL );
}

/**
 * Print an error and stop.
 *
 * @param string $message Message.
 * @return never
 */
function fail( string $message ): never {
	fwrite( STDERR, 'Error: ' . $message . PHP_EOL );
	exit( 1 );
}
