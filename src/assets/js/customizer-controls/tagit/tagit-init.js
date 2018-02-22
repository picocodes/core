wp.customize.controlConstructor.mailoptin_tagit = wp.customize.Control.extend({
    ready: function () {
        var control = this;

        jQuery('[data-block-type="tagit"]').each(function () {
            var id = jQuery(this).attr('id');
            var options = jQuery(this).data('tagit-options');
            var value = [];

            // var editor = ace.edit(editor_id);
            // editor.setTheme("ace/theme/" + theme);
            // editor.getSession().setMode("ace/mode/" + language);
            // editor.$blockScrolling = Infinity;
            // editor.getSession().setValue(jQuery('textarea#' + editor_id + '-textarea').val());
            //
            // editor.on('change', function () {
            //     jQuery('textarea#' + editor_id + '-textarea').val(editor.getSession().getValue()).change();
            //
            // });


            options.afterTagAdded = function(event, ui) {
                // do something special
                console.log(ui.tagLabel);
                value.push(ui.tagLabel);

                control.setting.set(value)
            };

            jQuery("#" + id).tagit(options);

        });
    }
});