<?php
/**
 * Ajustes del logo y selección independiente por tarjeta.
 *
 * @package Tarjetas
 */

defined( 'ABSPATH' ) || exit;

/**
 * Devuelve el adjunto configurado para las tarjetas nuevas (0 = McGregor).
 *
 * @return int
 */
function tarjetas_logo_configurado_id() {
	$id = absint( get_option( 'tarjetas_logo_predeterminado_id', 0 ) );
	return tarjetas_es_imagen_adjunta( $id ) ? $id : 0;
}

/**
 * Devuelve la URL de un logo; el valor 0 identifica el archivo incluido.
 *
 * @param int $id ID del adjunto.
 * @return string
 */
function tarjetas_logo_url_por_id( $id ) {
	return $id ? (string) wp_get_attachment_image_url( $id, 'medium' ) : tarjetas_logo_predeterminado_url();
}

/**
 * Las tarjetas anteriores conservan su selección sin consultar el ajuste global.
 *
 * @param int $post_id ID de la tarjeta.
 * @return int
 */
function tarjetas_logo_inicial_id( $post_id ) {
	if ( metadata_exists( 'post', $post_id, '_tarjetas_logo_inicial_id' ) ) {
		return absint( get_post_meta( $post_id, '_tarjetas_logo_inicial_id', true ) );
	}

	return absint( tarjetas_obtener_meta( $post_id, 'logo_id' ) );
}

/**
 * Obtiene el logo propio de una tarjeta, nunca el ajuste global vigente.
 *
 * @param int $post_id ID de la tarjeta.
 * @return string
 */
function tarjetas_logo_tarjeta_url( $post_id ) {
	$id = absint( tarjetas_obtener_meta( $post_id, 'logo_id' ) );
	return tarjetas_logo_url_por_id( $id ? $id : tarjetas_logo_inicial_id( $post_id ) );
}

/**
 * Fija el logo antes del guardado del formulario, también en auto-draft.
 *
 * @param int     $post_id ID de la tarjeta.
 * @param WP_Post $post    Post guardado.
 * @param bool    $update  Indica si ya existía.
 * @return void
 */
function tarjetas_fijar_logo_inicial( $post_id, $post, $update ) {
	if ( 'tarjeta' !== $post->post_type || wp_is_post_revision( $post_id ) || metadata_exists( 'post', $post_id, '_tarjetas_logo_inicial_id' ) ) {
		return;
	}

	// Al editar tarjetas antiguas, preservar el logo anterior en lugar del ajuste nuevo.
	$id = $update ? absint( tarjetas_obtener_meta( $post_id, 'logo_id' ) ) : tarjetas_logo_configurado_id();
	add_post_meta( $post_id, '_tarjetas_logo_inicial_id', $id, true );

	if ( ! $update ) {
		add_post_meta( $post_id, '_tarjetas_logo_id', $id, true );
	}
}
add_action( 'save_post_tarjeta', 'tarjetas_fijar_logo_inicial', 5, 3 );

/**
 * Registra el submenú de ajustes.
 *
 * @return void
 */
function tarjetas_registrar_pagina_ajustes() {
	global $tarjetas_ajustes_hook;

	$tarjetas_ajustes_hook = add_submenu_page(
		'edit.php?post_type=tarjeta',
		__( 'Ajustes de tarjetas', 'tarjetas' ),
		__( 'Ajustes', 'tarjetas' ),
		'manage_options',
		'tarjetas-ajustes',
		'tarjetas_renderizar_ajustes'
	);
}
add_action( 'admin_menu', 'tarjetas_registrar_pagina_ajustes' );

/**
 * Valida la selección, conservando el ajuste anterior ante una imagen inválida.
 *
 * @param mixed $value Valor recibido.
 * @return int
 */
function tarjetas_validar_logo_configurado( $value ) {
	if ( is_scalar( $value ) && preg_match( '/^\d+$/', (string) $value ) ) {
		$id = absint( $value );
		if ( 0 === $id || tarjetas_es_imagen_adjunta( $id ) ) {
			return $id;
		}
	}

	add_settings_error( 'tarjetas_logo_predeterminado_id', 'tarjetas_logo_invalido', __( 'Selecciona una imagen válida de la biblioteca de medios. Se ha conservado el logo anterior.', 'tarjetas' ) );
	return tarjetas_logo_configurado_id();
}

/**
 * Registra el ajuste con la API nativa de WordPress.
 *
 * @return void
 */
function tarjetas_registrar_ajustes() {
	register_setting(
		'tarjetas_ajustes',
		'tarjetas_logo_predeterminado_id',
		array(
			'type'              => 'integer',
			'sanitize_callback' => 'tarjetas_validar_logo_configurado',
			'default'           => 0,
		)
	);
}
add_action( 'admin_init', 'tarjetas_registrar_ajustes' );

/**
 * Renderiza los ajustes con controles nativos del administrador.
 *
 * @return void
 */
function tarjetas_renderizar_ajustes() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$id = tarjetas_logo_configurado_id();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Ajustes de tarjetas', 'tarjetas' ); ?></h1>
		<?php settings_errors(); ?>
		<form action="options.php" method="post">
			<?php settings_fields( 'tarjetas_ajustes' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="tarjetas_ajustes_seleccionar_logo"><?php esc_html_e( 'Logo', 'tarjetas' ); ?></label></th>
					<td data-tarjetas-logo-selector data-initial-id="0" data-initial-url="<?php echo esc_url( tarjetas_logo_predeterminado_url() ); ?>">
						<input type="hidden" name="tarjetas_logo_predeterminado_id" value="<?php echo esc_attr( $id ); ?>" data-logo-input />
						<p><img src="<?php echo esc_url( tarjetas_logo_url_por_id( $id ) ); ?>" alt="<?php esc_attr_e( 'Logo seleccionado', 'tarjetas' ); ?>" style="max-width: 400px; width: 100%; height: auto;" data-logo-preview /></p>
						<button type="button" class="button button-secondary" id="tarjetas_ajustes_seleccionar_logo" data-logo-select aria-describedby="tarjetas_logo_ayuda"><?php esc_html_e( 'Seleccionar imagen', 'tarjetas' ); ?></button>

						<p class="description" id="tarjetas_logo_ayuda"><?php esc_html_e( 'Este logo se aplicará únicamente a las nuevas tarjetas. Las tarjetas existentes conservarán su logo.', 'tarjetas' ); ?></p>
						<p class="description" data-logo-status role="status" aria-live="polite"></p>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/**
 * Carga el selector únicamente en ajustes y en el editor de tarjetas.
 *
 * @param string $hook Página administrativa.
 * @return void
 */
function tarjetas_cargar_selector_logo( $hook ) {
	global $tarjetas_ajustes_hook;

	$screen = get_current_screen();
	$is_settings = $tarjetas_ajustes_hook && $tarjetas_ajustes_hook === $hook;
	$is_editor = in_array( $hook, array( 'post.php', 'post-new.php' ), true ) && $screen && 'tarjeta' === $screen->post_type;
	if ( ! $is_settings && ! $is_editor ) {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_script( 'tarjetas-selector-logo', plugin_dir_url( __FILE__ ) . 'assets/js/logo-selector.js', array( 'jquery', 'media-views' ), '1.0.0', true );
	wp_localize_script(
		'tarjetas-selector-logo',
		'TarjetasLogo',
		array(
			'title'    => __( 'Seleccionar logo', 'tarjetas' ),
			'button'   => __( 'Usar logo', 'tarjetas' ),
			'alt'      => __( 'Logo seleccionado', 'tarjetas' ),
			'changed'  => __( 'Logo seleccionado. Guarda los cambios para aplicarlo.', 'tarjetas' ),
			'restored' => __( 'Logo restaurado. Guarda los cambios para aplicarlo.', 'tarjetas' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'tarjetas_cargar_selector_logo' );
