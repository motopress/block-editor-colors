(function ($) {

    $(function () {
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
        })
    });

})(jQuery);