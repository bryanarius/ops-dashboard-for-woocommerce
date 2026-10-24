<?php
/**
 * Checks that the admin page registers its hooks.
 */

namespace OpsDashboardWC\Tests\Unit;

use Brain\Monkey\Functions;
use OpsDashboardWC\Admin\Admin_Page;
use OpsDashboardWC\Settings;
use OpsDashboardWC\Tests\TestCase;

class AdminPageTest extends TestCase {

	public function test_register_adds_menu_and_asset_hooks(): void {
		$page = new Admin_Page( new Settings() );
		$page->register();

		$this->assertNotFalse( has_action( 'admin_menu', array( $page, 'add_menu_page' ) ) );
		$this->assertNotFalse( has_action( 'admin_enqueue_scripts', array( $page, 'enqueue_assets' ) ) );
	}

	public function test_register_adds_ajax_hook_for_logged_in_users_only(): void {
		$page = new Admin_Page( new Settings() );
		$page->register();

		$this->assertNotFalse( has_action( 'wp_ajax_odwc_filter_low_stock', array( $page, 'ajax_filter_low_stock' ) ) );
		$this->assertFalse( has_action( 'wp_ajax_nopriv_odwc_filter_low_stock' ) );
	}

	public function test_ajax_checks_nonce_and_rejects_users_without_capability(): void {
		Functions\expect( 'check_ajax_referer' )->once()->with( 'odwc_filter_low_stock', 'nonce' );
		Functions\when( 'current_user_can' )->justReturn( false );
		Functions\expect( 'wp_send_json_error' )->once()->with( null, 403 );
		Functions\expect( 'wp_send_json_success' )->never();

		( new Admin_Page( new Settings() ) )->ajax_filter_low_stock();
	}

	public function test_assets_are_not_loaded_on_other_admin_pages(): void {
		$page = new Admin_Page( new Settings() );

		// If enqueue_assets() tried to load anything here, Brain Monkey would
		// fail the test because wp_enqueue_style() is not defined.
		$page->enqueue_assets( 'edit.php' );

		$this->addToAssertionCount( 1 );
	}
}
