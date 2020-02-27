(function ($) {

    $(function () {
        $('.cec-color-field-edit').wpColorPicker({
            change: function (e, ui) {
                var preview = $(this).closest('form').find('.color-preview');
                preview.css('background', ui.color);
            }
        });
    });

})(jQuery);