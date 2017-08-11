( function( $ ) {

    var Tinymce_Customize_Control = {

        init: function() {
            $(window).on('load', function(){

                $('textarea.wp-editor-area').each(function(){
                    var tArea = $(this),
                        id = tArea.attr('id'),
                        editor = tinyMCE.get(id),
                        setChange,
                        content;

                    if(editor) {
                        editor.on('change', function (e) {
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