# Head & Footer Code

Contributors: urkekg, techwebux
Donate link: https://urosevic.net/wordpress/donate/?donate_for=head-footer-code
Tags: head, body, footer, code, script
Requires at least: 4.9
Tested up to: 7.0
Stable tag: 1.5.0
Requires PHP: 5.5
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Easy add site-wide, category and article specific custom code before the closing &lt;/head&gt; and &lt;/body&gt;, or after opening &lt;body&gt; tag.


## Description

**Head &amp; Footer Code** plugin helps you add custom code snippets (JavaScript, CSS, or HTML) to the page even if you are not a programmer. It could be within the `<head>` (site verification code for various services, custom styles, meta or Webfont link), right after opening `<body>` (pixel tracking, analytics or heatmap code) or just before closing `</body>` tag.

Go to **Tools** &rarr; **Head &amp; Footer Code** in WordPress Dashboard. Insert custom code to HEAD, BODY or FOOTER section (depending on what you have to do).

All magic is done by hooking to WordPress hooks `wp_head`, `wp_footer` and `wp_body_open`.


> 💡 Trial Head &amp; Footer Code plugin on a free dummy website before you add it on your project: [Test It Now!](https://tastewp.com/new/?pre-installed-plugin-slug=head-footer-code)


Various code snippets are supported, including but not limited to:

* Google Analytics 4
* Google Tag Manager
* Google Ads Conversion
* Lite Analytics
* Facebook/Meta Pixel
* Hotjar
* FullStory
* Google site verification
* Bing site verification
* Yandex site verification
* Alexa site verification


### Homepage in Blog Posts mode

If your WordPress shows the latest blog posts on the homepage, you can also add specific code only for the homepage on **Tools** &rarr; **Head &amp; Footer Code** (there will be section **Head, body and footer code on Homepage in Blog Posts mode**)


### Article specific code

To insert custom code specific for individual article (post, page or custom post type), use article-specific Metabox while editing post/page/custom post type (check out [Screenshots](https://wordpress.org/plugins/head-footer-code/#screenshots)). There choose if that specific code appends to site-wide code defined on **Tools** &rarr; **Head &amp; Footer Code**, or to replace it.


### Category specific code

You can also define a Category specific code on each individual category.

Other taxonomies (tag and custom taxonomy) do not have available their specific code, but for them a Global code is used instead.


### Video guide

https://www.youtube.com/watch?v=Gd41Dv09UC4


### Do you need our support?

If **Head &amp; Footer Code** does not work on your project, please let us know by [raising a new support ticket](https://wordpress.org/support/plugin/head-footer-code/#new-topic-0) in the [Community Forum](https://wordpress.org/support/plugin/head-footer-code/) and describe what does not works and how to reproduce the issue. We will make sure to resolve the issue as soon as possible.

If you find **Head &amp; Footer Code** useful for your project, please [review plugin](https://wordpress.org/support/plugin/head-footer-code/reviews/#new-post).


### Features

* **Multisite** and **PHP 8.5** compatible!
* Set site-wide custom content for:
  * head page section (before the `</head>`)
  * body section (after the `<body>`) - **Requires WordPress 5.2!**
  * footer page section (before the `</body>`)
* Homepage in Blog Posts mode:
  * set homepage specific custom code for head, body and/or footer
  * toggle homepage specific custom code on paged Homepage (page 2, 3, a nd so on)
* Set article specific custom code for:
  * head page section (before the `</head>`)
  * body section (after the `<body>`) - **Requires WordPress 5.2!**
  * footer page section (before the `</body>`)
* Set category specific custom code for head, body and/or footer of the page
* Choose priority of printed custom code to head/body/footer sections (lower number mean far from `</head>` and `</body>` and closer to `<body>`, higher number means closer to `</head>` and `</body>` and farther to `<body>`)
* Choose which post types will have enabled article specific head/body/footer fields
* Choose should article specific head/body/footer code be appended to site-wide code, or will replace site-wide code
* View on Posts/Pages/Custom Post Types listing if article has defined any article specific custom code
* Site-wide section is located under **Tools** > **Head & Footer Code**
* If you have set WP_DEBUG constant in `wp-config.php` to `true`, you'll see site-wide and article specific entries in page source code wrapped to comments.
* [NEW] allow unprivileged user roles Editor and Author to manage article-specific code on single site and multisite (disabled by default)

### Data stored in database

General settings (HEAD, BODY, FOOTER global code and priority, Homepage code and behaviour) saves in WordPress option `auhfc_settings`.
Each post/page/custom post type specific HEAD, BODY and FOOTER code and behaviour saves to post meta `_auhfc`.
Each category specific HEAD, BODY and FOOTER code and behaviour saves to taxonomy meta `_auhfc`.

During the Uninstall process all these data has been deleted from the database.
In case you wish to reinstall plugin, **DO NOT UNINSTALL IT** although **Deactivate**, then delete the directory `/wp-content/plugins-head-footer-code` and then reinstall plugin.


### Permissions on Multisite WordPress

1. Access to **Global**: only Super Admin and Administrator
1. Access to **Article specific**: Super Admin, Administrator; **optional**: Editor and Author
1. Access to **Category specific**: only Super Admin and Administrator


## Installation

Installation of the **Head &amp; Footer Code** is easy as any other WordPress plugin.


### Standard procedure

1. In WordPress Dashboard go to **Plugins** &rarr; **Add New**.
1. Enter `head footer code` to the **Search plugins...** field and wait for the moment.
1. Locate **Head &amp; Footer Code** and click the **Install Now** button.
1. After successful installation, click the **Activate** button.
1. Click **Settings** link for **Head &amp; Footer Code** or visit the **Tools** &rarr; **Head &amp; Footer Code**.
1. Add the desired code to the target section.


### FTP procedure

1. Click on the **Download** button to get **Head &amp; Footer Code** installation package.
1. Unpack archive **head-footer-code.zip** on local computer.
1. Upload the entire directory **head-footer-code** to the `/wp-content/plugins/` directory on your server.
1. In WordPress Dashboard go to **Plugins** &rarr; **Installed Plugins** and click the link **Activate** for the **Head &amp; Footer Code** plugin.
1. Click **Settings** link for **Head &amp; Footer Code** or visit the **Tools** &rarr; **Head &amp; Footer Code**.
1. Add the desired code to the target section.


## Frequently Asked Questions

### On Network WordPress an Administrator/Editor/Author user getting code validation hint error `Tag <tag> is not allowed.`

It's not a bug, it's a security measure by WordPress Core.

If you wish to remove hinting errors for Administrator role, make sure you allow `unfiltered_html` capability for that role (Google is your friend).

### Is supported PHP code in code snippets?

As it's a security risk, the **Head &amp; Footer Code** does not process PHP code if entered into any plugin field (global or article specific).

Any content added to HFC fields is printed on the front-end as is.

### I entered code to BODY section, but nothing outputs on front-end

This feature is implemented since WordPress version 5.2 and requires theme compatibility.

To make sure if theme you use supports [wp_body_open](https://developer.wordpress.org/reference/hooks/wp_body_open/) hook, open in code editor `header.php` file from theme you use, and check if right after opening `<BODY>` tag there is following code (if it does not exists, add it or ask some developer to do that for you):

`
if ( function_exists( 'wp_body_open' ) ) {
    wp_body_open();
}
`

### Where do I report security bugs found in this plugin?

Please report security bugs found in the source code of the Head & Footer Code plugin through the [Patchstack Vulnerability Disclosure Program](https://patchstack.com/database/vdp/head-footer-code). The Patchstack team will assist you with verification, CVE assignment, and notify the developers of this plugin.


## Screenshots

1. Head &amp; Footer Code box in Plugin search results
2. Plugin Settings page (Site-wide, Homepage and Article Post Types)
3. Article specific metabox
4. Example of custom code inserted to HEAD section (site-wide with appended article specific)
5. Example of custom code inserted to BODY and FOOTER sections (site-wide with appended article specific)
6. Category specific metabox
7. Example of custom code inserted to HEAD section (site-wide with appended category specific)
8. Example of custom code inserted to BODY and FOOTER section (site-wide with appended category specific)
9. Example of **Head &amp; Footer Code** column on Pages listing, to identify which pages have set custom code, which one and what mode is selected


## Upgrade Notice

### 1.5.0

To improve security and prevent XSS, this release introduce filtering of all opening SCRIPT and STYLE tags, and remove all not allowed attributes from them (eg. `onload`, `onmouseover`, etc).

### 1.0.0

Initial release of new plugin developed by Aleksandar Urosevic.

### 1.4.0

Introduced restriction for non-privileged roles (eg. Editor and Author) to access HFC on pages, posts and custom CPTs. If you wish to allow them manage article-specific HFC, make sure you enable that at the bottom of the global plugin settings page.

## Changelog

### 1.5.0 (2026-02-22)
* Test: WordPress 7.0-beta1, Twenty Twenty-Five 1.4, PHP 8.5.3
* Fix: Missing code editor when creating a new article
* Fix: Incorrect use of <label for=FORM_ELEMENT>
* Improve: Security (validate settings save data, better code filtering with included opening SCRIPT and STYLE tags)
* Optimize: Code editor on articles
* Optimize: Settings maintainability

### 1.4.6 (2025-12-01)
* Test: WordPress 6.9RC3, Twenty Twenty-Five 1.3, PHP 8.4.15

### 1.4.5 (2025-06-13)
* Fix: Compatibility with Jetpack > Writing > Composing > Compose using shortcode to embed media from popular sites
* Fix: Automatic conversion of false to array is deprecated in update.php
* Test: WordPress 6.8.1, Twenty Twenty-Five 1.2, Jetpack 14.7

https://youtu.be/CXCWqNUQSF4

### 1.4.4 (2025-05-31)
* Fix: Relaxed filtering to allow <link> preloadans security/privacy attributes.

### 1.4.3 (2025-04-30)
* Fix: Relaxed filtering to allow <script> blocks with `<`, `>`, `=>` characters and `type="application/ld+json"` for structured data (rich snippets), following stricter sanitization introduced in 1.4.0.

### 1.4.2 (2025-04-20)
* Fix: meta tag broken by security introduced in 1.4.0

### 1.4.1 (2025-04-13)
* Fix: hreflang link broken by security introduced in 1.4.0

### 1.4.0 (2025-04-12)
* Test: WordPress 6.8-RC3, Twenty Twenty-Five 1.1, Twenty Twenty-Three 1.6, Astra 4.9.2 and PHP 8.3.17
* Test: WordPress 4.9.26, Twenty Seventeen 1.7 and PHP 5.5.38
* Unclutter Settings page
* Improve: Security
* Improve: Refactor and reorganise codebase
  * auhfc_settings() -> Techwebux\Hfc\Main::settings()
  * auhfc_is_homepage_blog_posts() -> Techwebux\Hfc\Common::is_homepage_blog_posts()
  * auhfc_add_to_homepage_paged() -> Techwebux\Hfc\Common::add_to_homepage_paged()
  * sanitize_html_classes() -> Techwebux\Hfc\Common::sanitize_html_classes()
  * auhfc_allowed_code() -> Techwebux\Hfc\Common::allowed_html()
  * auhfc_form_allowed_code() -> Techwebux\Hfc\Common::form_allowed_html()
  * auhfc_html2code -> Techwebux\Hfc\Common::html2code()
  * auhfc_get_meta() -> Techwebux\Hfc\Common::get_meta()
  * auhfc_get_post_type() -> Techwebux\Hfc\Common::get_post_type()
  * auhfc_print_sitewide() -> Techwebux\Hfc\Common::print_sitewide()
  * auhfc_out() -> Techwebux\Hfc\Common::out()
  * auhfc_head_note() -> Techwebux\Hfc\Settings::head_note()
  * auhfc_body_note() -> Techwebux\Hfc\Settings::body_note()

### 1.3.7 (2024-11-18)
* Fix: Compatibility with WordPress 6.7

### 1.3.6 (2024-10-12)
* Improve: Optimize strings for easier translation

### 1.3.5 (2024-07-06)
* Tested: PHP 8.3.7 and WordPress 6.6 with Twenty Twenty-Four theme 1.1 (Single and Multisite)
* Change: Move the `Settings` link on plugins listing to 1st position

### 1.3.4 (2024-06-30)
* Tested: PHP 8.3.7 and WordPress 6.5.5 with Twenty Twenty-Four theme 1.1 (Single and Multisite)
* Change: PHP version lowered to 5.5
* Fix: Activation on deprecated PHP or WordPress

### 1.3.3 (2023-07-21)
* Tested: PHP 8.2.8 and WordPress 6.3-RC1 with Twenty Twenty-Three theme (Single and Multisite)

### 1.3.2 (2023-06-02)
* Tested: on PHP 8.2.6 and WordPress 6.2.2 with Twenty Twenty-Three theme (Single and Multisite)
* Fixed: Deprecated and Warning notices in update.php on PHP 8.2.6
* Improve: Multisite support CodeMirror on Article and Category

### 1.3.1 (2023-03-18)
* Tested: on PHP 8.1.14/8.2.1 and WordPress 6.2-RC2 with Twenty Twenty-Three theme (Single and Multisite)
* Add: support do not add homepage in Blog Post related code on paged pages (2, 3, and so on)
* Add: CodeEditor on textareas in article Meta boxes
* Fix: Fatal error due to relocated plugin update file
* Cleanup: Remove donate button from settings page
* Improve: Security.
* Improve: Coding Standard.

### 1.3.0 (2022-05-08)

* Tested: on PHP 8.1.5 and WordPress 6.0-RC1 with Twenty Twenty-Two theme (Single and Multisite)
* Add: Support for Categotry specific code.
* Improve: Coding Standard.
* Improve: Important notes on settings page.
* Improve: README converted to MarkDown.
* Improve: Remove PayPal logo and load minified admin stylesheet.
