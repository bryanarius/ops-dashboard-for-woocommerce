/**
 * Ops Dashboard for WooCommerce: admin script.
 * Loaded only on the dashboard page (see Admin_Page::enqueue_assets()).
 *
 * odwcData (ajaxUrl, action, nonce, i18n) is printed just before this file
 * by Admin_Page::enqueue_assets().
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		const select = document.getElementById( 'odwc-low-stock-category' );
		const table = document.getElementById( 'odwc-low-stock-table' );
		const empty = document.getElementById( 'odwc-low-stock-empty' );

		if ( ! select || ! table || ! empty || 'undefined' === typeof odwcData ) {
			return;
		}

		const tbody = table.querySelector( 'tbody' );
		const emptyText = empty.textContent;

		select.addEventListener( 'change', function () {
			const body = new FormData();
			body.append( 'action', odwcData.action );
			body.append( 'nonce', odwcData.nonce );
			body.append( 'category', select.value );

			select.disabled = true;

			fetch( odwcData.ajaxUrl, { method: 'POST', body: body, credentials: 'same-origin' } )
				.then( function ( response ) {
					return response.json();
				} )
				.then( function ( json ) {
					if ( ! json || ! json.success ) {
						throw new Error( 'Request failed' );
					}
					renderRows( json.data );
				} )
				.catch( function () {
					showMessage( odwcData.i18n.error );
				} )
				.finally( function () {
					select.disabled = false;
				} );
		} );

		/**
		 * Replace the table rows. Uses textContent, never innerHTML, so product
		 * names and SKUs can't inject HTML or scripts (the JS version of esc_html()).
		 *
		 * @param {Array<{name:string, sku:string, stock:number, edit_url:string}>} rows Products from the AJAX response.
		 */
		function renderRows( rows ) {
			tbody.replaceChildren();

			if ( 0 === rows.length ) {
				showMessage( emptyText );
				return;
			}

			rows.forEach( function ( row ) {
				const tr = document.createElement( 'tr' );

				const nameCell = document.createElement( 'td' );
				const link = document.createElement( 'a' );
				link.href = row.edit_url;
				link.textContent = row.name;
				nameCell.appendChild( link );

				const skuCell = document.createElement( 'td' );
				skuCell.textContent = row.sku;

				const stockCell = document.createElement( 'td' );
				stockCell.textContent = row.stock;

				tr.append( nameCell, skuCell, stockCell );
				tbody.appendChild( tr );
			} );

			table.hidden = false;
			empty.hidden = true;
		}

		/**
		 * Hide the table and show a message instead.
		 *
		 * @param {string} text Message to show.
		 */
		function showMessage( text ) {
			empty.textContent = text;
			empty.hidden = false;
			table.hidden = true;
		}
	} );
}() );
