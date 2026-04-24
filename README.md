# AFCA Basic SEO

**Contributors:** afca
**Tags:** seo, meta tags, open graph, twitter card, sitemap
**Requires at least:** 5.7
**Tested up to:** 6.6
**Requires PHP:** 7.4
**Stable tag:** 1.1.0
**License:** GPLv2 or later
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html

A lightweight alternative to Yoast SEO. Just the essentials, no bloat.

## Description

AFCA Basic SEO is a minimalist plugin focused on the essentials of SEO for WordPress:

- **Meta title, description, and keywords** — per post, page, CPT, and taxonomy term
- **Open Graph & Twitter Cards** — OG title, description, image per content (automatic fallback to featured image → default OG)
- Customizable **canonical URL**
- **Robots control** — noindex / nofollow per content and per term
- **Native WordPress sitemap** — choose which post types, taxonomies, and whether you want authors in the `wp-sitemap.xml`
- **Automatic exclusion** of content marked as noindex from the sitemap
- **Breadcrumbs via shortcode** `[afca_breadcrumbs]` — with customizable separator and schema.org microdata (BreadcrumbList)
- **Real-time character counter** in the editor

No dashboards, redirects, or a thousand and one features that 99% of sites don’t use.

## Installation

1. Upload the `afca-basic-seo` folder to `/wp-content/plugins/`
2. Activate under **Plugins**
3. Go to **AFCA SEO** in the side menu to configure
4. Edit posts/terms to set specific meta tags

## Frequently Asked Questions

### Does it work with Custom Post Types?

Yes. All public CPTs automatically get the meta box and appear as an option in the sitemap.

### Does it work with custom taxonomies?

Yes — all public taxonomies.

### Is it compatible with Yoast?

Disable Yoast first. Both will compete for the same meta tags.

## Changelog

### 1.1.0
- New: shortcode `[afca_breadcrumbs]` with schema.org microdata (BreadcrumbList).
- New: breadcrumb separator setting in the “General” tab.
- Supports attributes in the shortcode: `separator`, `home_label`, `show_home`, `show_current`.
- Filters: `afca_seo_breadcrumb_items` and `afca_seo_breadcrumbs_html`.

### 1.0.0
- Initial release.