<?php
/**
 * Checks that the admin page registers its hooks.
 */

namespace OpsDashboardWC\Tests\Unit;

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

	public function test_assets_are_not_loaded_on_other_admin_pages(): void {
		$page = new Admin_Page( new Settings() );

		// If enqueue_assets() tried to load anything here, Brain Monkey would
		// fail the test because wp_enqueue_style() is not defined.
		$page->enqueue_assets( 'edit.php' );

		$this->addToAssertionCount( 1 );
	}
}
