<?php
/**
 * Limpeza quando o plugin é desinstalado.
 *
 * Nota: só apaga a opção de configuração.
 * Meta de posts/termos fica preservada caso o plugin seja reinstalado.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

delete_option( 'afca_seo_options' );
