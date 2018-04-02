// I converted to using windows.load because customizer ready event wasn't firing in email customizer.
jQuery(window).on('load', function () {
    jQuery('[data-block-type="ace"]').each(function () {
        var editor_id = jQuery(this).attr('id');
        var theme = jQuery(this).data('ace-theme');
        var language = jQuery(this).data('ace-lang');

        var editor = ace.edit(editor_id);
        editor.setTheme("ace/theme/" + theme);
        editor.getSession().setMode("ace/mode/" + language);
        editor.$blockScrolling = Infinity;
        editor.getSession().setValue(jQuery('textarea#' + editor_id + '-textarea').val());

        editor.on('change', function () {
            jQuery('textarea#' + editor_id + '-textarea').val(editor.getSession().getValue()).change();

        });

    });
});