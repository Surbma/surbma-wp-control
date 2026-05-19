# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

**Surbma | WP Control** is a WordPress plugin published on WordPress.org. It is network-activated for WordPress Multisite. As of **v23.0** (2026-05-20), it provides an admin menu in each site’s wp-admin (not Network Admin on multisite), with placeholder pages for future features gated on `SURBMA_WP_CONTROL_*` constants in `wp-config.php`.

The plugin is a stripped-down public companion to the private **PWP Control** must-use plugin (described in the parent `agent-os/` standards). Do not confuse them: this plugin uses the `SURBMA_WP_CONTROL_` constant prefix, not `PWP_CONTROL_`.

## Current Code (v23.0)

**Bootstrap** ([`surbma-wp-control.php`](surbma-wp-control.php)):

1. Plugin header (`Network: True`, text domain `surbma-wp-control`)
2. Direct access guard
3. `SURBMA_WP_CONTROL_PLUGIN_DIR` and `SURBMA_WP_CONTROL_PLUGIN_URL` constants
4. `load_plugin_textdomain()` on the `init` hook
5. `includes/all-admin.php` when `is_admin()`

**Admin** (`includes/`):

- `all-admin.php` — loads admin menu and page callbacks
- `admin-menu.php` — `WP Control` top-level menu + Dashboard, Images & thumbnails, External link checker (site wp-admin only on multisite)
- `pages/dashboard.php` — empty dashboard shell
- `pages/images-thumbnails.php` — Images & thumbnails page (combined sizes + usage table per site)
- `images-thumbnails-usage.php` — registered size helpers, metadata/content usage scanners, transient cache
- `pages/external-link-checker.php` — placeholder for upcoming feature

**Images & thumbnails page:**

- One combined table per site: Size name, Dimensions, Crop, Status (enabled/disabled), In library, In content.
- **In library:** attachments with each size slug in `_wp_attachment_metadata['sizes']`.
- **In content:** block `sizeSlug`, shortcode/JSON patterns, dimension suffixes in `post_content` and matching `postmeta`.
- **Active image sizes** list shown below each site’s table.
- Cache: transient `surbma_wp_control_media_cleaner_{blog_id}`, default 1h; filter `surbma_wp_control_media_cleaner_cache_ttl`; refresh via `?refresh=1`.
- **Multisite:** no Network Admin menu; use each site’s wp-admin.

## Project Standards

Follow the standards defined in the parent WordPress installation at:
`../../../agent-os/standards/`

Key standards that apply here:
- **Direct access prevention**: every PHP file opens with `defined( 'ABSPATH' ) || die;`
- **Defensive constants**: wrap `define()` in `if ( ! defined(...) )` guards
- **Boolean constants**: check with `! defined(...) || false === CONSTANT` pattern
- **Conditional loading**: use `is_admin()` / `is_multisite()` nesting (see `environment-branching.md`)
- **Admin UI**: WordPress core admin styles only (`.wrap`, `.card`, `.title`) — no external CSS frameworks

Note: The constant prefix for this public plugin is `SURBMA_WP_CONTROL_`, not `PWP_CONTROL_`.

## Repository Structure

```
surbma-wp-control.php   ← bootstrap
includes/               ← admin menu, assets, pages
readme.txt              ← WordPress.org description and changelog
README.md               ← GitHub description
.distignore             ← files excluded from SVN deployment
.github/workflows/      ← CI/CD to WordPress.org SVN
.wordpress-org/         ← banner images for WordPress.org
```

No build step, no composer, no npm. Pure PHP.

## Versioning

Versions use a simple `MAJOR.0` scheme (e.g. `20.0`, `21.0`, `22.0`). Increment major for any new feature or breaking removal. Update both:
1. `Version:` header in `surbma-wp-control.php`
2. `Stable tag:` and `== Changelog ==` in `readme.txt`

## Deployment

Releases deploy automatically to WordPress.org SVN via GitHub Actions:
- **Tag push** → `wporg-deploy.yml` deploys the release
- **Push to `trunk`** → `wporg-readme-assets-update.yml` syncs readme and banner assets only

To release: bump version in both files, commit to `master`, push a version tag (e.g. `git tag 24.0 && git push origin 24.0`).

## Adding Features

If adding new optional features:
1. Gate them on a constant: `if ( defined( 'SURBMA_WP_CONTROL_FEATURE_NAME' ) ) { ... }`
2. Users define the constant in `wp-config.php` to opt in
3. Add code under `includes/` following `include-file-naming.md` when complexity grows
