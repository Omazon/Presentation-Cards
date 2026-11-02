( function ( $, window ) {
	'use strict';

	var mediaFrame;
	var logoFrame;
	var $photoId = $( '#tarjetas_foto_id' );
	var $photoPreview = $( '#tarjetas_foto_preview' );
	var $phonePhoto = $( '#tarjetas_phone_photo' );
	var $photoError = $( '#tarjetas_foto_error' );
	var $removePhoto = $( '#tarjetas_quitar_foto' );
	var $logoId = $( '#tarjetas_logo_id' );
	var $logoPreview = $( '#tarjetas_logo_preview' );
	var $phoneLogo = $( '#tarjetas_phone_logo' );
	var $removeLogo = $( '#tarjetas_quitar_logo' );
	var skipRequiredValidation = false;

	function escapeHtml( value ) {
		return $( '<div>' ).text( value ).html();
	}

	function updateCardColor( color ) {
		var selectedColor = /^#[0-9a-f]{6}$/i.test( color ) ? color : '#087d0a';

		$( '.tarjetas-editor' ).css( {
			'--tarjetas-green': selectedColor,
			'--tarjetas-green-dark': selectedColor
		} );
	}

	$( '#tarjetas_color' ).wpColorPicker( {
		change: function ( event, ui ) {
			updateCardColor( ui.color.toString() );
		},
		clear: function () {
			updateCardColor( '#087d0a' );
		}
	} );

	$( '#tarjetas_color' ).on( 'change', function () {
		updateCardColor( $( this ).val() );
	} );

	function updateTextPreview( inputSelector, previewSelector, fallback ) {
		var value = $( inputSelector ).val().trim();
		$( previewSelector ).text( value || fallback );
	}

	function updateDetailPreview( inputSelector, previewSelector ) {
		var value = $( inputSelector ).val().trim();
		var $preview = $( previewSelector );

		$preview.toggleClass( 'is-empty', ! value );
		$preview.find( '.tarjetas-phone__detail-copy > span' ).text( value );
	}

	function updateDepartmentPreview() {
		var $selected = $( '#tarjetas_departamento option:selected' );
		var value = $selected.val() && '0' !== $selected.val() ? $selected.text() : 'División o departamento';
		$( '#tarjetas_preview_departamento' ).text( value );
	}

	function updateSocialPreview() {
		var markup = '';
		var socialIcons = [ 'dashicons-linkedin', 'dashicons-instagram', 'dashicons-facebook', 'dashicons-admin-site-alt3' ];

		$( '.tarjetas-social-row' ).each( function ( index ) {
			var $row = $( this );
			var label = $row.find( '[name$="[label]"]' ).val().trim();
			var url = $row.find( '[name$="[url]"]' ).val().trim();

			if ( url ) {
				var icon = socialIcons[ index ] || 'dashicons-admin-site-alt3';
				markup += '<span><span class="dashicons ' + icon + '" aria-hidden="true"></span><span>' + escapeHtml( label || url ) + '</span></span>';
			}
		} );

		$( '#tarjetas_preview_sociales' ).html( markup );
	}

	function addSocialRow() {
		var index = $( '.tarjetas-social-row' ).length;
		var prefix = 'tarjetas_sociales_' + index;
		var markup = '<div class="tarjetas-social-row">' +
			'<div class="tarjetas-field"><label for="' + prefix + '_label">Nombre</label><input class="regular-text" type="text" id="' + prefix + '_label" name="tarjetas_sociales[' + index + '][label]" value="" autocomplete="off"></div>' +
			'<div class="tarjetas-field"><label for="' + prefix + '_url">Enlace</label><input class="regular-text" type="url" id="' + prefix + '_url" name="tarjetas_sociales[' + index + '][url]" value="" autocomplete="off"><input type="hidden" name="tarjetas_sociales[' + index + '][icon]" value="fa-solid fa-globe"></div>' +
			'<button type="button" class="button-link-delete tarjetas-remove-social">Quitar</button>' +
			'</div>';

		$( '#tarjetas_sociales_fields' ).append( markup );
	}

	function getImageUrl( attachment ) {
		if ( attachment.sizes && attachment.sizes.medium ) {
			return attachment.sizes.medium.url;
		}

		if ( attachment.sizes && attachment.sizes.thumbnail ) {
			return attachment.sizes.thumbnail.url;
		}

		return attachment.url;
	}

	function setPhoto( attachment ) {
		var imageUrl = getImageUrl( attachment );

		$photoId.val( attachment.id );
		$photoPreview.removeClass( 'is-empty' ).html( '<img src="' + escapeHtml( imageUrl ) + '" alt="">' );
		$phonePhoto.removeClass( 'is-empty' ).html( '<img src="' + escapeHtml( imageUrl ) + '" alt="">' );
		$photoError.addClass( 'is-hidden' );
		$removePhoto.removeClass( 'is-hidden' );
	}

	$( '#tarjetas_seleccionar_foto' ).on( 'click', function () {
		if ( mediaFrame ) {
			mediaFrame.open();
			return;
		}

		mediaFrame = window.wp.media( {
			title: window.TarjetasEditor.mediaTitle,
			button: { text: window.TarjetasEditor.mediaButton },
			library: { type: 'image' },
			multiple: false
		} );

		mediaFrame.on( 'select', function () {
			setPhoto( mediaFrame.state().get( 'selection' ).first().toJSON() );
		} );

		mediaFrame.open();
	} );

	$removePhoto.on( 'click', function () {
		$photoId.val( '' );
		$photoPreview.addClass( 'is-empty' ).html( '<span class="dashicons dashicons-format-image" aria-hidden="true"></span>' );
		$phonePhoto.addClass( 'is-empty' ).html( '<span class="dashicons dashicons-id-alt" aria-hidden="true"></span>' );
		$removePhoto.addClass( 'is-hidden' );
	} );

	$( '#tarjetas_seleccionar_logo' ).on( 'click', function () {
		if ( logoFrame ) {
			logoFrame.open();
			return;
		}

		logoFrame = window.wp.media( {
			title: window.TarjetasEditor.logoTitle,
			button: { text: window.TarjetasEditor.logoButton },
			library: { type: 'image' },
			multiple: false
		} );

		logoFrame.on( 'select', function () {
			var attachment = logoFrame.state().get( 'selection' ).first().toJSON();
			var imageUrl = getImageUrl( attachment );

			$logoId.val( attachment.id );
			$logoPreview.removeClass( 'is-empty' ).html( '<img src="' + escapeHtml( imageUrl ) + '" alt="">' );
			$phoneLogo.removeClass( 'is-empty' ).html( '<img src="' + escapeHtml( imageUrl ) + '" alt="">' );
			$removeLogo.removeClass( 'is-hidden' );
		} );

		logoFrame.open();
	} );

	$removeLogo.on( 'click', function () {
		$logoId.val( '' );
		$logoPreview.removeClass( 'is-empty' ).html( '<img src="' + escapeHtml( window.TarjetasEditor.defaultLogo ) + '" alt="">' );
		$phoneLogo.removeClass( 'is-empty' ).html( '<img src="' + escapeHtml( window.TarjetasEditor.defaultLogo ) + '" alt="">' );
		$removeLogo.addClass( 'is-hidden' );
	} );

	$( '#tarjetas_nombre' ).on( 'input', function () {
		updateTextPreview( '#tarjetas_nombre', '#tarjetas_preview_nombre', 'Nombre del colaborador' );
	} );

	$( '#tarjetas_cargo' ).on( 'input', function () {
		updateTextPreview( '#tarjetas_cargo', '#tarjetas_preview_cargo', 'Cargo' );
	} );

	$( '#tarjetas_empresa' ).on( 'input', function () {
		updateTextPreview( '#tarjetas_empresa', '#tarjetas_preview_empresa', 'Empresa' );
	} );

	$( '#tarjetas_departamento' ).on( 'change', updateDepartmentPreview );

	$( '#tarjetas_telefono, #tarjetas_whatsapp' ).on( 'input', function () {
		var digits = $( this ).val().replace( /\D/g, '' ).slice( 0, 11 );
		var maskedValue = digits ? '+' + digits.slice( 0, 3 ) : '';

		if ( digits.length > 3 ) {
			maskedValue += ' ' + digits.slice( 3, 7 );
		}
		if ( digits.length > 7 ) {
			maskedValue += ' ' + digits.slice( 7, 11 );
		}

		$( this ).val( maskedValue );
	} );

	[
		[ '#tarjetas_telefono', '#tarjetas_preview_telefono' ],
		[ '#tarjetas_whatsapp', '#tarjetas_preview_whatsapp' ],
		[ '#tarjetas_correo', '#tarjetas_preview_correo' ],
		[ '#tarjetas_ubicacion', '#tarjetas_preview_ubicacion' ]
	].forEach( function ( binding ) {
		$( binding[ 0 ] ).on( 'input', function () {
			updateDetailPreview( binding[ 0 ], binding[ 1 ] );
		} );
	} );

	$( '#tarjetas_agregar_social' ).on( 'click', function () {
		addSocialRow();
		$( '#tarjetas_sociales_fields input' ).last().trigger( 'focus' );
	} );

	$( '#tarjetas_sociales_fields' ).on( 'input', 'input', updateSocialPreview );
	$( '#tarjetas_sociales_fields' ).on( 'click', '.tarjetas-remove-social', function () {
		$( this ).closest( '.tarjetas-social-row' ).remove();
		updateSocialPreview();
	} );

	$( '#post' ).on( 'click', ':submit', function () {
		skipRequiredValidation = $( this ).hasClass( 'tarjetas-hide-button' );
	} );

	$( '#post' ).on( 'submit', function ( event ) {
		var focusSelector = '';

		if ( skipRequiredValidation ) {
			return;
		}

		if ( ! $photoId.val() ) {
			$photoError.removeClass( 'is-hidden' );
			focusSelector = '#tarjetas_seleccionar_foto';
		}


		if ( focusSelector ) {
			event.preventDefault();
			$( focusSelector ).trigger( 'focus' );
		}
	} );
} )( jQuery, window );
