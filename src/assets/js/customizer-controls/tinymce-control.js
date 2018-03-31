( function( $ ) {

    var Tinymce_Customize_Control = {

        init: function() {
            $(window).on('load', function(){

                $('textarea.wp-editor-area').each(function(){
                    var tArea = $(this),
                        id = tArea.attr('id'),
                        editor = tinymce.get(id),
                        content;

                    if(editor) {
                        editor.on('keyup change undo redo SetContent', function () {
                            editor.save();
                            content = editor.getContent();
                            tArea.val(content).trigger('change');
                        });
                    }
                });
            });
        }
    };

    Tinymce_Customize_Control.init();

} )( jQuery );