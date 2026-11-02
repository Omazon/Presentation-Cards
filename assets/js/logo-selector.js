/* global jQuery, wp, TarjetasLogo */
(function ($) {
	'use strict';

	$(function () {
		$('[data-tarjetas-logo-selector]').each(function () {
			var root = $(this);
			var input = root.find('[data-logo-input]');
			var preview = root.find('[data-logo-preview]');
			var reset = root.find('[data-logo-reset]');
			var initialId = Number(root.attr('data-initial-id')) || 0;
			var initialUrl = root.attr('data-initial-url') || '';
			var secondary = root.attr('data-secondary-preview');
			var frame;

			function update(id, url, message) {
				input.val(id).trigger('change');
				preview.attr('src', url).prop('hidden', !url);
				root.find('.tarjetas-logo-field__preview').toggleClass('is-empty', !url);
				reset.prop('disabled', id === initialId);
				root.find('[data-logo-status]').text(message);

				if (secondary) {
					var target = $(secondary);
					target.empty().toggleClass('is-empty', !url);
					if (url) {
						$('<img>', { src: url, alt: TarjetasLogo.alt }).appendTo(target);
					}
				}
			}

			root.find('[data-logo-select]').on('click', function () {
				if (!frame) {
					frame = wp.media({
						title: TarjetasLogo.title,
						button: { text: TarjetasLogo.button },
						library: { type: 'image' },
						multiple: false
					});
					frame.on('open', function () {
						var selection = frame.state().get('selection');
						var id = Number(input.val());
						selection.reset();
						if (id) {
							var attachment = wp.media.attachment(id);
							attachment.fetch();
							selection.add(attachment);
						}
					});
					frame.on('select', function () {
						var selected = frame.state().get('selection').first();
						if (!selected) {
							return;
						}
						var image = selected.toJSON();
						if (image.type !== 'image') {
							return;
						}
						var url = image.sizes && image.sizes.medium ? image.sizes.medium.url : image.url;
						update(Number(image.id), url, TarjetasLogo.changed);
					});
				}
				frame.open();
			});

			reset.on('click', function () {
				update(initialId, initialUrl, TarjetasLogo.restored);
			});
		});
	});
})(jQuery);
