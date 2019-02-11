(function ($) {

    /**
     * @var {object} moEmailCodeEditor_strings
     */
    var add_toolbar = function () {
        var title = $('.panel-title.site-title').text();
        var markup = [
            '<div class="mo-automation-code-toolbar">',
            '<div class="mo-automation-code-toolbar-left">',
            '<a href="#" onclick="parent.wp.customize.section(\'mailoptin_campaign_view_tags\').focus()" class="mo-automation-code-view-tags">' + moEmailCodeEditor_strings.viewTags + ' <span class="dashicons dashicons-editor-help"></span></a>',
            '</div>',
            '<div class="mo-automation-code-toolbar-center"><span class="mo-automation-code-title">' + title + '</span></div>',
            '<div class="mo-automation-code-toolbar-right">',
            '<a href="#" class="mo-automation-code-toolbar-btn mo-preview">',
            '<i class="fa fa-eye"></i>',
            '<span class="text">' + moEmailCodeEditor_strings.previewBtn + '</span>',
            '</a>',
            '<a href="#" class="mo-automation-code-toolbar-btn mo-code-editor btn-active">',
            '<i class="fa fa-code"></i>',
            moEmailCodeEditor_strings.codeEditorBtn,
            '</a>',
            '</div>',
            '</div>'
        ].join('');

        $('#customize-preview iframe').before(markup);
    };

    var add_ace_editor = function () {
        var markup = [
            '<div class="mo-email-automation-editor-wrap">',
            '<div id="mo-email-automation-editor"></div>',
            '</div>'
        ].join('');

        $('#customize-preview iframe').before(markup).hide();

        var editor = ace.edit('mo-email-automation-editor');
        editor.setTheme("ace/theme/monokai");
        var session = editor.getSession();
        // disable syntax checker https://stackoverflow.com/a/13016089/2648410
        session.setUseWorker(false);
        session.setMode("ace/mode/html");
        editor.$blockScrolling = Infinity;
        var textarea_val = $("input[data-customize-setting-link*='[code_your_own]']").val();
        if (textarea_val) {
            session.setValue(textarea_val);
        }
        editor.on('change', function () {
            $("input[data-customize-setting-link*='[code_your_own]']").val(session.getValue()).change();
        });

        // remove the temporary iframe hide css.
        $('#customize-preview-iframe-hide').remove();
    };

    var switch_view = function () {
        $(document).on('click', '.mo-automation-code-toolbar-btn', function (e) {
            e.preventDefault();
            $('.mo-automation-code-toolbar-btn').removeClass('btn-active');
            $(this).addClass('btn-active');
            var cache = $('#customize-preview iframe');
            if ($(this).hasClass('mo-preview')) {
                wp.customize.previewer.refresh();
                var iframe = cache[0];
                iframe.contentWindow.document.open();
                iframe.contentWindow.document.write($("input[data-customize-setting-link*='[code_your_own]']").val());
                iframe.contentWindow.document.close();
                $('.mo-email-automation-editor-wrap').hide();
                cache.show();
            }
            else {
                cache.hide();
                $('.mo-email-automation-editor-wrap').show();
            }
        });
    };

    $(document).on('ready', function () {
        // hide on load
        var css = '<style id="customize-preview-iframe-hide" type="text/css">#customize-preview iframe {display:none;}</style>';
        $(document.head).append(css);
    });

    $(window).on('load', function () {
        if (mailoptin_email_campaign_is_code_your_own === false) return;
        add_toolbar();
        add_ace_editor();
        switch_view();
    });
})(jQuery);