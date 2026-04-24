<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AFCA_SEO_Breadcrumbs {

	const SHORTCODE = 'afca_breadcrumbs';

	public function __construct() {
		add_shortcode( self::SHORTCODE, [ $this, 'render_shortcode' ] );
	}

	public function render_shortcode( $atts ) {
		$atts = shortcode_atts(
			[
				'separator'    => '',
				'home_label'   => '',
				'show_home'    => 'yes',
				'show_current' => 'yes',
			],
			$atts,
			self::SHORTCODE
		);

		$separator = $atts['separator'] !== ''
			? $atts['separator']
			: AFCA_SEO::get_option( 'breadcrumb_separator', '/' );

		$home_label = $atts['home_label'] !== ''
			? $atts['home_label']
			: __( 'Home', 'afca-basic-seo' );

		$show_home    = ! in_array( strtolower( $atts['show_home'] ), [ 'no', 'false', '0' ], true );
		$show_current = ! in_array( strtolower( $atts['show_current'] ), [ 'no', 'false', '0' ], true );

		$items = $this->get_items( $home_label, $show_home, $show_current );

		$items = apply_filters( 'afca_seo_breadcrumb_items', $items );

		if ( empty( $items ) ) {
			return '';
		}

		return $this->render_html( $items, $separator );
	}

	private function get_items( $home_label, $show_home, $show_current ) {
		$items = [];

		if ( $show_home ) {
			$items[] = [
				'label' => $home_label,
				'url'   => home_url( '/' ),
			];
		}

		if ( is_front_page() ) {
			if ( ! $show_current && ! empty( $items ) ) {
				// If neither home nor current, there is nothing to show.
				array_pop( $items );
			}
			return $items;
		}

		if ( is_home() ) {
			$page_for_posts = (int) get_option( 'page_for_posts' );
			if ( $page_for_posts ) {
				$this->append_item( $items, get_the_title( $page_for_posts ), '', $show_current );
			} else {
				$this->append_item( $items, __( 'Blog', 'afca-basic-seo' ), '', $show_current );
			}
			return $items;
		}

		if ( is_singular() ) {
			$post = get_queried_object();
			$this->build_for_singular( $items, $post, $show_current );
			return $items;
		}

		if ( is_category() || is_tag() || is_tax() ) {
			$term = get_queried_object();
			$this->build_for_term( $items, $term, $show_current );
			return $items;
		}

		if ( is_post_type_archive() ) {
			$pt_obj = get_queried_object();
			if ( $pt_obj && isset( $pt_obj->label ) ) {
				$this->append_item( $items, $pt_obj->label, '', $show_current );
			}
			return $items;
		}

		if ( is_author() ) {
			$author = get_queried_object();
			$label  = $author && isset( $author->display_name )
				? sprintf( /* translators: %s: author display name */ __( 'Author: %s', 'afca-basic-seo' ), $author->display_name )
				: __( 'Author', 'afca-basic-seo' );
			$this->append_item( $items, $label, '', $show_current );
			return $items;
		}

		if ( is_year() ) {
			$this->append_item( $items, get_the_date( 'Y' ), '', $show_current );
			return $items;
		}

		if ( is_month() ) {
			$year    = get_the_date( 'Y' );
			$items[] = [
				'label' => $year,
				'url'   => get_year_link( get_the_date( 'Y' ) ),
			];
			$this->append_item( $items, get_the_date( 'F Y' ), '', $show_current );
			return $items;
		}

		if ( is_day() ) {
			$year    = get_the_date( 'Y' );
			$month   = get_the_date( 'F Y' );
			$items[] = [
				'label' => $year,
				'url'   => get_year_link( $year ),
			];
			$items[] = [
				'label' => $month,
				'url'   => get_month_link( get_the_date( 'Y' ), get_the_date( 'm' ) ),
			];
			$this->append_item( $items, get_the_date(), '', $show_current );
			return $items;
		}

		if ( is_search() ) {
			$label = sprintf( /* translators: %s: search query */ __( 'Search: %s', 'afca-basic-seo' ), get_search_query() );
			$this->append_item( $items, $label, '', $show_current );
			return $items;
		}

		if ( is_404() ) {
			$this->append_item( $items, __( '404 Error', 'afca-basic-seo' ), '', $show_current );
			return $items;
		}

		return $items;
	}

	private function build_for_singular( &$items, $post, $show_current ) {
		if ( ! $post || ! isset( $post->ID ) ) {
			return;
		}

		if ( $post->post_type !== 'post' && $post->post_type !== 'page' ) {
			$pt_obj = get_post_type_object( $post->post_type );
			if ( $pt_obj && ! empty( $pt_obj->has_archive ) ) {
				$archive_url = get_post_type_archive_link( $post->post_type );
				if ( $archive_url ) {
					$items[] = [
						'label' => $pt_obj->labels->name,
						'url'   => $archive_url,
					];
				}
			}
		}

		if ( $post->post_type === 'page' && $post->post_parent ) {
			$ancestors = array_reverse( get_post_ancestors( $post->ID ) );
			foreach ( $ancestors as $ancestor_id ) {
				$items[] = [
					'label' => get_the_title( $ancestor_id ),
					'url'   => get_permalink( $ancestor_id ),
				];
			}
		}

		if ( $post->post_type === 'post' ) {
			$cats = get_the_category( $post->ID );
			if ( ! empty( $cats ) ) {
				$primary = $cats[0];
				$chain   = [];
				$cat     = $primary;
				while ( $cat ) {
					array_unshift( $chain, $cat );
					$cat = $cat->parent ? get_term( $cat->parent, 'category' ) : null;
					if ( is_wp_error( $cat ) ) {
						break; }
				}
				foreach ( $chain as $c ) {
					$items[] = [
						'label' => $c->name,
						'url'   => get_term_link( $c ),
					];
				}
			}
		}

		if ( $post->post_type === 'attachment' && $post->post_parent ) {
			$items[] = [
				'label' => get_the_title( $post->post_parent ),
				'url'   => get_permalink( $post->post_parent ),
			];
		}

		$this->append_item( $items, get_the_title( $post->ID ), '', $show_current );
	}

	private function build_for_term( &$items, $term, $show_current ) {
		if ( ! $term || ! isset( $term->term_id ) ) {
			return;
		}

		$ancestors = [];
		$parent_id = isset( $term->parent ) ? (int) $term->parent : 0;
		while ( $parent_id ) {
			$parent = get_term( $parent_id, $term->taxonomy );
			if ( ! $parent || is_wp_error( $parent ) ) {
				break; }
			array_unshift( $ancestors, $parent );
			$parent_id = (int) $parent->parent;
		}
		foreach ( $ancestors as $ancestor ) {
			$link    = get_term_link( $ancestor );
			$items[] = [
				'label' => $ancestor->name,
				'url'   => is_wp_error( $link ) ? '' : $link,
			];
		}

		$this->append_item( $items, $term->name, '', $show_current );
	}

	private function append_item( &$items, $label, $url, $show_current ) {
		if ( ! $show_current ) {
			return;
		}
		$items[] = [
			'label' => $label,
			'url'   => $url,
		];
	}

	private function render_html( $items, $separator ) {
		$sep_html = '<span class="afca-breadcrumbs__sep" aria-hidden="true"> ' . esc_html( $separator ) . ' </span>';

		$html  = '<nav class="afca-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumbs', 'afca-basic-seo' ) . '">';
		$html .= '<ol class="afca-breadcrumbs__list" itemscope itemtype="https://schema.org/BreadcrumbList">';

		$total = count( $items );
		foreach ( $items as $i => $item ) {
			$position = $i + 1;
			$is_last  = ( $position === $total );

			$html .= '<li class="afca-breadcrumbs__item' . ( $is_last ? ' is-current' : '' ) . '" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';

			if ( ! $is_last && ! empty( $item['url'] ) ) {
				$html .= '<a itemprop="item" href="' . esc_url( $item['url'] ) . '"><span itemprop="name">' . esc_html( $item['label'] ) . '</span></a>';
			} else {
				$html .= '<span itemprop="name" aria-current="' . ( $is_last ? 'page' : 'false' ) . '">' . esc_html( $item['label'] ) . '</span>';
			}

			$html .= '<meta itemprop="position" content="' . esc_attr( $position ) . '" />';
			$html .= '</li>';

			if ( ! $is_last ) {
				$html .= $sep_html;
			}
		}

		$html .= '</ol></nav>';

		return apply_filters( 'afca_seo_breadcrumbs_html', $html, $items, $separator );
	}
}
