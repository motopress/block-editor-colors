(function ($) {

	$(function () {

		var colorsTiles = $('#bec-custom-colors');

		$('.bec-color-field').each(function () {
			var color_field = $(this),
				preview = color_field.closest('form').find('.bec-color-preview');

			color_field.wpColorPicker({
				change: function (e, ui) {
					setTimeout(function () {
						preview.css('background', ui.color);
					}, 100);
				}
			});
		});

		colorsTiles.sortable({
			items: "> .bec-color-tile:not(.bec-color-creator)",
			cancel: 'input,button',
			placeholder: 'bec-color-tile bec-color-tile-placeholder',
			cursor: 'grabbing',
			forcePlaceholderSize: true,
			helper: 'clone',
			revert: true,
			tolerance: 'pointer',
			start: function (event, ui) {
				colorsTiles.find('.bec-color-tile').removeClass('update-error');
				ui.item.addClass('move-start');
			},
			stop: function (event, ui) {
				ui.item.removeClass('move-start');
			},
			update: function (event, ui) {
				var colors = [];

				$(this).find('.bec-color-tile:not(.bec-color-creator)').each(function (index, item) {
					colors[index] = $(item).find('input[name="color_id"]').val()
				});

				$.ajax({
					type: "POST",
					url: BlockEditorColors.ajax_url,
					dataType: 'json',
					data: {
						nonce: BlockEditorColors.nonce,
						action: 'bec_update_color_order',
						colors: colors
					},
					beforeSend: function () {
						ui.item.addClass('updating');
					}
				}).done(function (data) {
					ui.item.removeClass('updating');
					if (!data.success) {
						ui.item.addClass('update-error');
					}
				});
			}
		});
	});

})(jQuery);