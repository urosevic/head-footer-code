=== Head & Footer Code ===
Contributors: urkekg, techwebux
Donate link: https://urosevic.net/wordpress/donate/?donate_for=head-footer-code
Tags: wp_head, wp_footer, wp_body_open, head footer code, custom script
Requires at least: 4.9
Tested up to: 5.6
Stable tag: 1.2.2
Requires PHP: 5.6
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Easy add site-wide and/or article specific custom code before the closing &lt;/head&gt; and &lt;/body&gt;, or opening &lt;body&gt; tag.

== Description ==

Let we say that you have been told to add some custom code (HTML, JavaScript or CSS style) to page's `<head>` (like site verification code, custom styles, webfont link, etc), just before `</body>` or right after opening `<body>` (like pixel tracking, analytics code, heatmap code, etc), but you are not programmer. Then you can use Head &amp; Footer Code to do that.

Simply go to Tools &rarr; Head &amp; Footer Code in your website admin dashboard, and insert custom code to HEAD, BODY or FOOTER section (depending what you have to do).

If your WordPress show latest blog posts on homepage, you can also add specific code only for homepage on **Tools** &rarr; **Head &amp; Footer Code** (there will be section **Head, body and footer code on Homepage in Blog Posts mode**)

If you have to insert some custom code specific for individual article (post, page, custom post type), then you can use Article specific metabox while you editing post/page/custom post type (check out [Screenshots](https://wordpress.org/plugins/head-footer-code/#screenshots)). There you can also set should that specific code be appended to site-wide code defined on **Tools** &rarr; **Head &amp; Footer Code**, or should be overwritten.

Please note that taxonomies does not have own specific code but global code is added on those pages (category, tag and custom taxonomy listing, individual category, tags and custom taxonomies).

This magic is done by hooking to WordPress hooks `wp_head`, `wp_footer` and `wp_body_open`.

https://www.youtube.com/watch?v=Gd41Dv09UC4

**Works or broken?**

Please, consider to vote for this plugin. When you vote for broken, be so kind and tell in the [Forum](https://wordpress.org/support/plugin/head-footer-code/) what is broken. Maybe I might be able to fix it to make the plugin also work for you.

**I need your support**

It is very hard to continue development and support for this and my other free plugisn without contributions from users like you. If you enjoy using Head &amp; Footer Code and find it useful, please consider [making a donation](https://urosevic.net/wordpress/donate/?donate_for=head-footer-code). Your donation will help encourage and support the plugin's continued development and better user support.

**Features**

* Set site-wide custom content for head page section (before the `</head>`)
* Set site-wide custom content for body section (after the `<body>`) - **Requires WordPress 5.2!**
* Set site-wide custom content for footer page section (before the `</body>`)
* **[NEW in 1.2]** Set homepage specific custom code for head, body and/or footer if Homepage mode is se to Blog Posts
* Set article specific custom code for head page section (before the `</head>`)
* Set article specific custom code for body section (after the `<body>`) - **Requires WordPress 5.2!**
* Set article specific custom content for footer page section (before the `</body>`)
* Choose priority of printed custom code to head/body/footer sections (lower number mean far from `</head>` and `</body>` and closer to `<body>`, higher number means closer to `</head>` and `</body>` and farther to `<body>`)
* Choose which post types will have enabled article specific head/body/footer fields
* Choose should article specific head/body/footer code be appended to site-wide code, or will replace site-wide code
* **[NEW in 1.2.1]** View on Posts/Pages/Custom Post Types listing if article has defined any article specific custom code
* Site-wide section located under **Tools** > **Head & Footer Code**
* If you have set WP_DEBUG constant in `wp-config.php` to `true`, you'll see site-wide and article specific entries in page source code wrapped to comments.
* Multisite is supported.

General settings, including HEAD, BODY, FOOTER global code and priority, and also homepage code and behavior have been saved to WordPress option `auhfc_settings`.
Each post/page/custom post type specific HEAD, BODY and FOOTER code and behavior have been saved to post meta `_auhfc`.
On plugin uninstall these data is also deleted from database.

== Installation ==

Installation of this plugin is fairly easy as any other WordPress plugin.

**Standard procedure**

1. Go to **Plugins** &rarr; **Add New**.
1. Search for **head footer code**.
1. Enter to **Search Plugin** field `had footer code` and press Enter key.
1. Locate plugin **Head &amp; Footer Code** and click **Install Now** button.
1. After successfully installed plugin click link **Activate Plugin**.
1. Visit the **Tools** &rarr; **Head &amp; Footer Code**.
1. Add the desired code to proper section.

**FTP procedure**

1. Unpack `head-footer-code.zip`
1. Upload the whole directory and everything underneath to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Visit the **Tools** &rarr; **Head &amp; Footer Code** (**Settings** link).
1. Add the desired code to proper section.

== Frequently Asked Questions ==

= Why another one custom code plugin? =

Because all other similar plugins could not satisfy my requirements. In general, they have too much features or lack some features I need.

= I entered code to BODY section but nothing output on front-end =

This feature is implemented since WordPress version 5.2, but also require compatibility theme. Make sure that your theme support [wp_body_open](https://developer.wordpress.org/reference/hooks/wp_body_open/) hook.

Open in code editor `header.php` file from theme you use, and check if right after opening `<BODY>` tag there is following code (if it does not exists, add it or ask some developer to do that for you):
```if ( function_exists( 'wp_body_open' ) ) {
    wp_body_open();
}```

== Screenshots ==

1. Head &amp; Footer Code box in Plugin search results
2. Plugin Settings page (Site-wide, Homepage and Article Post Types)
3. Article specific metabox
4. Example of custom code inserted to HEAD section (site-wide with appended article specific)
5. Example of custom code inserted to BODY section (site-wide with appended article specific)
6. Example of custom code inserted to FOOTER section (site-wide with appended article specific)
7. Example of Head & Footer Code column on Pages listing, to identify which pages have set custom code, which one and what mode is selected

== Upgrade Notice ==

= 1.0.0 =
Initial release of new plugin developed by Aleksandar Urosevic.

== Changelog ==
= 1.2.2 (2021-02-01) =
* Fix: Noice errors in update script (thanks to @swinggraphics)
* Improve: wording on post/page listing

= 1.2.1 =
* Add: Head & Footer Code column to post/page/custom post type listing to show if/what article specific custom code is defined
* Fix: in_array() expects parameter 2 to be array, null given in head-footer-code/inc/front.php on line 46, 111, and 176

= 1.2 =
* Add: custom head, body and footer code for Homepage in Blog Posts mode.
* Fix: Code Editor broken in WordPress 5.5 release.
* Fix: Invalid requests while saving settings https://github.com/urosevic/head-footer-code/issues/1
* Improve: DRY for front-end conditions.
* Improve: translators tips for complex strings.
* Improve: all strings available to localize.

= 1.1.1 =
* Tested: on WordPress 5.4.1, 5.5-RC2-48768 with PHP 7.4.1
* Add: Video tutorial on how to install, configure and use Head & Footer Code plugin

= 1.1.0 =
* Tested: on WordPress 5.1.4, 5.3.2 and 5.4-beta3 with PHP 7.2.15 and 7.3.7
* Fix: Backslashes are removed on post/page update in article specific HEAD/BODY/FOOTER code reported by @asherber (`update_post_meta` pass key and value to `update_metadata` which expect them slashed key and value)
* Add: Support for `wp_body_open` Body hook introduced in WordPress 5.2
* Add: Backward compatibility for `wp_body_open` for older WordPress installations
* Add: FAQ Instructions on how to implement support for `wp_body_open` to any theme
* Update: Links and wording on plugin settings page
* Update: Screenshots

= 1.0.9.1 =
* Fix: Fatal Error on Multisite WP's (thanks @kunzemarketing for reporting)
* Improve: DRI for front-end debugging

= 1.0.9 =
* Add: Descriptive post types and descriptions for article specific sections
* Add: Option to process shortcodes in FOOTER section (global setting for site-wide and article specific)
* Change: Separate priority for HEAD and FOOT so admin can choose different priorities for header and footer
* Add: CodeMirror code editor for HEAD and FOOTER code in plugin settings
* Add: Plugin activation hook to prevent fatal errors in case of legacy WP and/or PHP
* Improve: Loading security

= 1.0.8 =
* Test compatibility with WordPress 5.1.1 and PHP 7.2.15
* Change: Meta boxes layout and type of behavior selector
* Change: Convert Post metaboxes to OOP
* Change: GNU GPL license to v3
* Add: Bundle GNU GPLv3 license to plugin codebase
* Update: Screenshots

= 1.0.7 =
* Compatibility check: Tested for WordPress 4.7.1
* UX: Add right hand sidebar on global settings page with links to donate, FAQ, Community support and plugin Reviews page.
* UX: Set monospaced font for textareas on global settings and article pages

= 1.0.6 =
* Fix: `PHP Notice:  Trying to get property of non-object in \wp-content\plugins\head-footer-code\inc\front.php on line 41`.
* Fix: Overwrite footer content for post/page if post/page template after content have another WP Loop query (like recent posts WP Widget in RHS sidebar).
* Optimize: Avoid reading post meta if not singular or post type not enabled
* Tested in Multisite environment (main and other network websites) on WordPress v4.5-alpha-36504 and theme Twenty Sixteen v1.2-alpha.

= 1.0.5 =
* Enhance: Add uninstall routine to make some housekeeping on plugin removal.
* Enhance: Add post type in debug comments.
* Readme: Test on WordPress v4.4-beta1 and updated compatibility.

= 1.0.4 =
* Fix: PHP Warning:  in_array() expects parameter 2 to be array, string given (introduced in v1.0.3)

= 1.0.3 =
* Change: Make even default WP post types `post` and `page` optional for page specific head/footer code

= 1.0.2 =
* Change: Replace PayPal donation links to prevent account limitations if plugin is used on website that violates PayPal's Acceptable Use Policy

= 1.0.1 =
* Fix: PHP Notice Trying to get property of non-object
* Optimize: Remove `attachment` post type from available to select as no reason to have custom head/footer code on attachments
* Optimize: Settings code cleanup

= 1.0.0 =
* Initial release.