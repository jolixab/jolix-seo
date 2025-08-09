=== Jolix SEO ===
Contributors: jolix
Tags: seo, meta, open graph, twitter cards, social media, sitemap, redirects, woocommerce
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A simple and effective SEO plugin to manage meta titles, descriptions, Open Graph fields, XML sitemaps, and redirects with global suffix options.

== Description ==

Jolix SEO is a lightweight, user-friendly SEO plugin that helps you optimize your WordPress site's meta tags for search engines and social media platforms.

**Key Features:**

**SEO Meta Management:**
* **Simple Interface** - Clean, intuitive meta box on post/page editor
* **Combined Fields** - One title field for both SEO and social media (no duplicate work)
* **Smart Image Handling** - Use featured image or custom image for Open Graph
* **Character Counting** - Real-time character counts with color-coded feedback
* **Global Suffixes** - Add site-wide suffixes to titles and descriptions
* **Smart Truncation** - Automatically truncate content to fit SEO best practices
* **Complete Coverage** - Outputs SEO meta tags, Open Graph, and Twitter Cards

**XML Sitemap:**
* **Automatic Generation** - Dynamic XML sitemap at `/sitemap.xml`
* **Post Type Selection** - Choose which content types to include
* **Individual Control** - Exclude specific posts/pages from sitemap
* **Backup Sitemap** - Physical backup file generation for reliability

**Advanced Redirect Management:**
* **Pattern Types** - Exact matches, wildcard patterns (* and ?), and full regex support
* **Priority System** - High/Normal/Low priority levels with intelligent ordering
* **Multiple Redirect Types** - Support for 301, 302, 307, and 308 redirects
* **404 Handling** - Automatically intercepts 404 pages and checks for matching redirects
* **Hit Tracking** - Records redirect usage statistics for monitoring
* **Live Testing** - Built-in pattern testing tool in admin interface

**WooCommerce Integration:**
* **Product SEO** - Enhanced meta fields for WooCommerce products
* **Product Images** - Smart handling of product gallery images for social sharing

**What it manages:**

* Meta title and description
* Open Graph tags (Facebook, LinkedIn, etc.)
* Twitter Card tags
* Social media images
* XML sitemaps with post type control
* 301/302/307/308 redirects with pattern matching
* WooCommerce product SEO optimization

**Perfect for:**

* Content creators who want comprehensive SEO control
* Agencies managing multiple client sites with redirect needs
* E-commerce sites using WooCommerce
* Sites migrating content requiring redirect management
* Anyone who needs reliable SEO management with advanced features

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/jolix-seo/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to Settings > Jolix SEO to configure global settings (optional)
4. Go to Tools > Redirects to manage URL redirects (optional)
5. Edit any post or page to see the SEO meta box

== Frequently Asked Questions ==

= Do I need to configure anything after installation? =

No! The plugin works immediately. Global suffix settings and XML sitemap are optional and can be configured at Settings > Jolix SEO. Redirects can be managed at Tools > Redirects.

= How do I set up redirects? =

Go to Tools > Redirects and click "Add New". You can create exact matches (/old-page), wildcard patterns (/blog/* to /news/*), or regular expressions for advanced pattern matching. Set priority levels and choose redirect types (301, 302, 307, 308).

= Does the sitemap update automatically? =

Yes! The XML sitemap at `/sitemap.xml` updates automatically when you publish, update, or delete content. You can also generate a physical backup sitemap file.

= Will this conflict with other SEO plugins? =

It's recommended to use only one SEO plugin at a time. Deactivate other SEO plugins before using Jolix SEO.

= What image size is recommended for social media? =

The optimal size is 1200x630 pixels (1.91:1 ratio) for best compatibility across platforms.

= Can I use this on existing content? =

Yes! The plugin will use your existing post titles and excerpts as defaults, then you can customize as needed.

= Does it work with custom post types? =

Yes! The plugin automatically works with all public post types.

== Screenshots ==

1. Simple, clean meta box interface on post editor
2. Global settings page for site-wide suffixes
3. Character counting with color-coded feedback

== Changelog ==

= 1.0.0 =
* Initial release
* Meta title and description management
* Open Graph and Twitter Cards support
* Featured image integration
* Global suffix system with smart truncation
* XML sitemap generation with post type control
* Advanced redirect management with pattern matching
* WooCommerce integration
* WordPress coding standards compliance

== Upgrade Notice ==

= 1.0.0 =
Initial release of Jolix SEO.