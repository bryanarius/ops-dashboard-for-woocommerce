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

## Queries live in data classes, markup in views

`includes/data/` returns plain arrays and never prints HTML. `Admin_Page::render()` checks permissions, gathers the data, and includes `admin/views/dashboard.php`, which only loops and escapes. It is close to MVC, with WordPress hooks doing the routing. Keeping the layers apart is what lets the AJAX handler reuse the same `Inventory` query as the page load.

## Refunds are excluded with `'type' => 'shop_order'`

WooCommerce stores a refund as its own order object with a negative total and a "completed" status, and `wc_get_orders()` returns refunds unless told otherwise. Before this was fixed, refunds showed up as rows in Recent orders and pulled the sales totals down (one day showed -$153 from 6 "orders"). The bug only appeared once the demo-data seeder created refunded orders, which is the main argument for seeding realistic data.

## Paid statuses come from `wc_get_is_paid_statuses()`

Sales only count orders WooCommerce considers paid (processing and completed by default). Asking WooCommerce instead of hard-coding the list means custom paid statuses added by other plugins are respected.

## "Today" starts at midnight in the store's timezone

The periods are calendar days, not rolling 24-hour windows: "Last 7 days" means today plus the 6 days before, starting at midnight. Midnight comes from `current_datetime()`, which uses the timezone in Settings → General, not the server's. A store in New York on a UTC server would otherwise have "today" start at 7 or 8 pm.

## Settings are clamped to a range, not passed through `absint()`

`absint()` turns `-50` into `50`, which is the wrong correction for a threshold. `Settings::sanitize()` casts to int and clamps instead: threshold 0–1000, recent orders 1–50. Missing or non-numeric values fall back to the defaults, and unknown keys are dropped. The ranges live in `Settings::ranges()` so the sanitizer and the form's `min`/`max` can't disagree.

## Shop managers can save the settings

The form posts to `options.php`, which requires `manage_options` (administrators only) by default. Shop managers can see the dashboard but would get "not allowed" on save. The `option_page_capability_odwc_settings_group` filter lowers that to `manage_woocommerce` for this plugin's settings group only, not for any other option.

## AJAX: nonce, then capability, then sanitize; JSON out, `textContent` in the browser

`admin-ajax.php` does not check the menu's capability, so the handler does all three checks itself, in order. It is registered only on `wp_ajax_`, with no `nopriv` variant, so logged-out visitors can't reach it. It returns data rather than HTML, and `admin.js` builds rows with `textContent`, the JavaScript equivalent of `esc_html()`.

## Variations are not checked for low stock

The low-stock query covers top-level products. Variable products usually track stock per variation, so a low "Hoodie – Red" won't appear. Including variations is a planned improvement; it needs the parent name in each row so the list stays readable.

## Sales totals are not cached yet

`get_totals()` loads every paid order in the period and sums them in PHP. That is fine for small stores and simple to verify, but slow for stores with thousands of orders a month. The next step is caching each total in a transient for a few minutes.

## Demo data is tagged and the seeder runs only locally

`bin/seed-demo-data.php` tags everything it creates with `_odwc_seed` meta, so re-running it or `--reset` never touches real products or orders. It refuses to run outside the command line or on a non-local host, and it turns off emails and automatic stock reduction while seeding.
