// I converted to using windows.load because customizer ready event wasn't firing in email customizer.
jQuery(window).on('load', function () {
    jQuery('[data-block-type="ace"]').each(function () {
        var editor_id = jQuery(this).attr('id');
        var theme = jQuery(this).data('ace-theme');
        var language = jQuery(this).data('ace-lang');

        var editor = ace.edit(editor_id);
        var session = editor.getSession();
        editor.setTheme("ace/theme/" + theme);
        session.setMode("ace/mode/" + language);
        editor.$blockScrolling = Infinity;
        session.setValue(jQuery('textarea#' + editor_id + '-textarea').val());

        editor.on('change', function () {
            jQuery('textarea#' + editor_id + '-textarea').val(session.getValue()).change();

        });

        // https://stackoverflow.com/a/39176259/2648410
        session.on("changeAnnotation", function () {
            var annotations = session.getAnnotations() || [], i = len = annotations.length;
            while (i--) {
                if (/doctype first\. Expected/.test(annotations[i].text)) {
                    annotations.splice(i, 1);
                }
                else if (/Unexpected End of file\. Expected/.test(annotations[i].text)) {
                    annotations.splice(i, 1);
                }
            }
            if (len > annotations.length) {
                session.setAnnotations(annotations);
            }
        });

    });
});