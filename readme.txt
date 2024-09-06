=== Surbma | WP Control ===
Contributors: Surbma, CherryPickStudios
Donate link: https://surbma.com/donate/
Tags: multisite, network
Requires at least: 5.4
Tested up to: 6.6
Stable tag: 21.0
Requires PHP: 7.4
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Very useful fixes and add-ons for WordPress Multisite installations.

== Description ==

This is a very complex plugin, which is very useful for any installations, but mainly for Multisite Networks. I have created this plugin for my own WordPress installations and I'm continue to add more useful functions to it in the future. This plugin is not for the average WordPress user, but for a WordPress developer or site managers. You need a basic knowledge to use WordPress Multisite, FTP and other things to fully take advantage of this plugin.

**Here is a list of the current functions:**

- Global Google Analytics tracking for all your blogs. You have to define the SURBMA_PWP_CONTROL_GOOGLE_ANALYTICS constant in the wp-config.php file. Just use the UA code, nothing else!

**Do you want to contribute or help improving this plugin?**

You can find it on GitHub: [https://github.com/Surbma/surbma-wp-control](https://github.com/Surbma/surbma-wp-control)

**You can find my other plugins and projects on GitHub:**

[https://github.com/Surbma](https://github.com/Surbma)

Please feel free to contribute, help or recommend any new features for my plugins, themes and other projects.

**Do you want to know more about me?**

Visit my webpage: [Surbma.com](https://surbma.com/)

== Installation ==

1. Upload `surbma-wp-control` folder to the `/wp-content/plugins/` directory
2. Activate the Surbma | WP Control plugin through the 'Plugins' menu in WordPress
3. That's it. :)

== Frequently Asked Questions ==

= What is this plugin good for? =

This plugin was created especially for my Multisite Networks, but works for all WordPress installations. It can control some minor, but important parts of a WordPress website, which is very useful if you handle a lot of client websites.

= What does Surbma mean? =

It is the reverse version of my last name. ;)

== Changelog ==

= 21.0 =

Release date: 2024-09-06

OTHER

- Removed all functions, except global Google Analytics tracking.

= 20.0 =

Release date: 2024-08-11

OTHER

- Removed: List all available translations on the Updates page.
- Removed: Removes some unwanted Widgets.
- Removed: Removes some unwanted Dashboard Widgets.
- Removed: Disables Welcome Screen.
- Removed: Removes version number from source code.
- Removed: Removes version number from admin footer.
- Removed: Removes WP logo menu from admin bar.
- Removed: Adds language code in body class if WPML plugin is activated.
- Removed: Fixes links and titles in the password reset emails in a Multisite Network.
- Removed: Redirects all attachment pages to its parent page or to homepage.
- Removed: Custom directives for virtual robots.txt file.
- Removed: Show site ID on network admin all sites page.
- Removed: Adds link to read more text.
- Tested up to WordPress 6.6 version.

= 19.0 =

Release date: 2024-06-11

OTHER

- Removed "Clean file names on upload" filter.
- Tested up to WordPress 6.5 version.

= 18.0 =

Release date: 2023-11-16

ENHANCEMENT

- Plugin manager's plugin list set to alphabetical order.

OTHER

- Login page customization functions are removed.

= 17.1 =

Release date: 2023-08-31

FIX

- Fixed login styling for the password field.

OTHER

- Tested up to WordPress 6.3 version.

= 17.0 =

Release date: 2023-03-12

NEW

- Remove RSD link from source code.
- Remove Windows Live Writer meta tag from source code.

OTHER

- Remove the generator meta tag with remove_action function.

= 16.0 =

Release date: 2023-01-02

NEW

- List all available translations on the Updates page.

OTHER

- Tested up to WordPress 6.1 version.

= 15.6 =

Release date: 2022-03-04

FIXES

- Fix login page button design.

= 15.5 =

Release date: 2022-02-07

FIXES

- Fix login page button design.

OTHER

- Tested up to WordPress 5.9 version.

= 15.4 =

Release date: 2021-09-18

OTHER

- Functions edited to have short form.
- Modified Soliloquy's menu capability.

= 15.3 =

Release date: 2021-08-04

NEW

- Hide LiteSpeed Cache banner from admin.

OTHER

- Tested up to WordPress 5.8 version.

= 15.2 =

Release date: 2021-03-10

FIXES

- Checking the backlink option if it is set in the database.

OTHER

- Tested up to WordPress 5.7 version.

= 15.1 =

Release date: 2021-01-10

FIXES

- Popup removal restored, as css is not specific, so it was in conflict with other essential popups.

= 15.0 =

Release date: 2020-11-29

NEW

- Removes the "Welcome to the block editor" popup from the edit page.
- Disable Gutenberg’s default fullscreen mode.

FIX

- Moved a css to global admin to load it on all pages.

= 14.1 =

Release date: 2020-05-30

- FIX - Extra Blog page content limited only for posts page.

= 14.0 =

Release date: 2020-05-30

- NEW - Adds page content to posts (blog) page.

= 13.2 =

Release date: 2020-04-01

- FIX - Theme manager fix to show the correct messages.

= 13.1 =

Release date: 2020-04-01

- FIX - Theme manager fix to show message, if all themes are in use.

= 13.0 =

- Release date: 2020-01-29
- NEW - Posts column for all sites listing page.
- NEW - Pages column for all sites listing page.
- NEW - Comments column for all sites listing page.
- NEW - Public status column for all sites listing page.

= 12.2 =

- Release date: 2020-01-29
- OTHER - Added CherryPickStudios as a new contributor.

= 12.1 =

- Release date: 2020-01-29
- New - Added .distignore file to ignore files and folders from SVN repository.
- New - Added .wordpress-org folder and assets, that will be used to update SVN assets.

= 12.0 =

- Release date: 2019-09-21
- NEW - Show SSL status on network admin all sites page.
- FIX - Site ID column is disabled in WP Engine environment.

= 11.0 =

- Release date: 2019-05-27
- NEW - Show site ID on network admin all sites page.

= 10.0 =

- Release date: 2019-05-24
- ADD - Theme and Plugin list shows if a subsite is deleted.

= 9.1 =

- Release date: 2019-05-17
- FIX - Theme and Plugin manager pages optimized and fixed to work with up to 10000 sites in a Multisite network.

= 9.0 =

- Release date: 2019-05-17
- NEW - List not-used plugins on Plugin manager page.

= 8.0 =

- Release date: 2019-05-16
- CHANGE - Menu items for plugins and themes are renamed.
- NEW - List not-used themes on Theme manager page.

= 7.0 =

- Release date: 2019-05-08
- NEW - Remove WordPress core update notifications from the admin.
- ADD - Added version and link to network activated plugins also.

= 6.1 =

- Release date: 2019-05-05
- TWEAK - Added minimum required PHP version.

= 6.0 =

- Release date: 2019-05-05
- NEW - List all network actived plugins and per-site activated plugins.
- NEW - List all network enabled themes and per-site activated theme with parent theme.

= 5.1 =

- FIX - Clean filename function fix to correctly remove the extension at the end of the filename.

= 5.0 =

- NEW - Clean filenames. Removes any unwanted characters from filenames uploaded to media library.

= 4.17 =

- Simple versioning.
- Admin footer text rewrite fix.

= 4.16.0 =

- Added Crawl-delay directive for the virtual robots.txt file.
- Tested up to WordPress 4.9 version.
- PHP 7.2 compatibility check.

= 4.15.0 =

- New Global Site Tag code for Google Analytics tracking.
- Hide WooCommerce "Connect" admin notice.

= 4.14.1 =

- Login button position fix, to be compatible with other login plugins.

= 4.14.0 =

- Removed function, that removed version parameters from static files.

= 4.13.0 =

- Removed version number from admin footer.
- Removed WP logo menu from admin bar.
- Tested up to WordPress 4.7 version.

= 4.12.0 =

- Added IP Anonymization option to Global Google Analytics.

= 4.11.2 =

- Fixed footer text customization for HTML5 Genesis themes.
- Tested up to WordPress 4.7 version.

= 4.11.1 =

- Admin footer text function fixed to display the original text if constant is not defined.

= 4.11.0 =

- Custom admin footer text.
- Added admin menu debug block in the WP Control page.
- Tested up to WordPress 4.6 version.

= 4.10.0 =

- Minor changes in custom login style.
- Attachment redirect fix to work with multilingual sites.

= 4.9.1 =

- Fix for global Google Analytics tracking, when network activated with Surbma - Premium WordPress plugin.

= 4.9.0 =

- Remove the WooThemes Helper notice from the admin.

= 4.8.1 =

- Fixed CSS for login page.

= 4.8.0 =

- Code optimization on admin page.
- NEW - Added "Lost your password?" link next to "Log In" button on log in page.
- Removed "Remember Me" option from log in page.
- NEW - Redirect all attachments to its parent page or to homepage.
- Tested up to WordPress 4.5 version.
- Tested with PHP 7 version.

= 4.7.2 =

- Changed a hook to follow Surbma - Premium WP plugin's changes.

= 4.7.1 =

- Fixed undefined variable error.

= 4.7.0 =

- Added admin styles from [Surbma - Premium WP](https://wordpress.org/plugins/surbma-premium-wp/) plugin.
- Fixed textdomain for localization.

= 4.6.0 =

- Override default Genesis favicon, if there is a favicon in the main WordPress folder. File formats accepted: favicon.ico, favicon.gif, favicon.png, favicon.jpg.

= 4.5.2 =

- Fixed textdomain path for localization.

= 4.5.1 =

- Fixed function names for Global Google Analytics to get data from [Surbma - Premium WP](https://wordpress.org/plugins/surbma-premium-wp/) plugin.

= 4.5.0 =

- Removed the WP Engine dashboard widget.

= 4.4.2 =

- Enhanced array readability on admin page.

= 4.4.1 =

- Removed some unnecessary images.

= 4.4.0 =

- Tested up to WordPress 4.3 version.
- Specific style added to separate css file and loading only on WP Control page.
- Changed menu icons.
- Fixed all urls and site titles related to password reset on a Multisite network.
- Code optimization on WP Control page.

= 4.3.0 =

- Protect module of Jetpack is still disabled by default, but can be enabled by defining a constant: SURBMA_WP_CONTROL_JETPACK_ENABLE_PROTECT

= 4.2.0 =

- WP Control menu will be a submenu, if Surbma - Premium WP plugin is activated.
- Turn off comments on Attachement pages.
- Remove post info and post meta on Attachement pages, when Genesis theme is used.
- Force layout to full-width on Attachement pages, when Genesis theme is used.

= 4.1.0 =

- Remove Protect module from the available modules of Jetpack.

= 4.0.0 =

- First commit to official WordPress repo.
