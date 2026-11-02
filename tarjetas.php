<?php
/**
 * Plugin Name: Tarjetas
 * Description: Añade el tipo de contenido personalizado "Tarjeta" y su editor de datos de contacto.
 * Version: 1.3.2
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Guegue Tarjeta
 * Text Domain: tarjetas
 *
 * @package Tarjetas
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/logo-settings.php';

/**
 * Registra el tipo de contenido Tarjeta.
 *
 * @return void
 */
function tarjetas_registrar_tipo_de_contenido() {
	$labels = array(
		'name'                  => __( 'Tarjetas', 'tarjetas' ),
		'singular_name'         => __( 'Tarjeta', 'tarjetas' ),
		'menu_name'             => __( 'Tarjetas', 'tarjetas' ),
		'name_admin_bar'        => __( 'Tarjeta', 'tarjetas' ),
		'add_new'               => __( 'Añadir nueva', 'tarjetas' ),
		'add_new_item'          => __( 'Añadir nueva tarjeta', 'tarjetas' ),
		'new_item'              => __( 'Nueva tarjeta', 'tarjetas' ),
		'edit_item'             => __( 'Editar tarjeta', 'tarjetas' ),
		'view_item'             => __( 'Ver tarjeta', 'tarjetas' ),
		'all_items'             => __( 'Todas las tarjetas', 'tarjetas' ),
		'search_items'          => __( 'Buscar tarjetas', 'tarjetas' ),
		'not_found'             => __( 'No se encontraron tarjetas.', 'tarjetas' ),
		'not_found_in_trash'    => __( 'No hay tarjetas en la papelera.', 'tarjetas' ),
		'featured_image'        => __( 'Fotografía del colaborador', 'tarjetas' ),
		'set_featured_image'    => __( 'Seleccionar fotografía', 'tarjetas' ),
		'remove_featured_image' => __( 'Quitar fotografía', 'tarjetas' ),
		'use_featured_image'    => __( 'Usar esta fotografía', 'tarjetas' ),
	);

	register_post_type(
		'tarjeta',
		array(
			'labels'             => $labels,
			'public'             => true,
			'show_in_rest'       => false,
			'has_archive'        => true,
			'rewrite'            => array( 'slug' => 'tarjetas' ),
			'menu_icon'          => 'dashicons-id-alt',
			'capabilities'       => array(
				'create_posts' => 'create_tarjetas',
			),
			'map_meta_cap'       => true,
			'supports'           => array(),
		)
	);

	remove_post_type_support( 'tarjeta', 'title' );
	remove_post_type_support( 'tarjeta', 'editor' );
}
add_action( 'init', 'tarjetas_registrar_tipo_de_contenido' );

/**
 * Concede la creación de tarjetas exclusivamente a los administradores.
 *
 * @return void
 */
function tarjetas_conceder_permiso_de_creacion_a_administradores() {
	$administrator = get_role( 'administrator' );

	if ( $administrator ) {
		$administrator->add_cap( 'create_tarjetas' );
	}
}
add_action( 'admin_init', 'tarjetas_conceder_permiso_de_creacion_a_administradores' );

/**
 * Registra las divisiones o departamentos administrables.
 *
 * @return void
 */
function tarjetas_registrar_taxonomia_departamento(): void {
	register_taxonomy(
		'tarjeta_departamento',
		array( 'tarjeta' ),
		array(
			'labels'            => array(
				'name'          => __( 'Divisiones / Departamentos', 'tarjetas' ),
				'singular_name' => __( 'División / Departamento', 'tarjetas' ),
				'menu_name'     => __( 'Divisiones / Departamentos', 'tarjetas' ),
				'add_new_item'  => __( 'Añadir división o departamento', 'tarjetas' ),
				'edit_item'     => __( 'Editar división o departamento', 'tarjetas' ),
			),
			'hierarchical'      => true,
			'public'            => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'meta_box_cb'       => false,
			'rewrite'           => false,
		)
	);
}
add_action( 'init', 'tarjetas_registrar_taxonomia_departamento' );

/**
 * Muestra el formulario propio de tarjetas sin el lienzo de Gutenberg.
 *
 * @param bool   $use_block_editor Indica si se usa Gutenberg.
 * @param string $post_type        Tipo de contenido.
 * @return bool
 */
function tarjetas_usar_formulario_personalizado( $use_block_editor, $post_type ) {
	if ( 'tarjeta' === $post_type ) {
		return false;
	}

	return $use_block_editor;
}
add_filter( 'use_block_editor_for_post_type', 'tarjetas_usar_formulario_personalizado', 10, 2 );

/**
 * Impide que las tarjetas incorporen bloques de Gutenberg.
 *
 * Las tarjetas se completan exclusivamente mediante el formulario personalizado.
 *
 * @param bool|string[]            $allowed_block_types Bloques permitidos.
 * @param WP_Block_Editor_Context  $editor_context      Contexto del editor.
 * @return bool|string[]
 */
function tarjetas_bloques_permitidos( $allowed_block_types, $editor_context ) {
	if ( isset( $editor_context->post ) && $editor_context->post instanceof WP_Post && 'tarjeta' === $editor_context->post->post_type ) {
		return false;
	}

	return $allowed_block_types;
}
add_filter( 'allowed_block_types_all', 'tarjetas_bloques_permitidos', 10, 2 );

/**
 * Mantiene vacío el contenido de las tarjetas si una petición intenta escribir bloques.
 *
 * @param array $data    Datos preparados para la base de datos.
 * @param array $postarr Datos originales del post.
 * @return array
 */
function tarjetas_eliminar_contenido_de_bloques( $data, $postarr ) {
	if ( isset( $data['post_type'] ) && 'tarjeta' === $data['post_type'] ) {
		$data['post_content'] = '';
	}

	return $data;
}
add_filter( 'wp_insert_post_data', 'tarjetas_eliminar_contenido_de_bloques', 10, 2 );

/**
 * Oculta una tarjeta publicada devolviéndola al estado de borrador.
 *
 * @param array $data    Datos preparados para la base de datos.
 * @param array $postarr Datos originales del post.
 * @return array
 */
function tarjetas_cambiar_a_borrador( $data, $postarr ) {
	if (
		isset( $data['post_type'], $_POST['tarjetas_ocultar'], $_POST['tarjetas_datos_nonce'] ) &&
		'tarjeta' === $data['post_type'] &&
		wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tarjetas_datos_nonce'] ) ), 'tarjetas_guardar_datos' )
	) {
		$post_id = isset( $postarr['ID'] ) ? absint( $postarr['ID'] ) : 0;
		if ( $post_id && current_user_can( 'edit_post', $post_id ) ) {
			$data['post_status'] = 'draft';
		}
	}

	return $data;
}
add_filter( 'wp_insert_post_data', 'tarjetas_cambiar_a_borrador', 15, 2 );

/**
 * Añade el editor de datos de tarjeta.
 *
 * @return void
 */
function tarjetas_registrar_metabox() {
	add_meta_box(
		'tarjetas_datos_colaborador',
		__( 'Datos de la tarjeta', 'tarjetas' ),
		'tarjetas_renderizar_metabox',
		'tarjeta',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes_tarjeta', 'tarjetas_registrar_metabox' );

/**
 * Elimina los paneles nativos para que la pantalla contenga solo el formulario.
 *
 * @return void
 */
function tarjetas_quitar_metaboxes_nativos() {
	remove_meta_box( 'submitdiv', 'tarjeta', 'side' );
	remove_meta_box( 'tarjeta_departamentodiv', 'tarjeta', 'side' );
}
add_action( 'add_meta_boxes_tarjeta', 'tarjetas_quitar_metaboxes_nativos', 99 );

/**
 * Devuelve un meta valor de tarjeta.
 *
 * @param int    $post_id ID del post.
 * @param string $key     Clave sin prefijo.
 * @return string
 */
function tarjetas_obtener_meta( $post_id, $key ) {
	return (string) get_post_meta( $post_id, '_tarjetas_' . $key, true );
}

/**
 * Devuelve la URL del logo corporativo predeterminado.
 *
 * @return string
 */
function tarjetas_logo_predeterminado_url() {
	return plugin_dir_url( __FILE__ ) . 'assets/mc_gregor.png';
}

/**
 * Obtiene las redes sociales, conservando los datos guardados con versiones anteriores.
 *
 * @param int $post_id ID de la tarjeta.
 * @return array<int, array{label: string, url: string, icon: string}>
 */
function tarjetas_obtener_sociales( $post_id, $incluir_vacias = false ) {
	$predeterminadas = array(
		array( 'label' => 'LinkedIn', 'url' => tarjetas_obtener_meta( $post_id, 'linkedin' ), 'icon' => 'fa-brands fa-linkedin-in' ),
		array( 'label' => 'Instagram', 'url' => tarjetas_obtener_meta( $post_id, 'instagram' ), 'icon' => 'fa-brands fa-instagram' ),
		array( 'label' => 'Facebook', 'url' => tarjetas_obtener_meta( $post_id, 'facebook' ), 'icon' => 'fa-brands fa-facebook-f' ),
		array( 'label' => 'Sitio web', 'url' => tarjetas_obtener_meta( $post_id, 'sitio_web' ), 'icon' => 'fa-solid fa-globe' ),
	);
	$guardadas = get_post_meta( $post_id, '_tarjetas_sociales', true );
	$sociales  = $predeterminadas;

	if ( is_array( $guardadas ) ) {
		foreach ( $guardadas as $social ) {
			if ( ! is_array( $social ) ) {
				continue;
			}

			$indice = array_search( isset( $social['label'] ) ? $social['label'] : '', wp_list_pluck( $predeterminadas, 'label' ), true );
			if ( false !== $indice ) {
				$sociales[ $indice ]['url'] = isset( $social['url'] ) ? $social['url'] : '';
			} else {
				$sociales[] = $social;
			}
		}
	}

	$sociales = array_map(
		static function ( $social ) {
			if ( ! is_array( $social ) ) {
				return null;
			}

			return array(
				'label' => isset( $social['label'] ) ? sanitize_text_field( $social['label'] ) : __( 'Enlace', 'tarjetas' ),
				'url'   => isset( $social['url'] ) ? esc_url_raw( $social['url'] ) : '',
				'icon'  => isset( $social['icon'] ) && preg_match( '/^[a-z0-9 -]+$/i', $social['icon'] ) ? $social['icon'] : 'fa-solid fa-globe',
			);
		},
		$sociales
	);

	if ( ! $incluir_vacias ) {
		$sociales = array_filter( $sociales, static function ( $social ) {
			return $social && $social['url'];
		} );
	}

	return array_values( array_filter( $sociales ) );
}

/**
 * Renderiza un campo de texto.
 *
 * @param string $id          ID del campo.
 * @param string $label       Etiqueta visible.
 * @param string $value       Valor actual.
 * @param string $type        Tipo HTML.
 * @param string $description Descripción opcional.
 * @param bool   $required    Indica si el campo es obligatorio.
 * @return void
 */
function tarjetas_campo( $id, $label, $value, $type = 'text', $description = '', $required = false ) {
	$name = preg_replace( '/^tarjetas_sociales_(\d+)_(label|url|icon)$/', 'tarjetas_sociales[$1][$2]', $id );
	?>
	<div class="tarjetas-field">
		<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?><?php if ( $required ) : ?> <span class="tarjetas-required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( '(obligatorio)', 'tarjetas' ); ?></span><?php endif; ?></label>
		<input class="regular-text" type="<?php echo esc_attr( $type ); ?>" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" autocomplete="off" <?php echo $required ? 'required' : ''; ?> <?php echo 'tel' === $type ? 'inputmode="tel" maxlength="17" pattern="\\+[0-9]{1,3} [0-9]{4} [0-9]{4}" placeholder="+505 1234 5678" title="' . esc_attr__( 'Indica el código de país y ocho números; por ejemplo, +505 1234 5678.', 'tarjetas' ) . '"' : ''; ?> />
		<?php if ( $description ) : ?>
			<p class="description"><?php echo esc_html( $description ); ?></p>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Renderiza el formulario y la vista de datos en el teléfono.
 *
 * @param WP_Post $post Post actual.
 * @return void
 */
function tarjetas_renderizar_metabox( $post ) {
	$nombre       = tarjetas_obtener_meta( $post->ID, 'nombre' );
	$cargo        = tarjetas_obtener_meta( $post->ID, 'cargo' );
	$empresa      = tarjetas_obtener_meta( $post->ID, 'empresa' );
	$telefono     = tarjetas_formatear_telefono( tarjetas_obtener_meta( $post->ID, 'telefono' ) );
	$whatsapp     = tarjetas_formatear_telefono( tarjetas_obtener_meta( $post->ID, 'whatsapp' ) );
	$correo       = tarjetas_obtener_meta( $post->ID, 'correo' );
	$ubicacion    = tarjetas_obtener_meta( $post->ID, 'ubicacion' );
	$linkedin     = tarjetas_obtener_meta( $post->ID, 'linkedin' );
	$instagram    = tarjetas_obtener_meta( $post->ID, 'instagram' );
	$facebook     = tarjetas_obtener_meta( $post->ID, 'facebook' );
	$sitio_web    = tarjetas_obtener_meta( $post->ID, 'sitio_web' );
	$color        = sanitize_hex_color( tarjetas_obtener_meta( $post->ID, 'color' ) );
	$color        = $color ? $color : '#087d0a';
	$foto_id      = absint( tarjetas_obtener_meta( $post->ID, 'foto_id' ) );
	$foto_url     = $foto_id ? wp_get_attachment_image_url( $foto_id, 'medium' ) : '';
	$logo_id      = absint( tarjetas_obtener_meta( $post->ID, 'logo_id' ) );
	$logo_url     = tarjetas_logo_tarjeta_url( $post->ID );
	$logo_inicial_id = tarjetas_logo_inicial_id( $post->ID );
	$sociales     = tarjetas_obtener_sociales( $post->ID, true );
	$departamento = wp_get_post_terms( $post->ID, 'tarjeta_departamento', array( 'fields' => 'ids' ) );
	$departamento = ! empty( $departamento ) ? (int) $departamento[0] : 0;
	$estado_qr    = 'auto-draft' === $post->post_status ? '' : get_permalink( $post );
	?>
	<?php wp_nonce_field( 'tarjetas_guardar_datos', 'tarjetas_datos_nonce' ); ?>
	<div class="tarjetas-editor" style="--tarjetas-green: <?php echo esc_attr( $color ); ?>; --tarjetas-green-dark: <?php echo esc_attr( $color ); ?>;">
		<div class="tarjetas-editor__form">
			<div class="tarjetas-save-actions">
				<button type="submit" class="button button-primary button-hero" name="publish" value="<?php echo esc_attr__( 'Publicar tarjeta', 'tarjetas' ); ?>"><?php esc_html_e( 'Publicar tarjeta', 'tarjetas' ); ?></button>
				<?php if ( 'publish' === $post->post_status ) : ?>
					<button type="submit" class="button button-secondary button-hero tarjetas-hide-button" name="tarjetas_ocultar" value="1" formnovalidate><?php esc_html_e( 'Ocultar', 'tarjetas' ); ?></button>
				<?php endif; ?>
				<p><?php esc_html_e( 'Completa los campos obligatorios para guardar y publicar esta tarjeta.', 'tarjetas' ); ?></p>
			</div>
			<section class="tarjetas-section tarjetas-appearance">
				<h2><?php esc_html_e( 'Apariencia', 'tarjetas' ); ?></h2>
				<div class="tarjetas-field">
					<label for="tarjetas_color"><?php esc_html_e( 'Color principal', 'tarjetas' ); ?></label>
					<input type="text" id="tarjetas_color" name="tarjetas_color" value="<?php echo esc_attr( $color ); ?>" class="tarjetas-color-field" data-default-color="#087d0a" />
					<p class="description"><?php esc_html_e( 'Se aplica a la cabecera, los iconos y el botón de la tarjeta.', 'tarjetas' ); ?></p>
				</div>
			</section>
			<section class="tarjetas-section">
				<h2><?php esc_html_e( 'Información del colaborador', 'tarjetas' ); ?></h2>
				<div class="tarjetas-photo-field">
					<label><?php esc_html_e( 'Fotografía del colaborador', 'tarjetas' ); ?> <span class="tarjetas-required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( '(obligatorio)', 'tarjetas' ); ?></span></label>
					<input type="hidden" id="tarjetas_foto_id" name="tarjetas_foto_id" value="<?php echo esc_attr( $foto_id ); ?>" />
					<p class="tarjetas-field-error is-hidden" id="tarjetas_foto_error" role="alert"><?php esc_html_e( 'Selecciona una fotografía del colaborador.', 'tarjetas' ); ?></p>
					<div class="tarjetas-photo-field__controls">
						<div class="tarjetas-photo-field__preview <?php echo $foto_url ? '' : 'is-empty'; ?>" id="tarjetas_foto_preview">
							<?php if ( $foto_url ) : ?>
								<img src="<?php echo esc_url( $foto_url ); ?>" alt="" />
							<?php else : ?>
								<span class="dashicons dashicons-format-image" aria-hidden="true"></span>
							<?php endif; ?>
						</div>
						<div>
							<button type="button" class="button button-secondary" id="tarjetas_seleccionar_foto"><?php esc_html_e( 'Seleccionar fotografía', 'tarjetas' ); ?></button>
							<button type="button" class="button-link-delete <?php echo $foto_url ? '' : 'is-hidden'; ?>" id="tarjetas_quitar_foto"><?php esc_html_e( 'Quitar', 'tarjetas' ); ?></button>
							<p class="description"><?php esc_html_e( 'Usa una imagen clara y reciente del colaborador.', 'tarjetas' ); ?></p>
						</div>
					</div>
				</div>
				<div class="tarjetas-logo-field" data-tarjetas-logo-selector data-initial-id="<?php echo esc_attr( $logo_inicial_id ); ?>" data-initial-url="<?php echo esc_url( tarjetas_logo_url_por_id( $logo_inicial_id ) ); ?>" data-secondary-preview="#tarjetas_phone_logo">
					<label><?php esc_html_e( 'Logo de la empresa', 'tarjetas' ); ?></label>
					<input type="hidden" id="tarjetas_logo_id" name="tarjetas_logo_id" value="<?php echo esc_attr( $logo_id ); ?>" data-logo-input />

					<div class="tarjetas-logo-field__controls">
						<div class="tarjetas-logo-field__preview <?php echo $logo_url ? '' : 'is-empty'; ?>" id="tarjetas_logo_preview">
							<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php esc_attr_e( 'Logo seleccionado', 'tarjetas' ); ?>" data-logo-preview <?php echo $logo_url ? '' : 'hidden'; ?> />
						</div>
						<div>
							<button type="button" class="button button-secondary" id="tarjetas_elegir_logo" data-logo-select><?php esc_html_e( 'Seleccionar logo', 'tarjetas' ); ?></button>
							<button type="button" class="button button-secondary" data-logo-reset <?php disabled( $logo_id ? $logo_id : $logo_inicial_id, $logo_inicial_id ); ?>><?php esc_html_e( 'Restaurar logo inicial', 'tarjetas' ); ?></button>
							<p class="description"><?php esc_html_e( 'Este logo pertenece a esta tarjeta. Puedes cambiarlo sin afectar a las demás; los ajustes globales no lo modificarán.', 'tarjetas' ); ?></p>
							<p class="description" data-logo-status role="status" aria-live="polite"></p>
						</div>
					</div>
				</div>
				<div class="tarjetas-fields-grid">
					<?php tarjetas_campo( 'tarjetas_nombre', __( 'Nombre completo', 'tarjetas' ), $nombre, 'text', '', true ); ?>
					<?php tarjetas_campo( 'tarjetas_cargo', __( 'Cargo', 'tarjetas' ), $cargo, 'text', '', true ); ?>
					<?php tarjetas_campo( 'tarjetas_empresa', __( 'Empresa', 'tarjetas' ), $empresa ); ?>
				</div>
				<div class="tarjetas-field">
					<label for="tarjetas_departamento"><?php esc_html_e( 'División / Departamento', 'tarjetas' ); ?> <span class="tarjetas-required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( '(obligatorio)', 'tarjetas' ); ?></span></label>
					<?php
					wp_dropdown_categories(
						array(
							'taxonomy'         => 'tarjeta_departamento',
							'hide_empty'       => false,
							'name'             => 'tarjetas_departamento',
							'id'               => 'tarjetas_departamento',
							'selected'         => $departamento,
							'show_option_none' => __( 'Selecciona una división o departamento', 'tarjetas' ),
							'option_none_value'=> '0',
							'required'         => true,
						)
					);
					?>
					<p class="description"><a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=tarjeta_departamento&post_type=tarjeta' ) ); ?>"><?php esc_html_e( 'Gestionar divisiones y departamentos', 'tarjetas' ); ?></a></p>
				</div>
			</section>

			<section class="tarjetas-section">
				<h2><?php esc_html_e( 'Información de contacto', 'tarjetas' ); ?></h2>
				<div class="tarjetas-fields-grid">
					<?php tarjetas_campo( 'tarjetas_telefono', __( 'Teléfono', 'tarjetas' ), $telefono, 'tel', __( 'Ejemplo: +505 1234 5678.', 'tarjetas' ), true ); ?>
					<?php tarjetas_campo( 'tarjetas_whatsapp', __( 'WhatsApp', 'tarjetas' ), $whatsapp, 'tel', __( 'Ejemplo: +505 1234 5678.', 'tarjetas' ), true ); ?>
				</div>
				<?php tarjetas_campo( 'tarjetas_correo', __( 'Correo electrónico', 'tarjetas' ), $correo, 'email', '', true ); ?>
				<?php tarjetas_campo( 'tarjetas_ubicacion', __( 'Ubicación', 'tarjetas' ), $ubicacion, 'text', __( 'Por ejemplo: Ciudad de Panamá, Panamá.', 'tarjetas' ), true ); ?>
			</section>

			<section class="tarjetas-section">
				<h2><?php esc_html_e( 'Redes sociales', 'tarjetas' ); ?></h2>
				<p class="tarjetas-section__intro"><?php esc_html_e( 'Añade solo los perfiles que desees mostrar.', 'tarjetas' ); ?></p>
				<div id="tarjetas_sociales_fields" class="tarjetas-sociales-fields">
					<?php foreach ( $sociales as $index => $social ) : ?>
						<div class="tarjetas-social-row <?php echo $index < 4 ? 'tarjetas-social-row--fixed' : ''; ?>">
							<?php if ( $index < 4 ) : ?>

								<?php tarjetas_campo( 'tarjetas_sociales_' . $index . '_url', $social['label'], $social['url'], 'url' ); ?>
								<input type="hidden" name="tarjetas_sociales[<?php echo esc_attr( $index ); ?>][label]" value="<?php echo esc_attr( $social['label'] ); ?>" />
							<?php else : ?>
								<?php tarjetas_campo( 'tarjetas_sociales_' . $index . '_label', __( 'Nombre', 'tarjetas' ), $social['label'], 'text' ); ?>
								<?php tarjetas_campo( 'tarjetas_sociales_' . $index . '_url', __( 'Enlace', 'tarjetas' ), $social['url'], 'url' ); ?>
								<button type="button" class="button-link-delete tarjetas-remove-social"><?php esc_html_e( 'Quitar', 'tarjetas' ); ?></button>
							<?php endif; ?>
							<input type="hidden" name="tarjetas_sociales[<?php echo esc_attr( $index ); ?>][icon]" value="<?php echo esc_attr( $social['icon'] ); ?>" />
						</div>
					<?php endforeach; ?>
				</div>
				<button type="button" class="button button-secondary" id="tarjetas_agregar_social"><?php esc_html_e( 'Añadir red social', 'tarjetas' ); ?></button>
			</section>
			<div class="tarjetas-save-actions tarjetas-save-actions--bottom">
				<?php if ( 'publish' === $post->post_status ) : ?>
					<button type="submit" class="button button-secondary button-hero tarjetas-hide-button" name="tarjetas_ocultar" value="1" formnovalidate><?php esc_html_e( 'Ocultar', 'tarjetas' ); ?></button>
				<?php endif; ?>
				<button type="submit" class="button button-primary button-hero" name="publish" value="<?php echo esc_attr__( 'Publicar tarjeta', 'tarjetas' ); ?>"><?php esc_html_e( 'Publicar tarjeta', 'tarjetas' ); ?></button>
			</div>
		</div>

		<aside class="tarjetas-editor__preview" aria-label="<?php esc_attr_e( 'Vista previa de datos', 'tarjetas' ); ?>">
			<div class="tarjetas-phone">
				<div class="tarjetas-phone__speaker" aria-hidden="true"></div>
				<div class="tarjetas-phone__screen">
					<div class="tarjetas-phone__photo <?php echo $foto_url ? '' : 'is-empty'; ?>" id="tarjetas_phone_photo">
						<?php if ( $foto_url ) : ?>
							<img src="<?php echo esc_url( $foto_url ); ?>" alt="" />
						<?php else : ?>
							<span class="dashicons dashicons-id-alt" aria-hidden="true"></span>
						<?php endif; ?>
					</div>
					<div class="tarjetas-phone__content">
						<div class="tarjetas-phone__identity">
							<div class="tarjetas-phone__identity-copy">
								<p class="tarjetas-phone__name" id="tarjetas_preview_nombre"><?php echo esc_html( $nombre ? $nombre : __( 'Nombre del colaborador', 'tarjetas' ) ); ?></p>
								<p class="tarjetas-phone__position" id="tarjetas_preview_cargo"><?php echo esc_html( $cargo ? $cargo : __( 'Cargo', 'tarjetas' ) ); ?></p>
								<p class="tarjetas-phone__department" id="tarjetas_preview_departamento"><?php echo esc_html( $departamento ? get_term( $departamento )->name : __( 'División o departamento', 'tarjetas' ) ); ?></p>
								<p class="tarjetas-phone__company" id="tarjetas_preview_empresa"><?php echo esc_html( $empresa ? $empresa : __( 'Empresa', 'tarjetas' ) ); ?></p>
							</div>
							<div class="tarjetas-phone__logo <?php echo $logo_url ? '' : 'is-empty'; ?>" id="tarjetas_phone_logo">
								<?php if ( $logo_url ) : ?>
									<img src="<?php echo esc_url( $logo_url ); ?>" alt="" />
								<?php endif; ?>
							</div>
						</div>
						<div class="tarjetas-phone__details">
							<p id="tarjetas_preview_telefono" class="<?php echo $telefono ? '' : 'is-empty'; ?>">
								<span class="tarjetas-phone__detail-icon dashicons dashicons-phone" aria-hidden="true"></span>
								<span class="tarjetas-phone__detail-copy"><strong><?php esc_html_e( 'Móvil', 'tarjetas' ); ?></strong><span><?php echo esc_html( $telefono ); ?></span></span>
							</p>
							<p id="tarjetas_preview_whatsapp" class="<?php echo $whatsapp ? '' : 'is-empty'; ?>">
								<span class="tarjetas-phone__detail-icon fa-brands fa-whatsapp" aria-hidden="true"></span>
								<span class="tarjetas-phone__detail-copy"><strong><?php esc_html_e( 'WhatsApp', 'tarjetas' ); ?></strong><span><?php echo esc_html( $whatsapp ); ?></span></span>
							</p>
							<p id="tarjetas_preview_correo" class="<?php echo $correo ? '' : 'is-empty'; ?>">
								<span class="tarjetas-phone__detail-icon dashicons dashicons-email" aria-hidden="true"></span>
								<span class="tarjetas-phone__detail-copy"><strong><?php esc_html_e( 'Correo', 'tarjetas' ); ?></strong><span><?php echo esc_html( $correo ); ?></span></span>
							</p>
							<p id="tarjetas_preview_ubicacion" class="<?php echo $ubicacion ? '' : 'is-empty'; ?>">
								<span class="tarjetas-phone__detail-icon dashicons dashicons-location" aria-hidden="true"></span>
								<span class="tarjetas-phone__detail-copy"><strong><?php esc_html_e( 'Ubicación', 'tarjetas' ); ?></strong><span><?php echo esc_html( $ubicacion ); ?></span></span>
							</p>
						</div>
						<div class="tarjetas-phone__socials" id="tarjetas_preview_sociales">
							<?php $iconos_sociales = array( 'dashicons-linkedin', 'dashicons-instagram', 'dashicons-facebook', 'dashicons-admin-site-alt3' ); ?>
							<?php foreach ( $sociales as $index => $social ) : ?><span><span class="dashicons <?php echo esc_attr( isset( $iconos_sociales[ $index ] ) ? $iconos_sociales[ $index ] : 'dashicons-admin-site-alt3' ); ?>" aria-hidden="true"></span><span><?php echo esc_html( $social['label'] ); ?></span></span><?php endforeach; ?>
						</div>
					</div>
				</div>
			</div>

			<div class="tarjetas-qr-group">
			<div class="tarjetas-qr">
				<h2><?php esc_html_e( 'QR de la tarjeta web', 'tarjetas' ); ?></h2>
				<?php if ( $estado_qr ) : ?>
					<img src="<?php echo esc_url( 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&format=svg&data=' . rawurlencode( $estado_qr ) ); ?>" alt="<?php esc_attr_e( 'Código QR de esta tarjeta', 'tarjetas' ); ?>" width="160" height="160" />
					<a href="<?php echo esc_url( $estado_qr ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Abrir enlace de la tarjeta', 'tarjetas' ); ?></a>
				<?php else : ?>
					<p><?php esc_html_e( 'El código QR estará disponible después de guardar la tarjeta.', 'tarjetas' ); ?></p>
				<?php endif; ?>
			</div>
			<?php tarjetas_renderizar_qr_contacto( $post ); ?>
			</div>
		</aside>
	</div>
	<?php
}

/**
 * Comprueba que un adjunto sea una imagen válida de la biblioteca multimedia.
 *
 * @param int $attachment_id ID del adjunto.
 * @return bool
 */
function tarjetas_es_imagen_adjunta( $attachment_id ) {
	$attachment_id = absint( $attachment_id );
	$mime_type     = $attachment_id ? get_post_mime_type( $attachment_id ) : '';

	return (bool) (
		$attachment_id &&
		'attachment' === get_post_type( $attachment_id ) &&
		0 === strpos( (string) $mime_type, 'image/' ) &&
		wp_attachment_is_image( $attachment_id )
	);
}

/**
 * Comprueba que los datos obligatorios enviados desde el formulario estén completos.
 *
 * @return bool
 */
function tarjetas_datos_obligatorios_completos() {
	$required_fields = array( 'nombre', 'cargo', 'ubicacion' );
	foreach ( $required_fields as $field ) {
		$key = 'tarjetas_' . $field;
		if ( ! isset( $_POST[ $key ] ) || '' === sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) ) {
			return false;
		}
	}

	if ( ! isset( $_POST['tarjetas_correo'] ) || ! is_email( sanitize_email( wp_unslash( $_POST['tarjetas_correo'] ) ) ) ) {
		return false;
	}

	foreach ( array( 'tarjetas_telefono', 'tarjetas_whatsapp' ) as $phone_field ) {
		$phone = isset( $_POST[ $phone_field ] ) ? sanitize_text_field( wp_unslash( $_POST[ $phone_field ] ) ) : '';
		if ( ! preg_match( '/^\+\d{1,3} \d{4} \d{4}$/', $phone ) ) {
			return false;
		}
	}

	$foto_id = isset( $_POST['tarjetas_foto_id'] ) ? absint( $_POST['tarjetas_foto_id'] ) : 0;
	if ( ! tarjetas_es_imagen_adjunta( $foto_id ) ) {
		return false;
	}


	$departamento_id = isset( $_POST['tarjetas_departamento'] ) ? absint( $_POST['tarjetas_departamento'] ) : 0;
	return (bool) ( $departamento_id && term_exists( $departamento_id, 'tarjeta_departamento' ) );
}

/**
 * Evita publicar tarjetas incompletas cuando se omite la validación del navegador.
 *
 * @param array $data    Datos preparados para la base de datos.
 * @param array $postarr Datos originales del post.
 * @return array
 */
function tarjetas_evitar_publicacion_incompleta( $data, $postarr ) {
	if (
		isset( $data['post_type'], $_POST['tarjetas_datos_nonce'] ) &&
		'tarjeta' === $data['post_type'] &&
		in_array( $data['post_status'], array( 'publish', 'future', 'private' ), true ) &&
		! tarjetas_datos_obligatorios_completos()
	) {
		$data['post_status'] = 'draft';
		set_transient( 'tarjetas_datos_incompletos_' . get_current_user_id(), true, MINUTE_IN_SECONDS );
	}

	return $data;
}
add_filter( 'wp_insert_post_data', 'tarjetas_evitar_publicacion_incompleta', 20, 2 );

/**
 * Muestra una explicación cuando el servidor evita publicar una tarjeta incompleta.
 *
 * @return void
 */
function tarjetas_aviso_datos_incompletos() {
	$transient_key = 'tarjetas_datos_incompletos_' . get_current_user_id();
	if ( ! get_transient( $transient_key ) ) {
		return;
	}

	delete_transient( $transient_key );
	printf(
		'<div class="notice notice-error is-dismissible"><p>%s</p></div>',
		esc_html__( 'La tarjeta no se publicó porque faltan campos obligatorios o algún dato tiene un formato inválido.', 'tarjetas' )
	);
}
add_action( 'admin_notices', 'tarjetas_aviso_datos_incompletos' );

/**
 * Guarda los datos del editor de tarjeta.
 *
 * @param int $post_id ID del post.
 * @return void
 */
function tarjetas_guardar_datos( $post_id ) {
	if ( ! isset( $_POST['tarjetas_datos_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tarjetas_datos_nonce'] ) ), 'tarjetas_guardar_datos' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( wp_is_post_revision( $post_id ) || 'tarjeta' !== get_post_type( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$text_fields = array( 'nombre', 'cargo', 'empresa', 'ubicacion' );
	foreach ( $text_fields as $field ) {
		$key   = 'tarjetas_' . $field;
		$value = isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
		update_post_meta( $post_id, '_tarjetas_' . $field, $value );
	}

	foreach ( array( 'telefono', 'whatsapp' ) as $phone_field ) {
		$key   = 'tarjetas_' . $phone_field;
		$value = isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
		$value = preg_match( '/^\+\d{1,3} \d{4} \d{4}$/', $value ) ? $value : '';
		update_post_meta( $post_id, '_tarjetas_' . $phone_field, $value );
	}

	$correo = isset( $_POST['tarjetas_correo'] ) ? sanitize_email( wp_unslash( $_POST['tarjetas_correo'] ) ) : '';
	update_post_meta( $post_id, '_tarjetas_correo', $correo );

	$color = isset( $_POST['tarjetas_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['tarjetas_color'] ) ) : '';
	update_post_meta( $post_id, '_tarjetas_color', $color ? $color : '#087d0a' );

	$sociales = array();
	if ( isset( $_POST['tarjetas_sociales'] ) && is_array( $_POST['tarjetas_sociales'] ) ) {
		foreach ( wp_unslash( $_POST['tarjetas_sociales'] ) as $social ) {
			if ( ! is_array( $social ) ) {
				continue;
			}

			$url = isset( $social['url'] ) ? esc_url_raw( $social['url'] ) : '';
			if ( ! $url ) {
				continue;
			}

			$sociales[] = array(
				'label' => isset( $social['label'] ) ? sanitize_text_field( $social['label'] ) : __( 'Enlace', 'tarjetas' ),
				'url'   => $url,
				'icon'  => isset( $social['icon'] ) && preg_match( '/^[a-z0-9 -]+$/i', $social['icon'] ) ? $social['icon'] : 'fa-solid fa-globe',
			);
		}
	}
	update_post_meta( $post_id, '_tarjetas_sociales', $sociales );

	$foto_id = isset( $_POST['tarjetas_foto_id'] ) ? absint( $_POST['tarjetas_foto_id'] ) : 0;
	if ( ! tarjetas_es_imagen_adjunta( $foto_id ) ) {
		$foto_id = 0;
	}
	update_post_meta( $post_id, '_tarjetas_foto_id', $foto_id );

	if ( isset( $_POST['tarjetas_logo_id'] ) && is_scalar( $_POST['tarjetas_logo_id'] ) ) {
		$logo_id = absint( $_POST['tarjetas_logo_id'] );
		if ( 0 === $logo_id ) {
			$logo_id = tarjetas_logo_inicial_id( $post_id );
		}
		if ( 0 === $logo_id || tarjetas_es_imagen_adjunta( $logo_id ) ) {
			update_post_meta( $post_id, '_tarjetas_logo_id', $logo_id );
		}
	}

	if ( isset( $_POST['tarjetas_departamento'] ) ) {
		$departamento_id = absint( $_POST['tarjetas_departamento'] );
		$departamento    = $departamento_id ? get_term( $departamento_id, 'tarjeta_departamento' ) : false;

		wp_set_post_terms(
			$post_id,
			$departamento && ! is_wp_error( $departamento ) ? array( $departamento_id ) : array(),
			'tarjeta_departamento',
			false
		);
	}

	$nombre = tarjetas_obtener_meta( $post_id, 'nombre' );
	if ( $nombre ) {
		$post = get_post( $post_id );
		if ( $post && $nombre !== $post->post_title ) {
			remove_action( 'save_post_tarjeta', 'tarjetas_guardar_datos' );
			wp_update_post(
				array(
					'ID'         => $post_id,
					'post_title' => $nombre,
					'post_name'  => sanitize_title( $nombre ),
				)
			);
			add_action( 'save_post_tarjeta', 'tarjetas_guardar_datos' );
		}
	}
}
add_action( 'save_post_tarjeta', 'tarjetas_guardar_datos' );

/**
 * Carga assets solo en el editor de tarjetas.
 *
 * @param string $hook Página administrativa actual.
 * @return void
 */
function tarjetas_cargar_assets_admin( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || 'tarjeta' !== $screen->post_type ) {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_style( 'tarjetas-font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css', array(), '6.7.2' );
	wp_enqueue_style( 'tarjetas-editor-admin', plugin_dir_url( __FILE__ ) . 'assets/css/admin-editor.css', array( 'wp-color-picker', 'tarjetas-font-awesome' ), '1.4.2' );
	wp_enqueue_script( 'tarjetas-editor-admin', plugin_dir_url( __FILE__ ) . 'assets/js/admin-editor.js', array( 'jquery', 'wp-color-picker' ), '1.4.1', true );
	wp_enqueue_script( 'tarjetas-qr-generator', plugin_dir_url( __FILE__ ) . 'assets/js/qrcode-generator.js', array(), '1.4.4', true );
	wp_enqueue_script( 'tarjetas-contacto-qr', plugin_dir_url( __FILE__ ) . 'assets/js/contact-qr.js', array( 'tarjetas-qr-generator' ), '1.0.1', true );
	wp_localize_script(
		'tarjetas-contacto-qr',
		'TarjetasContactoQR',
		array(
			'error'    => __( 'No se pudo generar el QR de contacto. Recarga la página para intentarlo de nuevo.', 'tarjetas' ),
			'overflow' => __( 'Los datos superan la capacidad de un QR. Acorta los campos o enlaces y guarda de nuevo, o utiliza el QR de la tarjeta web. No se ha omitido información.', 'tarjetas' ),
		)
	);
	wp_localize_script(
		'tarjetas-editor-admin',
		'TarjetasEditor',
		array(
			'mediaTitle'  => __( 'Seleccionar fotografía del colaborador', 'tarjetas' ),
			'mediaButton' => __( 'Usar fotografía', 'tarjetas' ),
			'logoTitle'   => __( 'Seleccionar logo de la empresa', 'tarjetas' ),
			'logoButton'  => __( 'Usar logo', 'tarjetas' ),
			'defaultLogo' => tarjetas_logo_url_por_id( tarjetas_logo_inicial_id( get_the_ID() ) ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'tarjetas_cargar_assets_admin' );

/**
 * Carga los estilos únicamente en las tarjetas públicas.
 *
 * @return void
 */
function tarjetas_cargar_assets_publicos() {
	if ( ! is_singular( 'tarjeta' ) ) {
		return;
	}

	wp_enqueue_style( 'tarjetas-font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css', array(), '6.7.2' );
	wp_enqueue_style( 'tarjetas-public-card', plugin_dir_url( __FILE__ ) . 'assets/css/public-card.css', array( 'tarjetas-font-awesome' ), '1.4.2' );
}
add_action( 'wp_enqueue_scripts', 'tarjetas_cargar_assets_publicos' );

/**
 * Sustituye la plantilla del tema por la tarjeta pública aislada.
 *
 * @param string $template Plantilla que WordPress seleccionó.
 * @return string
 */
function tarjetas_plantilla_publica( $template ) {
	if ( is_singular( 'tarjeta' ) ) {
		return plugin_dir_path( __FILE__ ) . 'templates/single-tarjeta.php';
	}

	return $template;
}
add_filter( 'template_include', 'tarjetas_plantilla_publica', 99 );

/**
 * Formatea un teléfono de Nicaragua para mostrarlo en el editor.
 *
 * @param string $telefono Teléfono almacenado en la tarjeta.
 * @return string
 */
function tarjetas_formatear_telefono( $telefono ) {
	$digits = preg_replace( '/\D+/', '', $telefono );

	if ( 11 === strlen( $digits ) ) {
		return '+' . substr( $digits, 0, 3 ) . ' ' . substr( $digits, 3, 4 ) . ' ' . substr( $digits, 7 );
	}

	return $telefono;
}

/**
 * Convierte un teléfono local de Nicaragua a formato E.164.
 *
 * @param string $telefono Teléfono almacenado en la tarjeta.
 * @return string
 */
function tarjetas_telefono_e164( $telefono ) {
	$digits = preg_replace( '/\D+/', '', $telefono );

	if ( 8 === strlen( $digits ) ) {
		return '+505' . $digits;
	}

	if ( 11 === strlen( $digits ) && 0 === strpos( $digits, '505' ) ) {
		return '+' . $digits;
	}

	return $digits ? '+' . $digits : '';
}

/**
 * Escapa un valor para usarlo dentro de una vCard 3.0.
 *
 * @param string $value Valor sin escapar.
 * @return string
 */
function tarjetas_escapar_vcard( $value ) {
	$value = wp_strip_all_tags( (string) $value );
	$value = str_replace( '\\', '\\\\', $value );
	$value = str_replace( array( "\r\n", "\n", "\r" ), '\\n', $value );

	return str_replace( array( ';', ',' ), array( '\\;', '\\,' ), $value );
}


/**
 * Muestra el QR en cualquier registro guardado, sin metadatos ni migraciones.
 *
 * @param WP_Post $post Tarjeta actual.
 * @return void
 */
function tarjetas_renderizar_qr_contacto( $post ) {
	?>
	<div class="tarjetas-qr" id="tarjetas_contacto_qr">
		<h2><?php esc_html_e( 'QR para añadir contacto', 'tarjetas' ); ?></h2>
		<?php if ( 'auto-draft' !== $post->post_status ) : ?>
			<?php
			$nombre = tarjetas_obtener_meta( $post->ID, 'nombre' );
			$datos  = array(
				'vcard'    => tarjetas_generar_vcard_qr( $post->ID ),
				'filename' => ( sanitize_title( $nombre ) ?: 'contacto' ) . '-qr.svg',
			);
			?>
			<script type="application/json" id="tarjetas_contacto_qr_datos"><?php echo wp_json_encode( $datos, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON seguro dentro del bloque de datos. ?></script>
			<div class="tarjetas-qr__image" data-alt="<?php esc_attr_e( 'Código QR con los datos del contacto', 'tarjetas' ); ?>"></div>
			<p class="tarjetas-qr__status" role="status"><?php esc_html_e( 'El QR de contacto necesita JavaScript para generarse.', 'tarjetas' ); ?></p>
			<a class="tarjetas-qr__download" hidden><?php esc_html_e( 'Descargar QR de contacto (SVG)', 'tarjetas' ); ?></a>
			<p><?php esc_html_e( 'Escanéalo con un lector compatible para añadir el contacto. La foto se incluye como enlace; no todos los lectores la importan.', 'tarjetas' ); ?></p>
			<p><?php esc_html_e( 'Incluye los últimos datos guardados. Después de cambiarlos, guarda la tarjeta y descarga de nuevo el QR. Los códigos impresos no se actualizan.', 'tarjetas' ); ?></p>
		<?php else : ?>
			<p><?php esc_html_e( 'El código QR estará disponible después de guardar la tarjeta.', 'tarjetas' ); ?></p>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Genera la vCard del QR con imágenes por URI, sin cargar ni incrustar archivos.
 *
 * @param int $post_id ID de la tarjeta.
 * @return string
 */
function tarjetas_generar_vcard_qr( $post_id ) {
	$nombre        = tarjetas_obtener_meta( $post_id, 'nombre' );
	$nombre        = $nombre ? $nombre : get_the_title( $post_id );
	$empresa       = tarjetas_obtener_meta( $post_id, 'empresa' );
	$cargo         = tarjetas_obtener_meta( $post_id, 'cargo' );
	$telefono      = tarjetas_telefono_e164( tarjetas_obtener_meta( $post_id, 'telefono' ) );
	$whatsapp      = tarjetas_telefono_e164( tarjetas_obtener_meta( $post_id, 'whatsapp' ) );
	$correo        = tarjetas_obtener_meta( $post_id, 'correo' );
	$ubicacion     = tarjetas_obtener_meta( $post_id, 'ubicacion' );
	$departamentos = get_the_terms( $post_id, 'tarjeta_departamento' );
	$vcard         = array( 'BEGIN:VCARD', 'VERSION:3.0', 'N:;' . tarjetas_escapar_vcard( $nombre ) . ';;;', 'FN:' . tarjetas_escapar_vcard( $nombre ) );
	$organizacion  = array( tarjetas_escapar_vcard( $empresa ) );

	if ( ! empty( $departamentos ) && ! is_wp_error( $departamentos ) ) {
		foreach ( $departamentos as $departamento ) {
			$organizacion[] = tarjetas_escapar_vcard( $departamento->name );
		}
	}
	if ( $empresa || count( $organizacion ) > 1 ) {
		$vcard[] = 'ORG:' . implode( ';', $organizacion );
	}
	if ( $cargo ) {
		$vcard[] = 'TITLE:' . tarjetas_escapar_vcard( $cargo );
	}
	if ( $telefono ) {
		$vcard[] = 'TEL;TYPE=CELL,VOICE:' . $telefono;
	}
	if ( $whatsapp ) {
		if ( $whatsapp !== $telefono ) {
			$vcard[] = 'TEL;TYPE=CELL:' . $whatsapp;
		}
		$vcard[] = 'URL:https://wa.me/' . ltrim( $whatsapp, '+' );
		$vcard[] = 'X-WA-ID:' . $whatsapp;
	}
	if ( $correo ) {
		$vcard[] = 'EMAIL;TYPE=INTERNET,WORK:' . tarjetas_escapar_vcard( $correo );
	}
	if ( $ubicacion ) {
		// La ubicación es texto libre, no una dirección dividida en ciudad y país.
		$vcard[] = 'ADR;TYPE=WORK:;;' . tarjetas_escapar_vcard( $ubicacion ) . ';;;;';
	}

	$vcard[] = 'URL:' . esc_url_raw( get_permalink( $post_id ) );
	foreach ( tarjetas_obtener_sociales( $post_id ) as $social ) {
		$vcard[] = 'URL:' . $social['url'];
	}

	$foto_id = absint( tarjetas_obtener_meta( $post_id, 'foto_id' ) );
	$foto    = $foto_id ? wp_get_attachment_image_url( $foto_id, 'medium' ) : '';
	if ( $foto ) {
		$vcard[] = 'PHOTO;VALUE=URI:' . esc_url_raw( $foto );
	}
	$logo = tarjetas_logo_tarjeta_url( $post_id );
	if ( $logo ) {
		$vcard[] = 'LOGO;VALUE=URI:' . esc_url_raw( $logo );
	}

	$vcard[] = 'END:VCARD';
	// Algunos lectores QR no despliegan líneas plegadas de vCard correctamente.
	return implode( "\r\n", $vcard ) . "\r\n";
}

/**
 * Devuelve la fotografía de la tarjeta codificada para una vCard.
 *
 * @param int $attachment_id ID de adjunto de WordPress.
 * @return string
 */
function tarjetas_foto_vcard( $attachment_id ) {
	$file = get_attached_file( $attachment_id );
	if ( ! $file || ! is_readable( $file ) ) {
		return '';
	}

	$mime = get_post_mime_type( $attachment_id );
	$types = array(
		'image/jpeg' => 'JPEG',
		'image/png'  => 'PNG',
		'image/gif'  => 'GIF',
		'image/webp' => 'WEBP',
	);
	if ( ! isset( $types[ $mime ] ) ) {
		return '';
	}

	$photo = file_get_contents( $file );
	if ( false === $photo ) {
		return '';
	}

	return 'PHOTO;ENCODING=b;TYPE=' . $types[ $mime ] . ':' . rtrim( chunk_split( base64_encode( $photo ), 60, "\r\n " ) );
}

/**
 * Entrega una vCard descargable para la tarjeta pública actual.
 *
 * @return void
 */
function tarjetas_descargar_vcard() {
	if ( ! is_singular( 'tarjeta' ) || ! isset( $_GET['tarjetas_vcard'] ) || '1' !== sanitize_text_field( wp_unslash( $_GET['tarjetas_vcard'] ) ) ) {
		return;
	}

	$post_id = get_queried_object_id();
	if ( 'publish' !== get_post_status( $post_id ) ) {
		wp_die( esc_html__( 'Esta tarjeta no está disponible.', 'tarjetas' ), '', array( 'response' => 404 ) );
	}

	$nombre       = tarjetas_obtener_meta( $post_id, 'nombre' );
	$cargo        = tarjetas_obtener_meta( $post_id, 'cargo' );
	$empresa      = tarjetas_obtener_meta( $post_id, 'empresa' );
	$telefono     = tarjetas_telefono_e164( tarjetas_obtener_meta( $post_id, 'telefono' ) );
	$whatsapp     = tarjetas_telefono_e164( tarjetas_obtener_meta( $post_id, 'whatsapp' ) );
	$correo       = tarjetas_obtener_meta( $post_id, 'correo' );
	$ubicacion    = tarjetas_obtener_meta( $post_id, 'ubicacion' );
	$sociales     = tarjetas_obtener_sociales( $post_id );
	$foto_id      = absint( tarjetas_obtener_meta( $post_id, 'foto_id' ) );
	$departamentos = get_the_terms( $post_id, 'tarjeta_departamento' );
	$departamento  = ! empty( $departamentos ) && ! is_wp_error( $departamentos ) ? $departamentos[0]->name : '';
	$vcard         = array(
		'BEGIN:VCARD',
		'VERSION:3.0',
		'N:;' . tarjetas_escapar_vcard( $nombre ) . ';;;',
		'FN:' . tarjetas_escapar_vcard( $nombre ),
	);

	if ( $cargo ) {
		$vcard[] = 'TITLE:' . tarjetas_escapar_vcard( $cargo );
	}
	if ( $empresa || $departamento ) {
		$vcard[] = 'ORG:' . tarjetas_escapar_vcard( $empresa ) . ';' . tarjetas_escapar_vcard( $departamento );
	}
	if ( $telefono ) {
		$vcard[] = 'TEL;TYPE=CELL,VOICE:' . $telefono;
	}
	if ( $whatsapp ) {
		$vcard[] = 'TEL;TYPE=CELL,WHATSAPP:' . $whatsapp;
		$vcard[] = 'X-WA-ID:' . $whatsapp;
	}
	if ( $correo ) {
		$vcard[] = 'EMAIL;TYPE=INTERNET,WORK:' . tarjetas_escapar_vcard( $correo );
	}
	if ( $ubicacion ) {
		$vcard[] = 'X-ADDRESS:' . tarjetas_escapar_vcard( $ubicacion );
	}
	foreach ( $sociales as $social ) {
		$vcard[] = 'URL:' . tarjetas_escapar_vcard( $social['url'] );
	}

	$foto = $foto_id ? tarjetas_foto_vcard( $foto_id ) : '';
	if ( $foto ) {
		$vcard[] = $foto;
	}

	$vcard[] = 'END:VCARD';
	$filename = sanitize_title( $nombre ? $nombre : get_the_title( $post_id ) );

	nocache_headers();
	header( 'Content-Type: text/vcard; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="' . ( $filename ? $filename : 'contacto' ) . '.vcf"' );
	echo implode( "\r\n", $vcard ) . "\r\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Valores ya están escapados para el formato vCard.
	exit;
}
add_action( 'template_redirect', 'tarjetas_descargar_vcard' );

/**
 * Actualiza las reglas de enlaces permanentes al activar o desactivar.
 *
 * @return void
 */
function tarjetas_activar_plugin() {
	tarjetas_registrar_tipo_de_contenido();
	tarjetas_registrar_taxonomia_departamento();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'tarjetas_activar_plugin' );

/**
 * Limpia las reglas de enlaces permanentes al desactivar.
 *
 * @return void
 */
function tarjetas_desactivar_plugin() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'tarjetas_desactivar_plugin' );
