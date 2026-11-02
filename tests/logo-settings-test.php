<?php
/**
 * Pruebas aisladas: php wp-content/plugins/tarjetas/tests/logo-settings-test.php
 * No requieren WordPress ni modifican su base de datos.
 */
if ( 'cli' !== PHP_SAPI ) {
	exit;
}

define( 'ABSPATH', __DIR__ );
$meta = array();
$options = array();
$hooks = array();
$images = array( 10, 20, 30 );
$settings_errors = array();
$enqueued = array();
$checks = 0;

function add_action( $hook, $callback, $priority = 10, $args = 1 ) {
	$GLOBALS['hooks'][ $hook ][ $priority ][] = array( $callback, $args );
}
function add_filter( $hook, $callback, $priority = 10, $args = 1 ) {}
function register_activation_hook( $file, $callback ) {}
function register_deactivation_hook( $file, $callback ) {}
function absint( $value ) { return abs( (int) $value ); }
function get_option( $key, $default = false ) { return $GLOBALS['options'][ $key ] ?? $default; }
function get_post_meta( $id, $key, $single = false ) { return $GLOBALS['meta'][ $id ][ $key ] ?? ''; }
function metadata_exists( $type, $id, $key ) { return array_key_exists( $key, $GLOBALS['meta'][ $id ] ?? array() ); }
function update_post_meta( $id, $key, $value ) { $GLOBALS['meta'][ $id ][ $key ] = $value; }
function add_post_meta( $id, $key, $value, $unique = false ) {
	if ( ! $unique || ! metadata_exists( 'post', $id, $key ) ) {
		update_post_meta( $id, $key, $value );
	}
}
function wp_is_post_revision( $id ) { return 999 === $id; }
function get_post_type( $id ) { return in_array( $id, array( 10, 20, 30, 40 ), true ) ? 'attachment' : 'tarjeta'; }
function get_post_mime_type( $id ) { return 40 === $id ? 'application/pdf' : 'image/png'; }
function wp_attachment_is_image( $id ) { return in_array( $id, $GLOBALS['images'], true ); }
function wp_get_attachment_image_url( $id, $size ) { return wp_attachment_is_image( $id ) ? 'https://example.test/logo-' . $id . '.png' : false; }
function plugin_dir_url( $file ) { return 'https://example.test/plugins/tarjetas/'; }
function wp_verify_nonce( $nonce, $action ) { return 'valid' === $nonce; }
function wp_unslash( $value ) { return $value; }
function wp_strip_all_tags( $value ) { return strip_tags( $value ); }
function sanitize_text_field( $value ) { return trim( strip_tags( $value ) ); }
function sanitize_email( $value ) { return $value; }
function sanitize_hex_color( $value ) { return preg_match( '/^#[0-9a-f]{6}$/i', $value ) ? $value : null; }
function current_user_can( $capability, $id = null ) { return true; }
function __( $text, $domain = null ) { return $text; }
function add_settings_error( $setting, $code, $message ) { $GLOBALS['settings_errors'][] = $code; }
function get_the_title( $id ) { return 'Tarjeta de prueba'; }
function get_the_terms( $id, $taxonomy ) { return false; }
function get_permalink( $id ) { return 'https://example.test/tarjetas/' . $id; }
function esc_url_raw( $url ) { return $url; }
function add_submenu_page( $parent, $title, $menu_title, $capability, $slug, $callback ) { return 'tarjetas_page_' . $slug; }
function get_current_screen() { return (object) array( 'post_type' => 'tarjeta' ); }
function wp_enqueue_media() { $GLOBALS['enqueued'][] = 'media'; }
function wp_enqueue_script( $handle, $src, $dependencies, $version, $footer ) { $GLOBALS['enqueued'][] = $handle; }
function wp_localize_script( $handle, $name, $values ) {}

require dirname( __DIR__ ) . '/tarjetas.php';

function check( $expected, $actual, $message ) {
	$GLOBALS['checks']++;
	if ( $expected !== $actual ) {
		throw new RuntimeException( $message . ': esperado ' . var_export( $expected, true ) . ', recibido ' . var_export( $actual, true ) );
	}
}

function save_card( $id, $update, $fields = array() ) {
	$_POST = array_merge( array( 'tarjetas_datos_nonce' => 'valid' ), $fields );
	$post = (object) array( 'ID' => $id, 'post_type' => 'tarjeta', 'post_status' => $update ? 'draft' : 'auto-draft' );
	$callbacks = $GLOBALS['hooks']['save_post_tarjeta'];
	ksort( $callbacks );
	foreach ( $callbacks as $group ) {
		foreach ( $group as $entry ) {
			call_user_func_array( $entry[0], array_slice( array( $id, $post, $update ), 0, $entry[1] ) );
		}
	}
}

$builtin = tarjetas_logo_predeterminado_url();
check( 0, tarjetas_logo_configurado_id(), 'McGregor es el ajuste inicial' );
save_card( 1, false );
check( true, metadata_exists( 'post', 1, '_tarjetas_logo_inicial_id' ), 'Se guarda el marcador incluso con ID 0' );
check( $builtin, tarjetas_logo_tarjeta_url( 1 ), 'Primera tarjeta con McGregor' );

$options['tarjetas_logo_predeterminado_id'] = 10;
check( $builtin, tarjetas_logo_tarjeta_url( 1 ), 'El cambio global no modifica la primera tarjeta' );
save_card( 2, false );
check( 10, tarjetas_logo_inicial_id( 2 ), 'Nueva tarjeta captura el ajuste global' );
check( 'https://example.test/logo-10.png', tarjetas_logo_tarjeta_url( 2 ), 'Logo guardado en borrador' );

$options['tarjetas_logo_predeterminado_id'] = 20;
save_card( 2, true, array( 'tarjetas_logo_id' => 10 ) );
check( 10, tarjetas_logo_inicial_id( 2 ), 'El guardado posterior no altera el logo inicial' );
check( 'https://example.test/logo-10.png', tarjetas_logo_tarjeta_url( 2 ), 'El borrador abierto conserva su selección' );
save_card( 3, false );
check( 'https://example.test/logo-20.png', tarjetas_logo_tarjeta_url( 3 ), 'La tercera tarjeta recibe el nuevo ajuste' );

save_card( 2, true, array( 'tarjetas_logo_id' => 30 ) );
check( 'https://example.test/logo-30.png', tarjetas_logo_tarjeta_url( 2 ), 'Selección manual independiente' );
check( 'https://example.test/logo-20.png', tarjetas_logo_tarjeta_url( 3 ), 'Otra tarjeta no cambia' );
save_card( 2, true );
check( 'https://example.test/logo-30.png', tarjetas_logo_tarjeta_url( 2 ), 'Un campo ausente no borra el logo' );
save_card( 2, true, array( 'tarjetas_logo_id' => 40 ) );
check( 'https://example.test/logo-30.png', tarjetas_logo_tarjeta_url( 2 ), 'Un adjunto no imagen no reemplaza el logo' );
save_card( 2, true, array( 'tarjetas_logo_id' => array( 20 ) ) );
check( 'https://example.test/logo-30.png', tarjetas_logo_tarjeta_url( 2 ), 'Un valor malformado no reemplaza el logo' );
save_card( 2, true, array( 'tarjetas_logo_id' => 0 ) );
check( 'https://example.test/logo-10.png', tarjetas_logo_tarjeta_url( 2 ), 'Restaurar no utiliza el ajuste global vigente' );

// Tarjetas creadas antes de incorporar la configuración.
$meta[4]['_tarjetas_logo_id'] = 0;
$meta[5]['_tarjetas_logo_id'] = 30;
check( $builtin, tarjetas_logo_tarjeta_url( 4 ), 'La tarjeta antigua sin logo sigue con McGregor' );
check( 'https://example.test/logo-30.png', tarjetas_logo_tarjeta_url( 5 ), 'La tarjeta antigua personalizada conserva su logo' );
save_card( 4, true, array( 'tarjetas_logo_id' => 20 ) );
check( 0, tarjetas_logo_inicial_id( 4 ), 'La tarjeta antigua captura su McGregor original al editar' );
save_card( 4, true, array( 'tarjetas_logo_id' => 0 ) );
check( $builtin, tarjetas_logo_tarjeta_url( 4 ), 'La tarjeta antigua restaura McGregor' );
save_card( 5, true, array( 'tarjetas_logo_id' => 20 ) );
check( 30, tarjetas_logo_inicial_id( 5 ), 'Se conserva el logo inicial personalizado de la tarjeta antigua' );
save_card( 5, true, array( 'tarjetas_logo_id' => 0 ) );
check( 'https://example.test/logo-30.png', tarjetas_logo_tarjeta_url( 5 ), 'La tarjeta antigua restaura su logo personalizado' );

save_card( 6, false, array( 'tarjetas_logo_id' => 30 ) );
check( 20, tarjetas_logo_inicial_id( 6 ), 'La creación manual también conserva la selección inicial' );
check( 'https://example.test/logo-30.png', tarjetas_logo_tarjeta_url( 6 ), 'Selección manual en el primer guardado' );

check( 10, tarjetas_validar_logo_configurado( '10' ), 'Ajuste válido' );
check( 0, tarjetas_validar_logo_configurado( '0' ), 'Restaurar McGregor en ajustes' );
check( 20, tarjetas_validar_logo_configurado( '40' ), 'PDF rechazado sin perder el ajuste previo' );
check( 20, tarjetas_validar_logo_configurado( array( 10 ) ), 'Ajuste malformado rechazado' );
check( 20, tarjetas_validar_logo_configurado( '-10' ), 'ID negativo rechazado' );
check( 3, count( $settings_errors ), 'Los errores de ajustes son visibles' );

check( true, false !== strpos( tarjetas_generar_vcard_qr( 2 ), 'LOGO;VALUE=URI:https://example.test/logo-10.png' ), 'El QR usa el logo propio, no el ajuste global' );
check( true, false !== strpos( tarjetas_generar_vcard_qr( 1 ), 'LOGO;VALUE=URI:' . $builtin ), 'El QR conserva McGregor' );

$options['tarjetas_logo_predeterminado_id'] = 40;
save_card( 7, false );
check( $builtin, tarjetas_logo_tarjeta_url( 7 ), 'Un ajuste cuyo adjunto ya no es válido usa McGregor para tarjetas nuevas' );
check( 'https://example.test/logo-20.png', tarjetas_logo_tarjeta_url( 3 ), 'Un ajuste inválido no cambia las tarjetas existentes' );

// La carga de assets utiliza el hook real devuelto por WordPress, no un nombre supuesto.
tarjetas_registrar_pagina_ajustes();
tarjetas_cargar_selector_logo( 'tarjetas_page_tarjetas-ajustes' );
check( array( 'media', 'tarjetas-selector-logo' ), $enqueued, 'El selector se carga en el submenú de ajustes' );
$enqueued = array();
tarjetas_cargar_selector_logo( 'index.php' );
check( array(), $enqueued, 'No se cargan assets en páginas ajenas' );
tarjetas_cargar_selector_logo( 'post-new.php' );
check( array( 'media', 'tarjetas-selector-logo' ), $enqueued, 'El selector se carga en el editor' );

// La inicialización no depende del nonce del editor.
$_POST = array();
tarjetas_fijar_logo_inicial( 8, (object) array( 'post_type' => 'tarjeta' ), false );
check( true, metadata_exists( 'post', 8, '_tarjetas_logo_inicial_id' ), 'Creación automática sin formulario' );
tarjetas_fijar_logo_inicial( 999, (object) array( 'post_type' => 'tarjeta' ), false );
check( false, metadata_exists( 'post', 999, '_tarjetas_logo_inicial_id' ), 'Se omiten las revisiones' );
tarjetas_fijar_logo_inicial( 9, (object) array( 'post_type' => 'post' ), false );
check( false, metadata_exists( 'post', 9, '_tarjetas_logo_inicial_id' ), 'Se omiten otros tipos de contenido' );

echo $checks . " comprobaciones correctas.\n";
