# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

**Surbma | WP Control** is a single-file public WordPress plugin published on WordPress.org. It is network-activated for WordPress Multisite. As of **v22.0** (2026-05-19), it has no active features — only scaffolding (constants, text domain) for future optional features gated on `SURBMA_WP_CONTROL_*` constants in `wp-config.php`.

The plugin is a stripped-down public companion to the private **PWP Control** must-use plugin (described in the parent `agent-os/` standards). Do not confuse them: this plugin uses the `SURBMA_WP_CONTROL_` constant prefix, not `PWP_CONTROL_`.

## Current Code (v22.0)

`surbma-wp-control.php` contains only:

1. Plugin header (`Network: True`, text domain `surbma-wp-control`)
2. Direct access guard
3. `SURBMA_WP_CONTROL_PLUGIN_DIR` and `SURBMA_WP_CONTROL_PLUGIN_URL` constants
4. `load_plugin_textdomain()` on the `init` hook

## Project Standards

Follow the standards defined in the parent WordPress installation at:
`../../../agent-os/standards/`

Key standards that apply here:
- **Direct access prevention**: every PHP file opens with `defined( 'ABSPATH' ) || die;`
- **Defensive constants**: wrap `define()` in `if ( ! defined(...) )` guards
- **Boolean constants**: check with `! defined(...) || false === CONSTANT` pattern
- **Conditional loading**: use `is_admin()` / `is_multisite()` nesting (see `environment-branching.md`)
- **Admin UI**: UIKit 3 via CDN, load assets only on plugin pages, use `.wrap > .pwp-control` wrapper with `uk-card` components

Note: The constant prefix for this public plugin is `SURBMA_WP_CONTROL_`, not `PWP_CONTROL_`.

## Repository Structure

```
surbma-wp-control.php   ← entire plugin (single file, no includes/)
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

To release: bump version in both files, commit to `master`, push a version tag (e.g. `git tag 22.0 && git push origin 22.0`).

## Adding Features

If adding new optional features:
1. Gate them on a constant: `if ( defined( 'SURBMA_WP_CONTROL_FEATURE_NAME' ) ) { ... }`
2. Users define the constant in `wp-config.php` to opt in
3. Keep all code in `surbma-wp-control.php` unless complexity warrants an `includes/` directory — follow the `include-file-naming.md` standard if you add includes
