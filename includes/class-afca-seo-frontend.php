<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Frontend — gera todas as meta tags, OG, Twitter, canónica e robots.
 */
class AFCA_SEO_Frontend {

	public function __construct() {
		add_filter( 'document_title_separator', [ $this, 'filter_title_separator' ] );
		add_filter( 'document_title_parts', [ $this, 'filter_title_parts' ] );
		add_filter( 'wp_robots', [ $this, 'filter_robots' ] );

		// Substituir canónica nativa.
		remove_action( 'wp_head', 'rel_canonical' );
		add_action( 'wp_head', [ $this, 'output_canonical' ], 5 );

		add_action( 'wp_head', [ $this, 'output_meta_tags' ], 1 );

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_style' ] );
	}

	public function filter_title_separator( $sep ) {
		$custom = AFCA_SEO::get_option( 'separator', '|' );
		return $custom !== '' ? $custom : $sep;
	}

	public function filter_title_parts( $parts ) {
		$custom = $this->get_meta_title();
		if ( $custom ) {
			$parts['title'] = $custom;
			unset( $parts['tagline'], $parts['site'] );
		}
		return $parts;
	}

	public function filter_robots( $robots ) {
		$noindex  = false;
		$nofollow = false;

		if ( is_singular() ) {
			$post_id  = get_queried_object_id();
			$noindex  = (bool) get_post_meta( $post_id, AFCA_SEO_Meta::META_PREFIX . 'noindex', true );
			$nofollow = (bool) get_post_meta( $post_id, AFCA_SEO_Meta::META_PREFIX . 'nofollow', true );
		} elseif ( is_category() || is_tag() || is_tax() ) {
			$term = get_queried_object();
			if ( $term && isset( $term->term_id ) ) {
				$noindex  = (bool) get_term_meta( $term->term_id, AFCA_SEO_Meta::META_PREFIX . 'noindex', true );
				$nofollow = (bool) get_term_meta( $term->term_id, AFCA_SEO_Meta::META_PREFIX . 'nofollow', true );
			}
		}

		if ( $noindex ) {
			$robots['noindex'] = true;
			unset( $robots['index'] );
		}
		if ( $nofollow ) {
			$robots['nofollow'] = true;
			unset( $robots['follow'] );
		}

		return $robots;
	}

	public function output_canonical() {
		$url = $this->get_canonical_url();
		if ( $url && ! is_wp_error( $url ) ) {
			echo '<link rel="canonical" href="' . esc_url( $url ) . "\" />\n";
		}
	}

	public function output_meta_tags() {
		$description = $this->get_meta_description();
		$keywords    = $this->get_keywords();

		$og_title  = $this->get_og_title();
		$og_desc   = $this->get_og_description();
		$og_image  = $this->get_og_image();
		$og_url    = $this->get_canonical_url();
		$site_name = AFCA_SEO::get_option( 'og_site_name', get_bloginfo( 'name' ) );
		$og_type   = $this->get_og_type();
		$locale    = get_locale();

		$card_type   = AFCA_SEO::get_option( 'twitter_card_type', 'summary_large_image' );
		$twitter_usr = AFCA_SEO::get_option( 'twitter_username', '' );

		echo "\n<!-- AFCA Basic SEO -->\n";

		if ( $description ) {
			echo '<meta name="description" content="' . esc_attr( $description ) . "\" />\n";
		}
		if ( $keywords ) {
			echo '<meta name="keywords" content="' . esc_attr( $keywords ) . "\" />\n";
		}

		// Open Graph.
		echo '<meta property="og:type" content="' . esc_attr( $og_type ) . "\" />\n";
		if ( $og_title ) {
			echo '<meta property="og:title" content="' . esc_attr( $og_title ) . "\" />\n";
		}
		if ( $og_desc ) {
			echo '<meta property="og:description" content="' . esc_attr( $og_desc ) . "\" />\n";
		}
		if ( $og_url && ! is_wp_error( $og_url ) ) {
			echo '<meta property="og:url" content="' . esc_url( $og_url ) . "\" />\n";
		}
		if ( $site_name ) {
			echo '<meta property="og:site_name" content="' . esc_attr( $site_name ) . "\" />\n";
		}
		if ( $locale ) {
			echo '<meta property="og:locale" content="' . esc_attr( $locale ) . "\" />\n";
		}
		if ( $og_image ) {
			echo '<meta property="og:image" content="' . esc_url( $og_image ) . "\" />\n";
			echo '<meta property="og:image:secure_url" content="' . esc_url( $og_image ) . "\" />\n";
		}

		// article:* para posts.
		if ( is_singular( 'post' ) ) {
			$post = get_post();
			if ( $post ) {
				echo '<meta property="article:published_time" content="' . esc_attr( get_the_date( 'c', $post ) ) . "\" />\n";
				echo '<meta property="article:modified_time" content="' . esc_attr( get_the_modified_date( 'c', $post ) ) . "\" />\n";
				$author = get_the_author_meta( 'display_name', $post->post_author );
				if ( $author ) {
					echo '<meta property="article:author" content="' . esc_attr( $author ) . "\" />\n";
				}
				foreach ( (array) get_the_category( $post->ID ) as $cat ) {
					echo '<meta property="article:section" content="' . esc_attr( $cat->name ) . "\" />\n";
				}
				$tags = get_the_tags( $post->ID );
				if ( $tags && ! is_wp_error( $tags ) ) {
					foreach ( $tags as $tag ) {
						echo '<meta property="article:tag" content="' . esc_attr( $tag->name ) . "\" />\n";
					}
				}
			}
		}

		// Twitter Card.
		echo '<meta name="twitter:card" content="' . esc_attr( $card_type ) . "\" />\n";
		if ( $twitter_usr ) {
			$user = '@' . ltrim( $twitter_usr, '@' );
			echo '<meta name="twitter:site" content="' . esc_attr( $user ) . "\" />\n";
			echo '<meta name="twitter:creator" content="' . esc_attr( $user ) . "\" />\n";
		}
		if ( $og_title ) {
			echo '<meta name="twitter:title" content="' . esc_attr( $og_title ) . "\" />\n";
		}
		if ( $og_desc ) {
			echo '<meta name="twitter:description" content="' . esc_attr( $og_desc ) . "\" />\n";
		}
		if ( $og_image ) {
			echo '<meta name="twitter:image" content="' . esc_url( $og_image ) . "\" />\n";
		}

		echo "<!-- /AFCA Basic SEO -->\n\n";
	}

	/* ---------------------- getters ---------------------- */

	private function get_meta_title() {
		if ( is_singular() ) {
			return (string) get_post_meta( get_queried_object_id(), AFCA_SEO_Meta::META_PREFIX . 'title', true );
		}
		if ( is_category() || is_tag() || is_tax() ) {
			$term = get_queried_object();
			if ( $term && isset( $term->term_id ) ) {
				return (string) get_term_meta( $term->term_id, AFCA_SEO_Meta::META_PREFIX . 'title', true );
			}
		}
		return '';
	}

	private function get_meta_description() {
		if ( is_singular() ) {
			$post_id = get_queried_object_id();
			$v       = get_post_meta( $post_id, AFCA_SEO_Meta::META_PREFIX . 'description', true );
			if ( $v ) {
				return $v; }

			$post = get_post( $post_id );
			if ( $post ) {
				if ( ! empty( $post->post_excerpt ) ) {
					return wp_strip_all_tags( $post->post_excerpt );
				}
				$content = wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
				return wp_trim_words( $content, 30, '…' );
			}
		} elseif ( is_category() || is_tag() || is_tax() ) {
			$term = get_queried_object();
			if ( $term && isset( $term->term_id ) ) {
				$v = get_term_meta( $term->term_id, AFCA_SEO_Meta::META_PREFIX . 'description', true );
				if ( $v ) {
					return $v; }
				if ( ! empty( $term->description ) ) {
					return wp_strip_all_tags( $term->description );
				}
			}
		} elseif ( is_front_page() || is_home() ) {
			return get_bloginfo( 'description' );
		}
		return '';
	}

	private function get_keywords() {
		if ( is_singular() ) {
			return (string) get_post_meta( get_queried_object_id(), AFCA_SEO_Meta::META_PREFIX . 'keywords', true );
		}
		if ( is_category() || is_tag() || is_tax() ) {
			$term = get_queried_object();
			if ( $term && isset( $term->term_id ) ) {
				return (string) get_term_meta( $term->term_id, AFCA_SEO_Meta::META_PREFIX . 'keywords', true );
			}
		}
		return '';
	}

	private function get_og_title() {
		if ( is_singular() ) {
			$v = get_post_meta( get_queried_object_id(), AFCA_SEO_Meta::META_PREFIX . 'og_title', true );
			if ( $v ) {
				return $v; }
		} elseif ( is_category() || is_tag() || is_tax() ) {
			$term = get_queried_object();
			if ( $term && isset( $term->term_id ) ) {
				$v = get_term_meta( $term->term_id, AFCA_SEO_Meta::META_PREFIX . 'og_title', true );
				if ( $v ) {
					return $v; }
			}
		}
		$meta = $this->get_meta_title();
		if ( $meta ) {
			return $meta; }
		return wp_get_document_title();
	}

	private function get_og_description() {
		if ( is_singular() ) {
			$v = get_post_meta( get_queried_object_id(), AFCA_SEO_Meta::META_PREFIX . 'og_description', true );
			if ( $v ) {
				return $v; }
		} elseif ( is_category() || is_tag() || is_tax() ) {
			$term = get_queried_object();
			if ( $term && isset( $term->term_id ) ) {
				$v = get_term_meta( $term->term_id, AFCA_SEO_Meta::META_PREFIX . 'og_description', true );
				if ( $v ) {
					return $v; }
			}
		}
		return $this->get_meta_description();
	}

	private function get_og_image() {
		$image = '';
		if ( is_singular() ) {
			$post_id = get_queried_object_id();
			$image   = get_post_meta( $post_id, AFCA_SEO_Meta::META_PREFIX . 'og_image', true );
			if ( ! $image && has_post_thumbnail( $post_id ) ) {
				$image = get_the_post_thumbnail_url( $post_id, 'large' );
			}
		} elseif ( is_category() || is_tag() || is_tax() ) {
			$term = get_queried_object();
			if ( $term && isset( $term->term_id ) ) {
				$image = get_term_meta( $term->term_id, AFCA_SEO_Meta::META_PREFIX . 'og_image', true );
			}
		}
		if ( ! $image ) {
			$image = AFCA_SEO::get_option( 'og_default_image', '' );
		}
		return $image;
	}

	private function get_canonical_url() {
		if ( is_singular() ) {
			$custom = get_post_meta( get_queried_object_id(), AFCA_SEO_Meta::META_PREFIX . 'canonical', true );
			return $custom ? $custom : get_permalink();
		}
		if ( is_category() || is_tag() || is_tax() ) {
			$term = get_queried_object();
			if ( $term && isset( $term->term_id ) ) {
				$custom = get_term_meta( $term->term_id, AFCA_SEO_Meta::META_PREFIX . 'canonical', true );
				return $custom ? $custom : get_term_link( $term );
			}
		}
		if ( is_front_page() || is_home() ) {
			return home_url( '/' );
		}
		if ( is_author() ) {
			return get_author_posts_url( get_queried_object_id() );
		}
		return '';
	}

	private function get_og_type() {
		if ( is_singular( 'post' ) ) {
			return 'article';
		}
		if ( is_singular() ) {
			return 'article';
		}
		return 'website';
	}

	public function enqueue_style() {
		wp_enqueue_style( 'afca-basic-seo-frontend', AFCA_SEO_URL . '/assets/frontend.css', [], AFCA_SEO_VERSION );
	}
}
