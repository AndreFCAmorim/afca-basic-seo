<?php
/**
 * Plugin Name: AFCA Basic SEO
 * Plugin URI:  https://example.com/afca-basic-seo
 * Description: Alternativa leve ao Yoast SEO. Meta tags, Open Graph, Twitter Cards, canónica e controlo do sitemap nativo do WordPress.
 * Version:     1.1.0
 * Author:      AFCA
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: afca-basic-seo
 * Domain Path: /languages
 * Requires at least: 5.7
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AFCA_SEO_VERSION', '1.1.0' );
define( 'AFCA_SEO_FILE', __FILE__ );
define( 'AFCA_SEO_PATH', plugin_dir_path( __FILE__ ) );
define( 'AFCA_SEO_URL', plugin_dir_url( __FILE__ ) );

require_once AFCA_SEO_PATH . 'includes/class-afca-seo.php';
require_once AFCA_SEO_PATH . 'includes/class-afca-seo-admin.php';
require_once AFCA_SEO_PATH . 'includes/class-afca-seo-meta.php';
require_once AFCA_SEO_PATH . 'includes/class-afca-seo-frontend.php';
require_once AFCA_SEO_PATH . 'includes/class-afca-seo-sitemap.php';
require_once AFCA_SEO_PATH . 'includes/class-afca-seo-breadcrumbs.php';

add_action(
	'plugins_loaded',
	function () {
		AFCA_SEO::instance();
	}
);
