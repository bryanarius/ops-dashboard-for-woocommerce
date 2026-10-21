<?php
/**
 * Plugin settings: storage, defaults, and registration.
 *
 * @package OpsDashboardWC
 */

namespace OpsDashboardWC;

defined( 'ABSPATH' ) || exit;

/**
 * All settings live in one option, stored as an array.
 */
class Settings {

	/**
	 * Option name in wp_options.
	 */
	public const OPTION_NAME = 'odwc_settings';

	/**
	 * Settings group used by settings_fields() on the form.
	 */
	public const GROUP = 'odwc_settings_group';

	/**
	 * Default values. Every key the plugin reads must have a default here.
	 *
	 * @return array<string, int>
	 */
	public static function defaults(): array {
		return array(
			'low_stock_threshold' => 5,
			'recent_orders_limit' => 10,
		);
	}

	/**
	 * Read a single setting, falling back to its default.
	 *
	 * @param string $key Setting key.
	 * @return mixed
	 */
	public function get( string $key ) {
		$saved    = get_option( self::OPTION_NAME, array() );
		$settings = wp_parse_args( is_array( $saved ) ? $saved : array(), self::defaults() );

		return $settings[ $key ] ?? null;
	}

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Register the option with the Settings API.
	 *
	 * @return void
	 */
	public function register_settings(): void {
		/*
		 * TODO (Day 7): register the option and its fields.
		 *
		 * 1. register_setting( self::GROUP, self::OPTION_NAME, array(
		 *        'type'              => 'array',
		 *        'sanitize_callback' => array( $this, 'sanitize' ),
		 *        'default'           => self::defaults(),
		 *    ) );
		 * 2. add_settings_section() and add_settings_field() for each setting,
		 *    or render the form fields yourself in a view.
		 *
		 * Docs: https://developer.wordpress.org/plugins/settings/settings-api/
		 */
	}

	/**
	 * Clean user input before it is saved. Never trust $_POST.
	 *
	 * @param mixed $input Raw submitted values.
	 * @return array<string, int>
	 */
	public function sanitize( $input ): array {
		/*
		 * TODO (Day 7): validate each field.
		 * - Cast to int with absint().
		 * - Clamp to sensible ranges (e.g. threshold 0–1000, orders 1–50).
		 * - Fall back to defaults() for anything missing or invalid.
		 * Write a unit test for this method first; it is pure logic.
		 */
		unset( $input );
		return self::defaults();
	}
}
