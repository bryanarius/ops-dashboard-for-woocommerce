# Ops Dashboard for WooCommerce

[![CI](https://github.com/bryanarius/ops-dashboard-for-woocommerce/actions/workflows/ci.yml/badge.svg)](https://github.com/bryanarius/ops-dashboard-for-woocommerce/actions/workflows/ci.yml)

A WordPress plugin that gives WooCommerce store managers one admin screen for the numbers they check every day: what's running out of stock, what just sold, and how sales are trending. No clicking through several WooCommerce pages.

> **Version 1.0.0.** All planned features are built and tested. See the [roadmap](#roadmap) for what's next.

![Ops Dashboard showing sales totals, low-stock products, recent orders and settings](docs/screenshot.jpg)

## Features

- **Sales summary:** revenue and paid-order count for today, the last 7 days, and the last 30 days, in the store's timezone and currency
- **Low-stock panel:** products at or below a threshold, lowest first, each linking to its edit screen
- **Category filter:** narrows the low-stock list instantly over AJAX, without reloading the page
- **Recent orders:** number, date, status, customer and total, linking to each order
- **Settings:** low-stock threshold and number of recent orders, editable by shop managers as well as admins

## Highlights

- **HPOS-compatible.** All data goes through WooCommerce's CRUD APIs (`wc_get_orders()`, `wc_get_products()`), never raw SQL, so it works whether orders live in `wp_posts` or WooCommerce's own tables.
- **Security at every entry point.** Capability checks on the page and the AJAX handler, nonces on the settings form and AJAX requests, input clamped to safe ranges, and all output escaped (including `textContent` on the JavaScript side).
- **A real bug, found and fixed.** Seeding realistic demo data showed refunds being counted as orders with negative totals. Queries now filter `'type' => 'shop_order'`.
- **Tested and linted in CI.** PHPUnit with Brain Monkey for unit tests, and PHPCS with WordPress Coding Standards, run on PHP 8.1–8.3 by GitHub Actions.
- **Decisions written down.** [docs/decisions.md](docs/decisions.md) explains the trade-offs, from timezone handling to why `absint()` was the wrong sanitizer.

## Built with

- PHP 8.1+, WordPress 6.5+, WooCommerce 8.2+
- WooCommerce CRUD APIs (`wc_get_products()`, `wc_get_orders()`); HPOS-compatible
- PHPUnit + Brain Monkey for unit tests
- PHPCS with WordPress Coding Standards
- GitHub Actions CI on PHP 8.1–8.3

## Security

- Every screen and handler checks the `manage_woocommerce` capability
- Nonces on all form and AJAX requests
- Input sanitized on the way in, output escaped on the way out

## Project structure

```
ops-dashboard-for-woocommerce.php   Plugin header, HPOS declaration, bootstrap
uninstall.php                       Cleanup when the plugin is deleted
includes/
  class-plugin.php                  Wires classes together
  class-settings.php                Option storage, defaults, Settings API
  data/                             Read-only queries (inventory, orders, sales)
admin/
  class-admin-page.php              Menu page, assets, rendering
  views/dashboard.php               Markup only
assets/                             Admin CSS and JS
tests/Unit/                         PHPUnit tests (no WordPress needed)
bin/seed-demo-data.php              Dev tool: fills a local store with demo data
docs/decisions.md                   Design decisions and trade-offs
```

## Installation

1. Download or clone into `wp-content/plugins/ops-dashboard-for-woocommerce`.
2. Activate WooCommerce, then this plugin.
3. Go to **WooCommerce → Ops Dashboard**.

## Development

```bash
composer install
composer lint      # PHPCS (WordPress Coding Standards)
composer lint:fix  # auto-fix what PHPCS can
composer test      # PHPUnit
```

For a local store with sample data, use [LocalWP](https://localwp.com/), install WooCommerce, and import its sample products (`wp-content/plugins/woocommerce/sample-data/sample_products.csv`). Symlink this folder into the site's `wp-content/plugins/`.

### Demo data

To fill every dashboard panel, seed demo products and orders from the plugin folder:

```bash
php bin/seed-demo-data.php           # create demo data (re-running replaces it)
php bin/seed-demo-data.php --reset   # remove demo data only
```

It creates 15 products across categories with stock from 0 to 20, and 30 orders over the last 45 days in mixed statuses (including refunds and some orders from today).

- Use the PHP binary that can reach your site's database, e.g. `/Applications/XAMPP/xamppfiles/bin/php` on XAMPP.
- Everything it creates is tagged with `_odwc_seed` meta, so it never touches your own products or orders.
- It runs only from the command line and only on local hosts (`localhost`, `*.test`, `*.local`) or sites with `WP_ENVIRONMENT_TYPE` set to `local` or `development`.
- Emails and automatic stock reduction are turned off while it runs.
- If this folder is symlinked, set `WP_LOAD=/path/to/wp-load.php`.

## Roadmap

- Cache sales totals in a transient
- Include product variations in the low-stock list
- Stock-status filter
- Daily low-stock email (WP-Cron)
- CSV export of low-stock products
- REST API endpoint

## Known limitations

- **Sales totals load every paid order in the period.** Revenue is summed in PHP, so the 30-day figure can be slow on stores with thousands of orders a month. Not cached yet.
- **Variations aren't counted as low stock.** Only top-level products with stock management enabled are checked, so a low "Hoodie – Red" variation won't appear.

## License

GPL-2.0-or-later
