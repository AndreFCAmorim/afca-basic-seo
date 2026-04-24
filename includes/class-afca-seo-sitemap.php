<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AFCA_SEO_Sitemap {

	public function __construct() {
		add_filter( 'wp_sitemaps_post_types', [ $this, 'filter_post_types' ] );
		add_filter( 'wp_sitemaps_taxonomies', [ $this, 'filter_taxonomies' ] );
		add_filter( 'wp_sitemaps_add_provider', [ $this, 'filter_providers' ], 10, 2 );
		add_filter( 'wp_sitemaps_posts_query_args', [ $this, 'exclude_noindex_posts' ], 10, 2 );
		add_filter( 'wp_sitemaps_taxonomies_query_args', [ $this, 'exclude_noindex_terms' ], 10, 2 );
	}

	public function filter_post_types( $post_types ) {
		$allowed = (array) AFCA_SEO::get_option( 'sitemap_post_types', [ 'post', 'page' ] );
		foreach ( $post_types as $name => $obj ) {
			if ( ! in_array( $name, $allowed, true ) ) {
				unset( $post_types[ $name ] );
			}
		}
		return $post_types;
	}

	public function filter_taxonomies( $taxonomies ) {
		$allowed = (array) AFCA_SEO::get_option( 'sitemap_taxonomies', [ 'category', 'post_tag' ] );
		foreach ( $taxonomies as $name => $obj ) {
			if ( ! in_array( $name, $allowed, true ) ) {
				unset( $taxonomies[ $name ] );
			}
		}
		return $taxonomies;
	}

	public function filter_providers( $provider, $name ) {
		if ( $name === 'users' && ! AFCA_SEO::get_option( 'sitemap_include_authors', true ) ) {
			return false;
		}
		return $provider;
	}

	public function exclude_noindex_posts( $args, $post_type ) {
		$args['meta_query'] = $this->build_exclude_meta_query( $args );
		return $args;
	}

	public function exclude_noindex_terms( $args, $taxonomy ) {
		$args['meta_query'] = $this->build_exclude_meta_query( $args );
		return $args;
	}

	private function build_exclude_meta_query( $args ) {
		$existing = isset( $args['meta_query'] ) && is_array( $args['meta_query'] ) ? $args['meta_query'] : [];

		$exclude = [
			'relation' => 'OR',
			[
				'key'     => AFCA_SEO_Meta::META_PREFIX . 'noindex',
				'compare' => 'NOT EXISTS',
			],
			[
				'key'     => AFCA_SEO_Meta::META_PREFIX . 'noindex',
				'value'   => '1',
				'compare' => '!=',
			],
		];

		if ( empty( $existing ) ) {
			return $exclude;
		}

		return [
			'relation' => 'AND',
			$existing,
			$exclude,
		];
	}
}
