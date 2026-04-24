<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Núcleo do plugin — singleton, carregamento de módulos e gestão de opções.
 */
class AFCA_SEO {

	const OPTION_KEY = 'afca_seo_options';

	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		load_plugin_textdomain( 'afca-basic-seo', false, dirname( plugin_basename( AFCA_SEO_FILE ) ) . '/languages' );

		if ( is_admin() ) {
			new AFCA_SEO_Admin();
			new AFCA_SEO_Meta();
		} else {
			new AFCA_SEO_Frontend();
		}

		// O sitemap precisa de estar disponível em ambos os contextos.
		new AFCA_SEO_Sitemap();

		// Breadcrumbs: shortcode registado globalmente (pode ser renderizado via REST, admin preview, etc.).
		new AFCA_SEO_Breadcrumbs();
	}

	/**
	 * Devolve todas as opções com defaults aplicados.
	 */
	public static function get_options() {
		$defaults = [
			'separator'               => '|',
			'breadcrumb_separator'    => '/',
			'og_default_image'        => '',
			'og_site_name'            => get_bloginfo( 'name' ),
			'twitter_card_type'       => 'summary_large_image',
			'twitter_username'        => '',
			'sitemap_post_types'      => [ 'post', 'page' ],
			'sitemap_taxonomies'      => [ 'category', 'post_tag' ],
			'sitemap_include_authors' => true,
		];
		$opts     = get_option( self::OPTION_KEY, [] );
		if ( ! is_array( $opts ) ) {
			$opts = [];
		}
		return wp_parse_args( $opts, $defaults );
	}

	public static function get_option( $key, $default = null ) {
		$opts = self::get_options();
		return array_key_exists( $key, $opts ) ? $opts[ $key ] : $default;
	}
}
