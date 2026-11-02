<?php
/**
 * Plantilla pública para una tarjeta de contacto.
 *
 * @package Tarjetas
 */

defined( 'ABSPATH' ) || exit;

while ( have_posts() ) :
	the_post();

	$post_id      = get_the_ID();
	$nombre       = tarjetas_obtener_meta( $post_id, 'nombre' );
	$cargo        = tarjetas_obtener_meta( $post_id, 'cargo' );
	$empresa      = tarjetas_obtener_meta( $post_id, 'empresa' );
	$telefono     = tarjetas_obtener_meta( $post_id, 'telefono' );
	$whatsapp     = tarjetas_obtener_meta( $post_id, 'whatsapp' );
	$correo       = tarjetas_obtener_meta( $post_id, 'correo' );
	$ubicacion    = tarjetas_obtener_meta( $post_id, 'ubicacion' );
	$color        = sanitize_hex_color( tarjetas_obtener_meta( $post_id, 'color' ) );
	$color        = $color ? $color : '#087d0a';
	$foto_id      = absint( tarjetas_obtener_meta( $post_id, 'foto_id' ) );
	$foto_url     = $foto_id ? wp_get_attachment_image_url( $foto_id, 'large' ) : '';
	$logo_url     = tarjetas_logo_tarjeta_url( $post_id );
	$departamentos = get_the_terms( $post_id, 'tarjeta_departamento' );
	$departamento  = ! empty( $departamentos ) && ! is_wp_error( $departamentos ) ? $departamentos[0]->name : '';
	$telefono_e164 = tarjetas_telefono_e164( $telefono );
	$whatsapp_e164 = tarjetas_telefono_e164( $whatsapp );
	$sociales      = tarjetas_obtener_sociales( $post_id );
	?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'tarjetas-public-card' ); ?>>
<?php wp_body_open(); ?>
<?php block_template_part( 'header' ); ?>
<main class="tarjetas-card-page" style="--tarjetas-color: <?php echo esc_attr( $color ); ?>;">
	<article class="tarjetas-card">
		<?php if ( $foto_url ) : ?>
			<div class="tarjetas-card__photo">
				<img src="<?php echo esc_url( $foto_url ); ?>" alt="<?php echo esc_attr( $nombre ); ?>">
			</div>
		<?php endif; ?>
		<div class="tarjetas-card__content">
			<header class="tarjetas-card__identity">
				<div>
					<h1 class="tarjetas-card__name"><?php echo esc_html( $nombre ); ?></h1>
					<?php if ( $cargo ) : ?><p class="tarjetas-card__position"><?php echo esc_html( $cargo ); ?></p><?php endif; ?>
					<?php if ( $departamento ) : ?><p class="tarjetas-card__department"><?php echo esc_html( $departamento ); ?></p><?php endif; ?>
					<?php if ( $empresa ) : ?><p class="tarjetas-card__company"><?php echo esc_html( $empresa ); ?></p><?php endif; ?>
				</div>
				<?php if ( $logo_url ) : ?>
									<div class="tarjetas-card__logo"><img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php esc_attr_e( 'Logo de la empresa', 'tarjetas' ); ?>"></div>
								<?php endif; ?>
			</header>

			<div class="tarjetas-card__details">
				<?php if ( $telefono ) : ?>
					<a class="tarjetas-card__detail" href="tel:<?php echo esc_attr( $telefono_e164 ); ?>">
						<span class="tarjetas-card__detail-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.9.33 1.79.62 2.65a2 2 0 0 1-.45 2.11L8 9.76a16 16 0 0 0 6 6l1.28-1.28a2 2 0 0 1 2.11-.45c.86.29 1.75.5 2.65.62A2 2 0 0 1 22 16.92z"></path></svg></span>
						<span class="tarjetas-card__detail-copy"><span class="tarjetas-card__detail-label"><?php esc_html_e( 'Móvil', 'tarjetas' ); ?></span><span class="tarjetas-card__detail-value"><?php echo esc_html( $telefono ); ?></span></span>
					</a>
				<?php endif; ?>
				<?php if ( $whatsapp ) : ?>
					<a class="tarjetas-card__detail" href="https://wa.me/<?php echo esc_attr( ltrim( $whatsapp_e164, '+' ) ); ?>" target="_blank" rel="noopener noreferrer">
						<span class="tarjetas-card__detail-icon" aria-hidden="true"><i class="fa-brands fa-whatsapp"></i></span>
						<span class="tarjetas-card__detail-copy"><span class="tarjetas-card__detail-label">WhatsApp</span><span class="tarjetas-card__detail-value"><?php echo esc_html( $whatsapp ); ?></span></span>
					</a>
				<?php endif; ?>
				<?php if ( $correo ) : ?>
					<a class="tarjetas-card__detail" href="mailto:<?php echo esc_attr( $correo ); ?>">
						<span class="tarjetas-card__detail-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="m3 7 9 6 9-6"></path></svg></span>
						<span class="tarjetas-card__detail-copy"><span class="tarjetas-card__detail-label"><?php esc_html_e( 'Correo', 'tarjetas' ); ?></span><span class="tarjetas-card__detail-value"><?php echo esc_html( $correo ); ?></span></span>
					</a>
				<?php endif; ?>
				<?php if ( $ubicacion ) : ?>
					<a class="tarjetas-card__detail" href="https://www.google.com/maps/search/?api=1&amp;query=<?php echo rawurlencode( $ubicacion ); ?>" target="_blank" rel="noopener noreferrer">
						<span class="tarjetas-card__detail-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0z"></path><circle cx="12" cy="10" r="2.5"></circle></svg></span>
						<span class="tarjetas-card__detail-copy"><span class="tarjetas-card__detail-label"><?php esc_html_e( 'Ubicación', 'tarjetas' ); ?></span><span class="tarjetas-card__detail-value"><?php echo esc_html( $ubicacion ); ?></span></span>
					</a>
				<?php endif; ?>
			</div>

			<?php if ( $sociales ) : ?>
				<nav class="tarjetas-card__socials" aria-label="<?php esc_attr_e( 'Enlaces de contacto', 'tarjetas' ); ?>">
					<?php foreach ( $sociales as $red ) : ?>
						<a class="tarjetas-card__social" href="<?php echo esc_url( $red['url'] ); ?>" target="_blank" rel="noopener noreferrer"><i class="<?php echo esc_attr( $red['icon'] ); ?>" aria-hidden="true"></i><span><?php echo esc_html( $red['label'] ); ?></span></a>
					<?php endforeach; ?>
				</nav>
			<?php endif; ?>

			<a class="tarjetas-card__save" href="<?php echo esc_url( add_query_arg( 'tarjetas_vcard', '1', get_permalink( $post_id ) ) ); ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><path d="M17 21v-8H7v8M7 3v5h8"></path></svg><?php esc_html_e( 'Guardar contacto', 'tarjetas' ); ?></a>
		</div>
	</article>
</main>
<?php block_template_part( 'footer' ); ?>
<?php wp_footer(); ?>
</body>
</html>
<?php endwhile; ?>
