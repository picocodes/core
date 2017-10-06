(function ($) {
    $(window).on('load', function () {
        $('[data-block-type="ace"]').each(function () {
            var editor_id = $(this).attr('id');
            var theme = $(this).data('ace-theme');
            var language = $(this).data('ace-lang');

            var editor = ace.edit(editor_id);
            editor.setTheme("ace/theme/" + theme);
            editor.getSession().setMode("ace/mode/" + language);
            editor.getSession().setValue( $( 'textarea#' + editor_id + '-textarea' ).val() );

            editor.on('change', function () {
                $('textarea#' + editor_id + '-textarea').val(editor.getSession().getValue()).change();

            });

        });
    });

})(jQuery);