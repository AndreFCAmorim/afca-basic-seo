<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AFCA_SEO_Meta {

	const META_PREFIX = '_afca_seo_';

	const FIELDS = [ 'title', 'description', 'keywords', 'canonical', 'noindex', 'nofollow', 'og_title', 'og_description', 'og_image' ];

	public function __construct() {
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
		add_action( 'save_post', [ $this, 'save_post_meta' ], 10, 2 );
		add_action( 'registered_taxonomy', [ $this, 'hook_term_fields' ], 10, 1 );
	}

	public function hook_term_fields( $taxonomy ) {
		$tax_obj = get_taxonomy( $taxonomy );
		if ( ! $tax_obj || empty( $tax_obj->public ) ) {
			return;
		}
		add_action( "{$taxonomy}_add_form_fields", [ $this, 'render_term_add_fields' ] );
		add_action( "{$taxonomy}_edit_form_fields", [ $this, 'render_term_edit_fields' ] );
		add_action( "created_{$taxonomy}", [ $this, 'save_term_meta' ] );
		add_action( "edited_{$taxonomy}", [ $this, 'save_term_meta' ] );
	}

	public function add_meta_boxes() {
		$post_types = get_post_types( [ 'public' => true ], 'names' );
		foreach ( $post_types as $pt ) {
			add_meta_box(
				'afca_seo_meta',
				__( 'AFCA SEO', 'afca-basic-seo' ),
				[ $this, 'render_post_meta_box' ],
				$pt,
				'normal',
				'default'
			);
		}
	}

	public function render_post_meta_box( $post ) {
		wp_nonce_field( 'afca_seo_meta_save', 'afca_seo_nonce' );
		$values = [];
		foreach ( self::FIELDS as $field ) {
			$values[ $field ] = get_post_meta( $post->ID, self::META_PREFIX . $field, true );
		}
		$this->render_fields( $values, 'afca_seo', 'post' );
	}

	private function render_fields( $values, $input_name, $context ) {
		$image_input_id = 'afca_og_image_' . $context;
		?>
		<div class="afca-seo-fields">
			<p>
				<label for="afca_meta_title_<?php echo esc_attr( $context ); ?>">
					<strong><?php esc_html_e( 'Meta Title', 'afca-basic-seo' ); ?></strong>
				</label>
				<input type="text" id="afca_meta_title_<?php echo esc_attr( $context ); ?>" name="<?php echo esc_attr( $input_name ); ?>[title]" value="<?php echo esc_attr( $values['title'] ); ?>" class="widefat afca-count" data-recommended="60" maxlength="200">
				<span class="afca-char-count"></span>
				<span class="description"><?php esc_html_e( 'Recommended: 50–60 characters. Leave empty to use the default title.', 'afca-basic-seo' ); ?></span>
			</p>

			<p>
				<label for="afca_meta_desc_<?php echo esc_attr( $context ); ?>">
					<strong><?php esc_html_e( 'Meta Description', 'afca-basic-seo' ); ?></strong>
				</label>
				<textarea id="afca_meta_desc_<?php echo esc_attr( $context ); ?>" name="<?php echo esc_attr( $input_name ); ?>[description]" rows="3" class="widefat afca-count" data-recommended="160" maxlength="320"><?php echo esc_textarea( $values['description'] ); ?></textarea>
				<span class="afca-char-count"></span>
				<span class="description"><?php esc_html_e( 'Recommended: 120–160 characters.', 'afca-basic-seo' ); ?></span>
			</p>

			<p>
				<label for="afca_keywords_<?php echo esc_attr( $context ); ?>">
					<strong><?php esc_html_e( 'Keywords', 'afca-basic-seo' ); ?></strong>
				</label>
				<input type="text" id="afca_keywords_<?php echo esc_attr( $context ); ?>" name="<?php echo esc_attr( $input_name ); ?>[keywords]" value="<?php echo esc_attr( $values['keywords'] ); ?>" class="widefat">
				<span class="description"><?php esc_html_e( 'Separated by commas.', 'afca-basic-seo' ); ?></span>
			</p>

			<p>
				<label for="afca_canonical_<?php echo esc_attr( $context ); ?>">
					<strong><?php esc_html_e( 'Canonical URL', 'afca-basic-seo' ); ?></strong>
				</label>
				<input type="url" id="afca_canonical_<?php echo esc_attr( $context ); ?>" name="<?php echo esc_attr( $input_name ); ?>[canonical]" value="<?php echo esc_attr( $values['canonical'] ); ?>" class="widefat">
				<span class="description"><?php esc_html_e( 'Leave empty to use the default URL.', 'afca-basic-seo' ); ?></span>
			</p>

			<p>
				<label>
					<input type="checkbox" name="<?php echo esc_attr( $input_name ); ?>[noindex]" value="1" <?php checked( (int) $values['noindex'], 1 ); ?>>
					<strong><?php esc_html_e( 'Noindex', 'afca-basic-seo' ); ?></strong>
					<span class="description"><?php esc_html_e( '(do not index in search engines)', 'afca-basic-seo' ); ?></span>
				</label>
				&nbsp;&nbsp;
				<label>
					<input type="checkbox" name="<?php echo esc_attr( $input_name ); ?>[nofollow]" value="1" <?php checked( (int) $values['nofollow'], 1 ); ?>>
					<strong><?php esc_html_e( 'Nofollow', 'afca-basic-seo' ); ?></strong>
					<span class="description"><?php esc_html_e( '(do not follow links on this page)', 'afca-basic-seo' ); ?></span>
				</label>
			</p>

			<hr>
			<h3><?php esc_html_e( 'Open Graph / Social Networks', 'afca-basic-seo' ); ?></h3>

			<p>
				<label for="afca_og_title_<?php echo esc_attr( $context ); ?>">
					<strong><?php esc_html_e( 'OG Title', 'afca-basic-seo' ); ?></strong>
				</label>
				<input type="text" id="afca_og_title_<?php echo esc_attr( $context ); ?>" name="<?php echo esc_attr( $input_name ); ?>[og_title]" value="<?php echo esc_attr( $values['og_title'] ); ?>" class="widefat">
				<span class="description"><?php esc_html_e( 'Leave empty to reuse the Meta Title.', 'afca-basic-seo' ); ?></span>
			</p>

			<p>
				<label for="afca_og_desc_<?php echo esc_attr( $context ); ?>">
					<strong><?php esc_html_e( 'OG Description', 'afca-basic-seo' ); ?></strong>
				</label>
				<textarea id="afca_og_desc_<?php echo esc_attr( $context ); ?>" name="<?php echo esc_attr( $input_name ); ?>[og_description]" rows="2" class="widefat"><?php echo esc_textarea( $values['og_description'] ); ?></textarea>
			</p>

			<p class="afca-image-picker">
				<label><strong><?php esc_html_e( 'OG Image', 'afca-basic-seo' ); ?></strong></label>
				<input type="hidden" name="<?php echo esc_attr( $input_name ); ?>[og_image]" id="<?php echo esc_attr( $image_input_id ); ?>" value="<?php echo esc_attr( $values['og_image'] ); ?>">
				<span class="afca-image-preview">
					<?php if ( $values['og_image'] ) : ?>
						<img src="<?php echo esc_url( $values['og_image'] ); ?>" alt="">
					<?php endif; ?>
				</span>
				<span class="afca-image-buttons">
					<button type="button" class="button afca-upload-image" data-target="<?php echo esc_attr( $image_input_id ); ?>"><?php esc_html_e( 'Choose image', 'afca-basic-seo' ); ?></button>
					<button type="button" class="button afca-remove-image" data-target="<?php echo esc_attr( $image_input_id ); ?>"><?php esc_html_e( 'Remove', 'afca-basic-seo' ); ?></button>
				</span>
				<span class="description"><?php esc_html_e( 'Recommended: 1200×630px. If empty, uses the featured image or the default OG image.', 'afca-basic-seo' ); ?></span>
			</p>
		</div>
		<?php
	}

	public function save_post_meta( $post_id, $post ) {
		if ( ! isset( $_POST['afca_seo_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['afca_seo_nonce'] ) ), 'afca_seo_meta_save' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( ! isset( $_POST['afca_seo'] ) || ! is_array( $_POST['afca_seo'] ) ) {
			return;
		}

		$data = wp_unslash( $_POST['afca_seo'] );

		foreach ( self::FIELDS as $field ) {
			$value = isset( $data[ $field ] ) ? $data[ $field ] : '';
			$value = $this->sanitize_field( $field, $value );

			if ( $value === '' || $value === 0 ) {
				delete_post_meta( $post_id, self::META_PREFIX . $field );
			} else {
				update_post_meta( $post_id, self::META_PREFIX . $field, $value );
			}
		}
	}

	private function sanitize_field( $field, $value ) {
		switch ( $field ) {
			case 'description':
			case 'og_description':
				return sanitize_textarea_field( $value );
			case 'canonical':
			case 'og_image':
				return esc_url_raw( $value );
			case 'noindex':
			case 'nofollow':
				return ! empty( $value ) ? 1 : 0;
			default:
				return sanitize_text_field( $value );
		}
	}

	public function render_term_add_fields( $taxonomy ) {
		wp_nonce_field( 'afca_seo_term_save', 'afca_seo_term_nonce' );
		$empty = array_fill_keys( self::FIELDS, '' );
		?>
		<h2 style="margin-top:30px;"><?php esc_html_e( 'AFCA SEO', 'afca-basic-seo' ); ?></h2>
		<div class="form-field afca-term-add">
			<?php $this->render_fields( $empty, 'afca_seo_term', 'term' ); ?>
		</div>
		<?php
	}

	public function render_term_edit_fields( $term ) {
		wp_nonce_field( 'afca_seo_term_save', 'afca_seo_term_nonce' );
		$values = [];
		foreach ( self::FIELDS as $field ) {
			$values[ $field ] = get_term_meta( $term->term_id, self::META_PREFIX . $field, true );
		}
		?>
		<tr class="form-field afca-term-section">
			<th colspan="2"><h2 style="margin:0;"><?php esc_html_e( 'AFCA SEO', 'afca-basic-seo' ); ?></h2></th>
		</tr>
		<tr class="form-field">
			<th scope="row"><?php esc_html_e( 'SEO Fields', 'afca-basic-seo' ); ?></th>
			<td>
				<?php $this->render_fields( $values, 'afca_seo_term', 'term' ); ?>
			</td>
		</tr>
		<?php
	}

	public function save_term_meta( $term_id ) {
		if ( ! isset( $_POST['afca_seo_term_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['afca_seo_term_nonce'] ) ), 'afca_seo_term_save' ) ) {
			return;
		}
		if ( ! current_user_can( 'manage_categories' ) ) {
			return;
		}
		if ( ! isset( $_POST['afca_seo_term'] ) || ! is_array( $_POST['afca_seo_term'] ) ) {
			return;
		}

		$data = wp_unslash( $_POST['afca_seo_term'] );

		foreach ( self::FIELDS as $field ) {
			$value = isset( $data[ $field ] ) ? $data[ $field ] : '';
			$value = $this->sanitize_field( $field, $value );

			if ( $value === '' || $value === 0 ) {
				delete_term_meta( $term_id, self::META_PREFIX . $field );
			} else {
				update_term_meta( $term_id, self::META_PREFIX . $field, $value );
			}
		}
	}
}