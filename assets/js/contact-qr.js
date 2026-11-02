(function () {
	'use strict';

	var container = document.getElementById('tarjetas_contacto_qr');
	var payload = document.getElementById('tarjetas_contacto_qr_datos');
	if (!container || !payload) {
		return;
	}

	var preview = container.querySelector('.tarjetas-qr__image');
	var download = container.querySelector('.tarjetas-qr__download');
	var status = container.querySelector('.tarjetas-qr__status');

	try {
		var data = JSON.parse(payload.textContent);
		// The encoder defaults to single-byte text; contact names require UTF-8.
		qrcode.stringToBytes = qrcode.stringToBytesFuncs['UTF-8'];
		var code = qrcode(0, 'M');
		code.addData(data.vcard, 'Byte');
		code.make();

		// Preserve four quiet-zone modules on every side, including in the download.
		var svg = code.createSvgTag({ cellSize: 4, margin: 16 });
		var url = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svg);
		var image = document.createElement('img');
		image.alt = preview.dataset.alt;
		image.width = 320;
		image.height = 320;
		image.src = url;
		preview.appendChild(image);
		download.href = url;
		download.download = data.filename;
		download.hidden = false;
		status.hidden = true;
	} catch (error) {
		download.hidden = true;
		status.hidden = false;
		status.textContent = String(error).indexOf('code length overflow') !== -1
			? TarjetasContactoQR.overflow
			: TarjetasContactoQR.error;
	}
}());
