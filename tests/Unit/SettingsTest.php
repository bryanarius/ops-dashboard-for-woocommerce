<?php
/**
 * Example tests. Use them as a pattern for your own.
 */

namespace OpsDashboardWC\Tests\Unit;

use Brain\Monkey\Functions;
use OpsDashboardWC\Settings;
use OpsDashboardWC\Tests\TestCase;

class SettingsTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		// Use the real behaviour of wp_parse_args for these tests.
		Functions\when( 'wp_parse_args' )->alias(
			static function ( $args, $defaults ) {
				return array_merge( $defaults, $args );
			}
		);
	}

	public function test_get_returns_default_when_nothing_saved(): void {
		Functions\when( 'get_option' )->justReturn( array() );

		$this->assertSame( 5, ( new Settings() )->get( 'low_stock_threshold' ) );
	}

	public function test_get_returns_saved_value(): void {
		Functions\when( 'get_option' )->justReturn( array( 'low_stock_threshold' => 12 ) );

		$this->assertSame( 12, ( new Settings() )->get( 'low_stock_threshold' ) );
	}

	public function test_get_survives_corrupted_option(): void {
		Functions\when( 'get_option' )->justReturn( 'not-an-array' );

		$this->assertSame( 10, ( new Settings() )->get( 'recent_orders_limit' ) );
	}

	/*
	 * TODO (Day 7): test Settings::sanitize() once you implement it, e.g.
	 * - negative threshold becomes 0 (or the default)
	 * - string "15" becomes int 15
	 * - missing keys fall back to defaults
	 * - recent_orders_limit above the max is clamped
	 */
}
