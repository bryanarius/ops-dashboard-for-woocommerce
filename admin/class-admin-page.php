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

		/*
		 * TODO (Day 7): register an AJAX handler for filtering, e.g.
		 * add_action( 'wp_ajax_odwc_filter_low_stock', array( $this, 'ajax_filter_low_stock' ) );
		 * In the handler: check_ajax_referer(), current_user_can( self::CAPABILITY ),
		 * sanitize input, then wp_send_json_success().
		 * (Alternative: a REST route with register_rest_route() and a permission_callback.)
		 */
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

		/*
		 * TODO (Day 7): pass data to admin.js, e.g.
		 * wp_add_inline_script( 'odwc-admin', 'const odwcData = ' . wp_json_encode( array(
		 *     'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		 *     'nonce'   => wp_create_nonce( 'odwc_filter' ),
		 * ) ) . ';', 'before' );
		 */
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
		$sales         = array(
			1  => $sales_summary->get_totals( 1 ),
			7  => $sales_summary->get_totals( 7 ),
			30 => $sales_summary->get_totals( 30 ),
		);

		$low_stock = ( new \OpsDashboardWC\Data\Inventory() )->get_low_stock_products(
			(int) $settings->get( 'low_stock_threshold' )
		);

		include ODWC_PATH . 'admin/views/dashboard.php';
	}
}
