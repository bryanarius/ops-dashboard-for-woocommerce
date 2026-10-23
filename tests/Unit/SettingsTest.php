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

	public function test_register_lets_shop_managers_save_settings(): void {
		$settings = new Settings();
		$settings->register();

		$this->assertNotFalse( has_filter( 'option_page_capability_' . Settings::GROUP, array( $settings, 'capability' ) ) );
		$this->assertSame( 'manage_woocommerce', $settings->capability() );
	}

	public function test_sanitize_converts_numeric_strings_to_int(): void {
		$clean = ( new Settings() )->sanitize(
			array(
				'low_stock_threshold' => '15',
				'recent_orders_limit' => '20',
			)
		);

		$this->assertSame( 15, $clean['low_stock_threshold'] );
		$this->assertSame( 20, $clean['recent_orders_limit'] );
	}

	public function test_sanitize_clamps_negative_threshold_to_zero(): void {
		$clean = ( new Settings() )->sanitize( array( 'low_stock_threshold' => '-50' ) );

		$this->assertSame( 0, $clean['low_stock_threshold'] );
	}

	public function test_sanitize_clamps_values_above_max(): void {
		$clean = ( new Settings() )->sanitize(
			array(
				'low_stock_threshold' => '5000',
				'recent_orders_limit' => '999',
			)
		);

		$this->assertSame( 1000, $clean['low_stock_threshold'] );
		$this->assertSame( 50, $clean['recent_orders_limit'] );
	}

	public function test_sanitize_clamps_zero_orders_to_one(): void {
		$clean = ( new Settings() )->sanitize( array( 'recent_orders_limit' => '0' ) );

		$this->assertSame( 1, $clean['recent_orders_limit'] );
	}

	public function test_sanitize_uses_default_for_non_numeric_input(): void {
		$clean = ( new Settings() )->sanitize( array( 'low_stock_threshold' => '<script>alert(1)</script>' ) );

		$this->assertSame( 5, $clean['low_stock_threshold'] );
	}

	public function test_sanitize_fills_missing_keys_with_defaults(): void {
		$this->assertSame( Settings::defaults(), ( new Settings() )->sanitize( array() ) );
	}

	public function test_sanitize_returns_defaults_for_non_array_input(): void {
		$this->assertSame( Settings::defaults(), ( new Settings() )->sanitize( 'not-an-array' ) );
	}

	public function test_sanitize_drops_unknown_keys(): void {
		$clean = ( new Settings() )->sanitize( array( 'is_admin' => '1' ) );

		$this->assertArrayNotHasKey( 'is_admin', $clean );
	}
}
