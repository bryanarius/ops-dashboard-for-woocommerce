<?php
/**
 * Checks that sales totals are cached in a transient.
 */

namespace OpsDashboardWC\Tests\Unit;

use Brain\Monkey\Functions;
use OpsDashboardWC\Data\Sales_Summary;
use OpsDashboardWC\Tests\TestCase;

class SalesSummaryTest extends TestCase {

	public function test_cached_totals_are_returned_without_querying_orders(): void {
		$cached = array(
			'revenue'     => 120.0,
			'order_count' => 3,
		);

		Functions\expect( 'get_transient' )->once()->with( 'odwc_sales_7' )->andReturn( $cached );
		Functions\expect( 'wc_get_orders' )->never();
		Functions\expect( 'set_transient' )->never();

		$this->assertSame( $cached, ( new Sales_Summary() )->get_totals( 7 ) );
	}

	public function test_fresh_totals_are_calculated_and_cached(): void {
		Functions\when( 'get_transient' )->justReturn( false );
		Functions\when( 'current_datetime' )->justReturn( new \DateTimeImmutable( '2026-10-28 15:00:00' ) );
		Functions\when( 'wc_get_is_paid_statuses' )->justReturn( array( 'processing', 'completed' ) );
		Functions\when( 'wc_get_orders' )->justReturn( array( $this->order( '20.50' ), $this->order( '30.00' ) ) );

		$expected = array(
			'revenue'     => 50.5,
			'order_count' => 2,
		);

		Functions\expect( 'set_transient' )->once()->with( 'odwc_sales_7', $expected, 5 * MINUTE_IN_SECONDS );

		$this->assertSame( $expected, ( new Sales_Summary() )->get_totals( 7 ) );
	}

	public function test_register_clears_cache_on_order_changes(): void {
		$summary = new Sales_Summary();
		$summary->register();

		foreach ( array( 'woocommerce_new_order', 'woocommerce_update_order', 'woocommerce_trash_order', 'woocommerce_delete_order' ) as $hook ) {
			$this->assertNotFalse( has_action( $hook, array( $summary, 'flush_cache' ) ), $hook );
		}
	}

	public function test_flush_cache_deletes_every_period(): void {
		Functions\expect( 'delete_transient' )->once()->with( 'odwc_sales_1' );
		Functions\expect( 'delete_transient' )->once()->with( 'odwc_sales_7' );
		Functions\expect( 'delete_transient' )->once()->with( 'odwc_sales_30' );

		( new Sales_Summary() )->flush_cache();
	}

	/**
	 * A stand-in for WC_Order. get_totals() only calls get_total().
	 *
	 * @param string $total Order total, as WooCommerce returns it.
	 */
	private function order( string $total ): object {
		return new class( $total ) {
			public function __construct( private string $total ) {}

			public function get_total(): string {
				return $this->total;
			}
		};
	}
}
