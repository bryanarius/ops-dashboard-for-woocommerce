/**
 * Ops Dashboard for WooCommerce: admin script.
 * Loaded only on the dashboard page (see Admin_Page::enqueue_assets()).
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		/*
		 * TODO (Day 7): low-stock filtering.
		 * 1. Listen for changes on a filter <select> in the low-stock panel.
		 * 2. fetch( odwcData.ajaxUrl, { method: 'POST', body: FormData with
		 *    action=odwc_filter_low_stock, nonce=odwcData.nonce, category=... } )
		 * 3. Re-render the table rows from the JSON response.
		 *    Build DOM nodes with textContent, not innerHTML, to avoid XSS.
		 */
	} );
}() );
