<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Página de definições do plugin (Geral / Social / Sitemap).
 */
class AFCA_SEO_Admin {

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_menu' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_filter( 'plugin_action_links_' . plugin_basename( AFCA_SEO_FILE ), [ $this, 'action_links' ] );
	}

	public function action_links( $links ) {
		$url = admin_url( 'admin.php?page=afca-seo' );
		array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Definições', 'afca-basic-seo' ) . '</a>' );
		return $links;
	}

	public function add_menu() {
		add_menu_page(
			__( 'AFCA SEO', 'afca-basic-seo' ),
			__( 'AFCA SEO', 'afca-basic-seo' ),
			'manage_options',
			'afca-seo',
			[ $this, 'render_page' ],
			'dashicons-chart-line',
			80
		);
	}

	public function register_settings() {
		register_setting(
			'afca_seo_settings',
			AFCA_SEO::OPTION_KEY,
			[ 'sanitize_callback' => [ $this, 'sanitize_settings' ] ]
		);
	}

	public function sanitize_settings( $input ) {
		if ( ! is_array( $input ) ) {
			$input = [];
		}

		$clean                         = [];
		$clean['separator']            = isset( $input['separator'] ) ? sanitize_text_field( $input['separator'] ) : '|';
		$clean['breadcrumb_separator'] = isset( $input['breadcrumb_separator'] ) ? sanitize_text_field( $input['breadcrumb_separator'] ) : '/';
		if ( $clean['breadcrumb_separator'] === '' ) {
			$clean['breadcrumb_separator'] = '/';
		}
		$clean['og_default_image'] = isset( $input['og_default_image'] ) ? esc_url_raw( $input['og_default_image'] ) : '';
		$clean['og_site_name']     = isset( $input['og_site_name'] ) ? sanitize_text_field( $input['og_site_name'] ) : '';

		$card                       = isset( $input['twitter_card_type'] ) ? $input['twitter_card_type'] : 'summary_large_image';
		$clean['twitter_card_type'] = in_array( $card, [ 'summary', 'summary_large_image' ], true ) ? $card : 'summary_large_image';

		$clean['twitter_username'] = isset( $input['twitter_username'] ) ? sanitize_text_field( $input['twitter_username'] ) : '';

		$clean['sitemap_post_types'] = [];
		if ( ! empty( $input['sitemap_post_types'] ) && is_array( $input['sitemap_post_types'] ) ) {
			foreach ( $input['sitemap_post_types'] as $pt ) {
				$clean['sitemap_post_types'][] = sanitize_key( $pt );
			}
		}

		$clean['sitemap_taxonomies'] = [];
		if ( ! empty( $input['sitemap_taxonomies'] ) && is_array( $input['sitemap_taxonomies'] ) ) {
			foreach ( $input['sitemap_taxonomies'] as $tax ) {
				$clean['sitemap_taxonomies'][] = sanitize_key( $tax );
			}
		}

		$clean['sitemap_include_authors'] = ! empty( $input['sitemap_include_authors'] );

		return $clean;
	}

	public function enqueue_assets( $hook ) {
		$load_on        = [ 'post.php', 'post-new.php', 'term.php', 'edit-tags.php' ];
		$is_plugin_page = is_string( $hook ) && strpos( $hook, 'afca-seo' ) !== false;
		if ( ! $is_plugin_page && ! in_array( $hook, $load_on, true ) ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_style( 'afca-seo-admin', AFCA_SEO_URL . 'assets/admin.css', [], AFCA_SEO_VERSION );
		wp_enqueue_script( 'afca-seo-admin', AFCA_SEO_URL . 'assets/admin.js', [ 'jquery' ], AFCA_SEO_VERSION, true );
		wp_localize_script(
			'afca-seo-admin',
			'AFCA_SEO_I18N',
			[
				'pickTitle'  => __( 'Escolher imagem', 'afca-basic-seo' ),
				'pickButton' => __( 'Usar esta imagem', 'afca-basic-seo' ),
				'chars'      => __( 'caracteres', 'afca-basic-seo' ),
			]
		);
	}

	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$options    = AFCA_SEO::get_options();
		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general';
		$allowed    = [ 'general', 'social', 'sitemap' ];
		if ( ! in_array( $active_tab, $allowed, true ) ) {
			$active_tab = 'general';
		}
		?>
		<div class="wrap afca-seo-wrap">
			<h1><?php esc_html_e( 'AFCA Basic SEO', 'afca-basic-seo' ); ?></h1>
			<p class="description"><?php esc_html_e( 'Alternativa leve ao Yoast — só o essencial.', 'afca-basic-seo' ); ?></p>

			<h2 class="nav-tab-wrapper">
				<a href="?page=afca-seo&tab=general" class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Geral', 'afca-basic-seo' ); ?>
				</a>
				<a href="?page=afca-seo&tab=social" class="nav-tab <?php echo $active_tab === 'social' ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Social / Open Graph', 'afca-basic-seo' ); ?>
				</a>
				<a href="?page=afca-seo&tab=sitemap" class="nav-tab <?php echo $active_tab === 'sitemap' ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Sitemap', 'afca-basic-seo' ); ?>
				</a>
			</h2>

			<form method="post" action="options.php">
				<?php settings_fields( 'afca_seo_settings' ); ?>

				<?php if ( $active_tab === 'general' ) : ?>
					<?php $this->render_tab_general( $options ); ?>
				<?php elseif ( $active_tab === 'social' ) : ?>
					<?php $this->render_tab_social( $options ); ?>
				<?php elseif ( $active_tab === 'sitemap' ) : ?>
					<?php $this->render_tab_sitemap( $options ); ?>
				<?php endif; ?>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	private function render_tab_general( $options ) {
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<label for="afca_separator"><?php esc_html_e( 'Separador de título', 'afca-basic-seo' ); ?></label>
				</th>
				<td>
					<input type="text" id="afca_separator" name="afca_seo_options[separator]" value="<?php echo esc_attr( $options['separator'] ); ?>" class="small-text" maxlength="5">
					<p class="description"><?php esc_html_e( 'Carácter entre partes do título (ex: | - • »).', 'afca-basic-seo' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="afca_breadcrumb_separator"><?php esc_html_e( 'Separador de breadcrumbs', 'afca-basic-seo' ); ?></label>
				</th>
				<td>
					<input type="text" id="afca_breadcrumb_separator" name="afca_seo_options[breadcrumb_separator]" value="<?php echo esc_attr( $options['breadcrumb_separator'] ); ?>" class="small-text" maxlength="10">
					<p class="description">
						<?php esc_html_e( 'Carácter entre items do breadcrumb (ex: / » > ›). Use o shortcode [afca_breadcrumbs] no tema ou num widget. Pode ser sobreposto por shortcode com separator="…".', 'afca-basic-seo' ); ?>
					</p>
				</td>
			</tr>
		</table>
		<?php
	}

	private function render_tab_social( $options ) {
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'Imagem OG por defeito', 'afca-basic-seo' ); ?></th>
				<td>
					<div class="afca-image-picker">
						<input type="hidden" name="afca_seo_options[og_default_image]" id="afca_og_default_image" value="<?php echo esc_attr( $options['og_default_image'] ); ?>">
						<div class="afca-image-preview">
							<?php if ( $options['og_default_image'] ) : ?>
								<img src="<?php echo esc_url( $options['og_default_image'] ); ?>" alt="">
							<?php endif; ?>
						</div>
						<p>
							<button type="button" class="button afca-upload-image" data-target="afca_og_default_image"><?php esc_html_e( 'Escolher imagem', 'afca-basic-seo' ); ?></button>
							<button type="button" class="button afca-remove-image" data-target="afca_og_default_image"><?php esc_html_e( 'Remover', 'afca-basic-seo' ); ?></button>
						</p>
					</div>
					<p class="description"><?php esc_html_e( 'Usada quando o conteúdo não tem imagem destacada nem imagem OG personalizada. Recomendado: 1200×630px.', 'afca-basic-seo' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="afca_og_site_name"><?php esc_html_e( 'Nome do site (OG)', 'afca-basic-seo' ); ?></label>
				</th>
				<td>
					<input type="text" id="afca_og_site_name" name="afca_seo_options[og_site_name]" value="<?php echo esc_attr( $options['og_site_name'] ); ?>" class="regular-text">
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="afca_twitter_card_type"><?php esc_html_e( 'Tipo de Twitter Card', 'afca-basic-seo' ); ?></label>
				</th>
				<td>
					<select id="afca_twitter_card_type" name="afca_seo_options[twitter_card_type]">
						<option value="summary" <?php selected( $options['twitter_card_type'], 'summary' ); ?>>summary</option>
						<option value="summary_large_image" <?php selected( $options['twitter_card_type'], 'summary_large_image' ); ?>>summary_large_image</option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="afca_twitter_username"><?php esc_html_e( 'Utilizador Twitter/X', 'afca-basic-seo' ); ?></label>
				</th>
				<td>
					<input type="text" id="afca_twitter_username" name="afca_seo_options[twitter_username]" value="<?php echo esc_attr( $options['twitter_username'] ); ?>" class="regular-text" placeholder="@exemplo">
				</td>
			</tr>
		</table>
		<?php
	}

	private function render_tab_sitemap( $options ) {
		$post_types = get_post_types( [ 'public' => true ], 'objects' );
		$taxonomies = get_taxonomies( [ 'public' => true ], 'objects' );
		?>
		<h2><?php esc_html_e( 'Tipos de conteúdo', 'afca-basic-seo' ); ?></h2>
		<p><?php esc_html_e( 'Marque os tipos de conteúdo que devem aparecer no sitemap.', 'afca-basic-seo' ); ?></p>
		<table class="form-table afca-checklist" role="presentation">
			<?php
			foreach ( $post_types as $pt ) :
				if ( $pt->name === 'attachment' ) {
					continue; }
				?>
				<tr>
					<th scope="row"><?php echo esc_html( $pt->label ); ?> <code><?php echo esc_html( $pt->name ); ?></code></th>
					<td>
						<label>
							<input type="checkbox" name="afca_seo_options[sitemap_post_types][]" value="<?php echo esc_attr( $pt->name ); ?>" <?php checked( in_array( $pt->name, $options['sitemap_post_types'], true ) ); ?>>
							<?php esc_html_e( 'Incluir no sitemap', 'afca-basic-seo' ); ?>
						</label>
					</td>
				</tr>
			<?php endforeach; ?>
		</table>

		<h2><?php esc_html_e( 'Taxonomias', 'afca-basic-seo' ); ?></h2>
		<p><?php esc_html_e( 'Marque as taxonomias que devem aparecer no sitemap.', 'afca-basic-seo' ); ?></p>
		<table class="form-table afca-checklist" role="presentation">
			<?php foreach ( $taxonomies as $tax ) : ?>
				<tr>
					<th scope="row"><?php echo esc_html( $tax->label ); ?> <code><?php echo esc_html( $tax->name ); ?></code></th>
					<td>
						<label>
							<input type="checkbox" name="afca_seo_options[sitemap_taxonomies][]" value="<?php echo esc_attr( $tax->name ); ?>" <?php checked( in_array( $tax->name, $options['sitemap_taxonomies'], true ) ); ?>>
							<?php esc_html_e( 'Incluir no sitemap', 'afca-basic-seo' ); ?>
						</label>
					</td>
				</tr>
			<?php endforeach; ?>
		</table>

		<h2><?php esc_html_e( 'Autores', 'afca-basic-seo' ); ?></h2>
		<table class="form-table afca-checklist" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'Arquivo de autores', 'afca-basic-seo' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="afca_seo_options[sitemap_include_authors]" value="1" <?php checked( $options['sitemap_include_authors'] ); ?>>
						<?php esc_html_e( 'Incluir páginas de autor no sitemap', 'afca-basic-seo' ); ?>
					</label>
				</td>
			</tr>
		</table>

		<p>
			<a href="<?php echo esc_url( home_url( '/wp-sitemap.xml' ) ); ?>" target="_blank" rel="noopener" class="button">
				<?php esc_html_e( 'Ver sitemap', 'afca-basic-seo' ); ?>
			</a>
		</p>
		<?php
	}
}
