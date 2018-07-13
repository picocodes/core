(function ($) {

    wp.customize.bind('ready', function () {
        var editor_open_flag = false;

        $(document).on('click', '.mo-tinymce-expanded-editor-btn', function (e) {
            e.preventDefault();
            var content_editor,
                editor_button = $(this);
            var editor_button_icon = editor_button.find('span.dashicons');
            var editor_button_id = this.id;

            editor_open_flag = !editor_open_flag;

            if (editor_open_flag === true) {

                editor_button.addClass('editor-open');

                content_editor = '<div class="mo-tinymce-expanded-editor"><textarea style="height: 300px" id="mo-tinymce-expanded-textarea">hello</textarea></div>';
                $('.wp-full-overlay').prepend(content_editor);
                $('#mo-tinymce-expanded-textarea').mo_wp_editor({mode: 'tmce'});

                editor_button_icon.removeClass('dashicons-edit').addClass('dashicons-hidden');
                editor_button.find('span:not(.dashicons)').text(moTinyMceExpandedEditor.button_close_text);
            } else {
                editor_button.removeClass('editor-open');
                editor_button_icon.removeClass('dashicons-hidden').addClass('dashicons-edit');
                editor_button.find('span:not(.dashicons)').text(moTinyMceExpandedEditor.button_open_text);

                $('.mo-tinymce-expanded-editor').remove();
            }
        })
    });
})(jQuery);