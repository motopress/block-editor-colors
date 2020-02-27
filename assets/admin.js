(function ($) {

    $(function () {
        $('.cec-color-field-edit').wpColorPicker({
            change: function (e) {
                var preview = $(this).closest('form').find('.color-preview');

                if(preview){
                    preview.css('background', this.value);
                }
            }
        });
    });

})(jQuery);