=== webGefaehrte Custom Functionality Plugin ===
Contributors: locke85
Tags: custom functionality, shortcodes, generatepress, smtp, multisite
Requires at least: 5.8
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 2.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Shared WordPress website functionality for the webGefaehrte group without mixing in membership, checkout or LMS logic.

== Description ==

The webGefaehrte Custom Functionality Plugin centralizes shared website functionality across multiple WordPress installations. It provides reusable editorial helpers, shortcodes, metaboxes and GeneratePress-related enhancements while keeping membership-specific logic out of the plugin.

= Included functionality =

* Page excerpts for pages.
* TOC shortcode and automatic H2 anchors.
* Reading time shortcode.
* Header and CTA metaboxes.
* Scoped Contact Form 7 redirects.
* SMTP support via wp-config.php constants.
* Font Awesome loading from the plugin or a filtered URL.
* Ratgeber permalinks for posts.
* Podcast show notes tooling and the `wg_guest` shortcode.
* Main-site-only chat and sharing-news CPT modules.
* Chat archive helpers, `display_post_type` and `chat_category_grid`.
* Optional HTML support for user profile descriptions via filter.
* Server-rendered GenerateBlocks author profile fallbacks on empty author archives.

= Main-site-only modules =

On multisite installations, some modules only initialize on the main site:

* `wg_seo_chat` with `wg_chat_category`, custom chat permalinks and hardened chat metabox handling.
* `wg_sharing_news` with hardened news metabox handling.
* Podcast show notes, chat archives and optional user profile HTML support.
* The compatible `wg_seo_chat_form` shortcode as a secure editor-only frontend intake using `admin-post`.

Legacy guest submission, open AJAX handlers, debug logging and theme-coupled admin assets are intentionally not part of the current CFP implementation.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/` or install the ZIP package via the WordPress plugin uploader.
2. Activate the plugin in WordPress.
3. If you use the ratgeber permalink feature or the chat CPT permalinks, save the permalink settings once after activation.

== Frequently Asked Questions ==

= Does the plugin depend on wg-membership-suite? =

No. CFP must also work on sites without the membership suite.

= Does the plugin require GeneratePress, SSP, Yoast, MailPoet or ACF? =

No. CFP contains defensive integrations and must not fatal if those plugins or themes are missing.

= How is the frontend chat form protected? =

The `wg_seo_chat_form` shortcode is only available to logged-in users with `edit_posts`. Submission uses a nonce-protected `admin-post` request and stores new chats as `pending`.

= How are author archive profile fields resolved? =

CFP uses the installed GenerateBlocks server-side dynamic tag replacement filters. It only fills unresolved `author_avatar_url` and whitelisted `author_meta` values (`display_name`, `first_name`, `last_name`) on real author archives. Existing GenerateBlocks profile data is preserved; a missing avatar alt text is added for accessibility. The avatar comes from the WordPress avatar API, defaults to 150px and can be configured with `WG_CFP_AUTHOR_ARCHIVE_AVATAR_SIZE` or the `wg_cfp_author_archive_avatar_size` filter.

== Changelog ==

= 2.0.2 =
* Added server-rendered GenerateBlocks author profile fallbacks for author archives without posts.
* Resolve avatars through the WordPress avatar API for multisite compatibility.
* Preserve existing GenerateBlocks output and add accessible alt text to fallback avatar images.

= 2.0.1 =
* Added main-site-only CPT modules for `wg_seo_chat` and `wg_sharing_news`.
* Added hardened chat and news metabox handling inside CFP.
* Replaced the legacy open chat AJAX flow with a nonce-protected editor-only frontend intake using `admin-post`.
* Documented the new main-site module boundaries and excluded legacy debug and upload behavior.

= 2.0.0 =
* Introduced modular CFP loading with main-site-only initialization for site-specific modules.
* Added podcast show notes tooling with the compatible `wg_guest` shortcode.
* Added chat archive helpers, shortcodes and guarded taxonomy field registration.
* Consolidated shared GeneratePress helpers and reduced smooth scroll to the safe selector.

= 1.7.0 =
* Removed membership-specific `wg_angebot` author support from CFP.
* Deduplicated page excerpt support and hardened metabox saving.
* Scoped Contact Form 7 redirects and decoupled Font Awesome from the child theme.
* Added ratgeber permalink support for posts with safer fallback behavior.

= 1.6.9 =
* Fixed a fatal error caused by an invalid `save_post` callback.
* Fixed a conditional assignment bug.
* Improved PHP 8 compatibility.

== Upgrade Notice ==

= 2.0.2 =
Adds server-rendered, multisite-safe author profile fallbacks for GenerateBlocks author archives.
