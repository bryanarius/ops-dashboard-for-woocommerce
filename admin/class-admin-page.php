<?php
/**
 * The dashboard admin page.
 *
 * @package OpsDashboardWC
 */

namespace OpsDashboardWC\Admin;

use OpsDashboardWC\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the WooCommerce > Ops Dashboard screen and loads its assets.
 */
class Admin_Page {

	/**
	 * Admin page slug (?page=...).
	 */
	public const SLUG = 'odwc-dashboard';

	/**
	 * Capability required to see the dashboard. Shop managers have it,
	 * customers and subscribers do not.
	 */
	public const CAPABILITY = 'manage_woocommerce';

	/**
	 * AJAX action name. Also used as the nonce action for that request.
	 */
	public const AJAX_ACTION = 'odwc_filter_low_stock';

	/**
	 * Hook suffix returned by add_submenu_page(), used to load assets only on our page.
	 *
	 * @var string
	 */
	private $hook_suffix = '';

	/**
	 * Settings.
	 *
	 * @var Settings
	 */
	private $settings;

	/**
	 * Constructor.
	 *
	 * @param Settings $settings Plugin settings.
	 */
	public function __construct( Settings $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		// Logged-in users only. There is deliberately no wp_ajax_nopriv_ hook.
		add_action( 'wp_ajax_' . self::AJAX_ACTION, array( $this, 'ajax_filter_low_stock' ) );
	}

	/**
	 * AJAX: low-stock products, optionally filtered by category.
	 *
	 * Expects POST fields: nonce, category (slug, may be empty).
	 * Responds with { success: true, data: [ rows ] } or { success: false }.
	 *
	 * @return void
	 */
	public function ajax_filter_low_stock(): void {
		// 1. Nonce: the request came from our dashboard page. Dies with a 403 if not.
		check_ajax_referer( self::AJAX_ACTION, 'nonce' );

		// 2. Capability: admin-ajax.php does not check our menu capability for us.
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_send_json_error( null, 403 );
			return;
		}

		// 3. Sanitize: a category slug only ever contains lowercase letters, numbers and dashes.
		$category = isset( $_POST['category'] ) ? sanitize_title( wp_unslash( $_POST['category'] ) ) : '';

		$rows = ( new \OpsDashboardWC\Data\Inventory() )->get_low_stock_products(
			(int) $this->settings->get( 'low_stock_threshold' ),
			20,
			$category
		);

		wp_send_json_success( $rows );
	}

	/**
	 * Add the page under the WooCommerce menu.
	 *
	 * @return void
	 */
	public function add_menu_page(): void {
		$hook_suffix = add_submenu_page(
			'woocommerce',
			__( 'Ops Dashboard', 'ops-dashboard-for-woocommerce' ),
			__( 'Ops Dashboard', 'ops-dashboard-for-woocommerce' ),
			self::CAPABILITY,
			self::SLUG,
			array( $this, 'render' )
		);

		$this->hook_suffix = is_string( $hook_suffix ) ? $hook_suffix : '';
	}

	/**
	 * Load CSS/JS only on our page, never site-wide.
	 *
	 * @param string $hook_suffix Current admin page hook suffix.
	 * @return void
	 */
	public function enqueue_assets( string $hook_suffix ): void {
		if ( '' === $this->hook_suffix || $hook_suffix !== $this->hook_suffix ) {
			return;
		}

		wp_enqueue_style( 'odwc-admin', ODWC_URL . 'assets/css/admin.css', array(), ODWC_VERSION );
		wp_enqueue_script( 'odwc-admin', ODWC_URL . 'assets/js/admin.js', array(), ODWC_VERSION, true );

		// Gives admin.js what it needs for the AJAX request, as a global odwcData object.
		$data = array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'action'  => self::AJAX_ACTION,
			'nonce'   => wp_create_nonce( self::AJAX_ACTION ),
			'i18n'    => array(
				'error' => __( 'Could not load products. Please reload the page and try again.', 'ops-dashboard-for-woocommerce' ),
			),
		);

		wp_add_inline_script( 'odwc-admin', 'const odwcData = ' . wp_json_encode( $data ) . ';', 'before' );
	}

	/**
	 * Render the page.
	 *
	 * @return void
	 */
	public function render(): void {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'You do not have permission to view this page.', 'ops-dashboard-for-woocommerce' ) );
		}

		$settings = $this->settings;

		$recent_orders = ( new \OpsDashboardWC\Data\Orders() )->get_recent_orders(
			(int) $settings->get( 'recent_orders_limit' )
		);

		$sales_summary = new \OpsDashboardWC\Data\Sales_Summary();
		$sales         = array();

		// Same list flush_cache() clears, so every cached period gets cleared.
		foreach ( \OpsDashboardWC\Data\Sales_Summary::PERIODS as $days ) {
			$sales[ $days ] = $sales_summary->get_totals( $days );
		}

		$inventory  = new \OpsDashboardWC\Data\Inventory();
		$low_stock  = $inventory->get_low_stock_products(
			(int) $settings->get( 'low_stock_threshold' )
		);
		$categories = $inventory->get_categories();

		include ODWC_PATH . 'admin/views/dashboard.php';
	}
}
