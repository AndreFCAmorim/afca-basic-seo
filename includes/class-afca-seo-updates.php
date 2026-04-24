<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AFCA_SEO_Updates {

	private $remote_response;
	private string $update_hub;
	private string $plugin_name;
	private string $plugin_version;

	public function __construct( $hub, $uid, $version ) {
		$this->remote_response = get_transient( 'afca-seo-basic-update-api-response' );
		$this->update_hub      = $hub;
		$this->plugin_name     = $uid;
		$this->plugin_version  = $version;

		add_filter( 'site_transient_update_plugins', [ $this, 'get_plugin_updates' ] );
	}

	public function get_plugin_updates( $transient ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		if ( ! isset( $this->remote_response->id ) ) {
			return $transient;
		}

		$data = (object) [
			'id'             => $this->plugin_name,
			'new_version'    => $this->remote_response->version,
			'package'        => $this->remote_response->url,
			'url'            => $this->update_hub . 'afca-software-lib/' . $this->plugin_name,
			'requires'       => $this->remote_response->wp_required,
			'tested'         => $this->remote_response->wp_tested,
			'released'       => $this->remote_response->released_date,
			'upgrade_notice' => $this->remote_response->released_notes,
		];

		if ( version_compare( $this->plugin_version, $data->new_version, '<' ) ) {
			if ( isset( $transient->response ) && is_array( $transient->response ) ) {
				$transient->response['afca-seo-basic/afca-seo-basic.php'] = $data;
			}
		}

		return $transient;
	}

	public function check_for_updates_on_hub() {
		$ssl_verify = true; // Initial SSL verification option

		try {
			$this->remote_response = wp_remote_get(
				$this->update_hub . 'wp-json/afca-software-library/v1/ref/' . $this->plugin_name,
				[
					'timeout'   => 30,
					'headers'   => [
						'Accept' => 'application/json',
					],
					'sslverify' => $ssl_verify,
				]
			);
		} catch ( \Exception $ex ) {
			error_log( 'It was not possible to check for updates: ' . $ex->getMessage() );
			return;
		}

		$response_code = wp_remote_retrieve_response_code( $this->remote_response );

		// Retry with SSL verification disabled if there is an error
		if ( is_wp_error( $this->remote_response ) || $response_code !== 200 || empty( $response_code ) ) {
			try {
				$ssl_verify            = false; // Disable SSL verification
				$this->remote_response = wp_remote_get(
					$this->update_hub . 'wp-json/afca-software-library/v1/ref/' . $this->plugin_name,
					[
						'timeout'   => 30,
						'headers'   => [
							'Accept' => 'application/json',
						],
						'sslverify' => $ssl_verify,
					]
				);
			} catch ( \Exception $ex ) {
				error_log( 'It was not possible to check for updates: ' . $ex->getMessage() );
				return;
			}
		}

		$response_code = wp_remote_retrieve_response_code( $this->remote_response );

		// Bug Fix When Response is 403 Forbidden
		if ( $response_code == 403 || $response_code == '' ) {
			$this->remote_response = file_get_contents( $this->update_hub . 'wp-json/afca-software-library/v1/ref/' . $this->plugin_name );
		}

		if ( is_wp_error( $this->remote_response ) && ( wp_remote_retrieve_response_code( $this->remote_response ) !== 200 || empty( wp_remote_retrieve_body( $this->remote_response ) ) ) ) {
			error_log( $this->remote_response->get_error_message() );
			return;
		} else {
			if ( is_string( $this->remote_response ) ) {
				$this->remote_response = json_decode( $this->remote_response );
			} else {
				$this->remote_response = json_decode( wp_remote_retrieve_body( $this->remote_response ) );
			}
			set_transient( 'afca-seo-basic-update-api-response', $this->remote_response, 86400 );
		}
	}
}
