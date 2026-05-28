# Tech Stack

## Backend

- **PHP** — pure PHP WordPress plugin, no Composer, no build step
- **WordPress APIs** — hooks, transients, multisite functions, admin menus, attachment metadata

## Frontend

- **Vanilla JavaScript / jQuery** — for admin UI interactions where needed
- **WordPress core admin styles** — `.wrap`, `.card`, `.title`, and standard WP admin CSS only; no external CSS frameworks

## Infrastructure

- **WordPress Multisite** — network-activated; features appear in each site's wp-admin (not Network Admin)
- **WordPress.org SVN** — deployment via GitHub Actions CI/CD on tag push
- **GitHub** — source control and CI/CD pipeline

## Constraints

- No npm, no Composer, no build pipeline
- No external CSS frameworks (Bootstrap, Tailwind, etc.)
- WordPress core admin UI patterns only
