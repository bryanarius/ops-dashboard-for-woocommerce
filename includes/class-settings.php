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
	 * Allowed range for each setting. Used by sanitize() and the form inputs.
	 *
	 * @return array<string, array{0:int, 1:int}> Setting key => array( min, max ).
	 */
	public static function ranges(): array {
		return array(
			'low_stock_threshold' => array( 0, 1000 ),
			'recent_orders_limit' => array( 1, 50 ),
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
		add_filter( 'option_page_capability_' . self::GROUP, array( $this, 'capability' ) );
	}

	/**
	 * Register the option with the Settings API.
	 *
	 * @return void
	 */
	public function register_settings(): void {
		register_setting(
			self::GROUP,
			self::OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize' ),
				'default'           => self::defaults(),
			)
		);
	}

	/**
	 * Capability needed to save these settings through options.php.
	 *
	 * By default options.php requires manage_options (administrators only).
	 * This lets shop managers, who can see the dashboard, save its settings too.
	 *
	 * @return string
	 */
	public function capability(): string {
		return 'manage_woocommerce';
	}

	/**
	 * Clean user input before it is saved. Never trust $_POST.
	 *
	 * @param mixed $input Raw submitted values.
	 * @return array<string, int>
	 */
	public function sanitize( $input ): array {
		$defaults = self::defaults();

		if ( ! is_array( $input ) ) {
			return $defaults;
		}

		$clean = array();

		foreach ( self::ranges() as $key => $range ) {
			if ( ! isset( $input[ $key ] ) || ! is_numeric( $input[ $key ] ) ) {
				$clean[ $key ] = $defaults[ $key ];
				continue;
			}

			// Not absint(): it turns -50 into 50, but a negative threshold should become 0.
			$clean[ $key ] = max( $range[0], min( $range[1], (int) $input[ $key ] ) );
		}

		return $clean;
	}
}
