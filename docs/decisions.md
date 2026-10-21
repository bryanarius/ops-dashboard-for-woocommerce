# Design decisions

A short log of choices made while building this plugin, and why. Add an entry whenever you pick one approach over another. These make good interview talking points.

## Use WooCommerce APIs, never raw SQL on orders or products

Orders go through `wc_get_orders()` and products through `wc_get_products()`. WooCommerce can store orders in its own tables (HPOS) instead of `wp_posts`, so direct queries on posts/postmeta would silently return nothing on HPOS stores. The plugin declares HPOS compatibility in the main file, and this rule is what makes that declaration true.

## Capability: `manage_woocommerce`

Shop managers and administrators have it; customers and subscribers don't. Checked when registering the menu and again in `render()` (and must be checked in every AJAX/REST handler).

## Assets load only on the dashboard page

`enqueue_assets()` compares the hook suffix so the plugin adds nothing to other admin screens.

## Naming: "Ops Dashboard for WooCommerce"

WooCommerce's trademark guidance asks that third-party extensions not start with "WooCommerce"/"Woo"; the "… for WooCommerce" pattern is the accepted form. Slug and text domain: `ops-dashboard-for-woocommerce`; code prefix `odwc` / namespace `OpsDashboardWC`.

<!-- TODO: add your own entries as you go, e.g. how you defined "today" for sales totals, whether variations count as low stock, caching with transients. -->
