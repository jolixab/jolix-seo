# Jolix SEO

A simple and effective WordPress SEO plugin to manage meta titles, descriptions, Open Graph fields, XML sitemaps, and redirects with global suffix options.

## Features

### SEO Meta Management
- **Simple Interface** - Clean, intuitive meta box on post/page editor
- **Combined Fields** - One title field for both SEO and social media (no duplicate work)
- **Smart Image Handling** - Use featured image or custom image for Open Graph
- **Character Counting** - Real-time character counts with color-coded feedback
- **Global Suffixes** - Add site-wide suffixes to titles and descriptions
- **Smart Truncation** - Automatically truncate content to fit SEO best practices
- **Complete Coverage** - Outputs SEO meta tags, Open Graph, and Twitter Cards

### XML Sitemap
- **Automatic Generation** - Dynamic XML sitemap at `/sitemap.xml`
- **Post Type Selection** - Choose which content types to include
- **Individual Control** - Exclude specific posts/pages from sitemap
- **Backup Sitemap** - Physical backup file generation for reliability

### Advanced Redirect Management
- **Pattern Types** - Exact matches, wildcard patterns (* and ?), and full regex support
- **Priority System** - High/Normal/Low priority levels with intelligent ordering
- **Multiple Redirect Types** - Support for 301, 302, 307, and 308 redirects
- **404 Handling** - Automatically intercepts 404 pages and checks for matching redirects
- **Hit Tracking** - Records redirect usage statistics for monitoring
- **Live Testing** - Built-in pattern testing tool in admin interface

### WooCommerce Integration
- **Product SEO** - Enhanced meta fields for WooCommerce products
- **Product Images** - Smart handling of product gallery images for social sharing

## Installation

1. Download or clone this repository
2. Upload the `jolix-seo` folder to `/wp-content/plugins/`
3. Activate the plugin through the 'Plugins' screen in WordPress
4. Go to Settings > Jolix SEO to configure global settings (optional)
5. Edit any post or page to see the SEO meta box

## File Structure

```
jolix-seo/
├── jolix-seo.php                        # Main plugin bootstrap
├── includes/                            # Core class files
│   ├── class-jolix-seo.php             # Core plugin class
│   ├── class-jolix-seo-meta-box.php    # Admin meta box functionality
│   ├── class-jolix-seo-settings.php    # Settings page and management
│   ├── class-jolix-seo-sitemap.php     # XML sitemap functionality
│   ├── class-jolix-seo-woocommerce.php # WooCommerce integration
│   └── class-jolix-seo-redirects.php   # Advanced redirect management
├── templates/                           # Admin UI templates
│   ├── meta-box.php                     # Post/page meta box template
│   ├── settings-page.php               # Settings page template
│   ├── redirects-list.php              # Redirects management interface
│   └── redirect-form.php               # Redirect add/edit form
├── languages/                           # Translation files
│   ├── jolix-seo.pot                   # Translation template
│   └── index.php                       # Security file
├── build.sh                            # Build script for distribution
├── readme.txt                          # WordPress.org readme
├── README.md                           # GitHub readme
├── index.php                           # Security file
└── uninstall.php                       # Cleanup on uninstall
```

## Usage

### Individual Posts/Pages

1. Edit any post or page
2. Scroll down to the "Jolix SEO" meta box
3. Fill in your custom meta title and description
4. Choose between featured image or custom image for social media
5. Watch the character counters for optimal length

### Global Settings

1. Go to **Settings > Jolix SEO** in WordPress admin
2. Set optional suffixes for titles and descriptions
3. Enable smart truncation to automatically fit within SEO limits
4. Configure XML sitemap settings and post type inclusion
5. Save settings - they'll apply to all posts and pages

### Redirect Management

1. Go to **Tools > Redirects** in WordPress admin
2. Add new redirects with pattern matching support:
   - **Exact**: `/old-page` → `/new-page`
   - **Wildcard**: `/blog/*` → `/news/*`
   - **Regex**: `/product-(\d+)` → `/item/$1`
3. Set priority levels and redirect types (301, 302, 307, 308)
4. Monitor hit statistics and test patterns with the built-in testing tool

## What It Outputs

The plugin generates these meta tags:

```html
<!-- Basic SEO -->
<meta name="title" content="Your Title">
<meta name="description" content="Your Description">

<!-- Open Graph (Facebook, LinkedIn, etc.) -->
<meta property="og:type" content="article">
<meta property="og:url" content="https://yoursite.com/post">
<meta property="og:title" content="Your Title">
<meta property="og:description" content="Your Description">
<meta property="og:image" content="https://yoursite.com/image.jpg">

<!-- Twitter Cards -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Your Title">
<meta name="twitter:description" content="Your Description">
<meta name="twitter:image" content="https://yoursite.com/image.jpg">
```

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher

## Translation

The plugin is translation-ready and includes:

- POT file for creating translations (`languages/jolix-seo.pot`)
- Text domain: `jolix-seo`
- All user-facing strings are wrapped with translation functions

To create a translation:
1. Use the POT file in the `languages/` directory
2. Create PO/MO files for your language (e.g., `jolix-seo-sv_SE.po`)
3. Place them in the `languages/` directory

## Development

This plugin follows WordPress coding standards and best practices:

- Proper escaping and sanitization
- Nonce verification for security
- Object-oriented structure
- WordPress hooks and filters

## Support

For support, feature requests, or bug reports, please create an issue in this repository.

## License

This plugin is licensed under the GPL v2 or later.