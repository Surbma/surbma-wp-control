# AGENTS.md

## Cursor Cloud specific instructions

### Overview

Single-file WordPress plugin (`surbma-wp-control.php`) — no build step, no npm, no composer in-repo. Requires a WordPress Multisite installation to run/test.

### Services

| Service | Location | Notes |
|---------|----------|-------|
| MariaDB | localhost:3306 | DB: `wordpress`, user: `wp`, pass: `wp` |
| WordPress Multisite | `/var/www/wordpress` | Runs on `localhost:8080` via PHP built-in server |
| Plugin symlink | `/var/www/wordpress/wp-content/plugins/surbma-wp-control` → `/workspace` | |

### Starting the environment

```bash
# Start MariaDB (if not running)
sudo mysqld_safe &

# Start PHP dev server
php -S localhost:8080 -t /var/www/wordpress
```

### Lint

```bash
# PHP syntax check
php -l surbma-wp-control.php

# WordPress coding standards (PHPCS)
phpcs --standard=WordPress surbma-wp-control.php
```

### Testing with WP-CLI

```bash
cd /var/www/wordpress

# Verify plugin is active
wp plugin list --network

# Run arbitrary PHP in WP context
wp eval "echo SURBMA_WP_CONTROL_PLUGIN_DIR;"

# Network activate/deactivate
wp plugin activate surbma-wp-control --network
wp plugin deactivate surbma-wp-control --network
```

### Gotchas

- The plugin is symlinked into WP. Edits in `/workspace` are immediately reflected — no copy step needed.
- The PHP built-in server does not support `.htaccess` rewrites; pretty permalinks won't work but admin/plugin loading is fine.
- Plugin uses `Network: True` header — must be **network-activated**, not per-site activated.
- WP admin credentials: `admin` / `admin`.
