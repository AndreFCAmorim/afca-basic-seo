<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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

		new AFCA_SEO_Sitemap();
		new AFCA_SEO_Breadcrumbs();

		$update_class = new AFCA_SEO_Updates( 'https://andreamorim.site/', basename( AFCA_SEO_PATH ), AFCA_SEO_VERSION );

		add_action( 'afca_basic_seo_updates', [ $update_class, 'check_for_updates_on_hub' ] );
		if ( ! wp_next_scheduled( 'afca_basic_seo_updates' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'daily', 'afca_basic_seo_updates' );
		}
	}

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
