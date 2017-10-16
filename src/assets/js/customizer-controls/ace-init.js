wp.customize.controlConstructor.mailoptin_ace_editor = wp.customize.Control.extend( {
    ready: function() {
        var control = this;
        wp.customize.Control.prototype.ready.call( control );

        jQuery('[data-block-type="ace"]').each(function () {
            var editor_id = jQuery(this).attr('id');
            var theme = jQuery(this).data('ace-theme');
            var language = jQuery(this).data('ace-lang');

            var editor = ace.edit(editor_id);
            editor.setTheme("ace/theme/" + theme);
            editor.getSession().setMode("ace/mode/" + language);
            editor.$blockScrolling = Infinity;
            editor.getSession().setValue( jQuery( 'textarea#' + editor_id + '-textarea' ).val() );

            editor.on('change', function () {
                jQuery('textarea#' + editor_id + '-textarea').val(editor.getSession().getValue()).change();

            });

        });
    }
} );