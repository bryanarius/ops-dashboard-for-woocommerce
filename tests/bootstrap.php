<?php
/**
 * PHPUnit bootstrap.
 *
 * Unit tests run WITHOUT WordPress. Brain Monkey fakes WordPress functions
 * (add_action, get_option, ...) so pure logic can be tested fast.
 * For tests that need a real WordPress + WooCommerce, look at wp-env and
 * the WordPress PHPUnit test suite later.
 */

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

define( 'ABSPATH', __DIR__ . '/' );
define( 'ODWC_VERSION', 'test' );
define( 'ODWC_PATH', dirname( __DIR__ ) . '/' );
define( 'ODWC_URL', 'https://example.test/wp-content/plugins/ops-dashboard-for-woocommerce/' );
