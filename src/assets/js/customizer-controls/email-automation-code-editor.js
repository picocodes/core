(function ($) {

    var add_toolbar = function () {
        var title = $('.panel-title.site-title').text();
        var markup = [
            '<div class="mo-automation-code-toolbar">',
            '<div class="mo-automation-code-toolbar-center"><span class="mo-automation-code-title">' + title + '</span></div>',
            '<div class="mo-automation-code-toolbar-right">',
            '<a href="#" title="Preview" class="mo-automation-code-toolbar-btn preview">',
            '<i class="fa fa-eye"></i>',
            '<span class="text">Preview</span>',
            '</a>',
            '<a href="#" title="Code Editor" class="mo-automation-code-toolbar-btn code-editor btn-active">',
            '<i class="fa fa-code"></i>',
            'Code Editor',
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
        var session = editor.getSession();
        // disable syntax checker https://stackoverflow.com/a/13016089/2648410
        session.setUseWorker(false);
        editor.setTheme("ace/theme/monokai");
        session.setMode("ace/mode/html");
        editor.$blockScrolling = Infinity;
        var textarea_val = $("input[data-customize-setting-link*='[code_your_own]']").val();
        if (textarea_val) {
            session.setValue(textarea_val);
        }
        editor.on('change', function () {
            $("input[data-customize-setting-link*='[code_your_own]']").val(session.getValue()).change();
        });
    };

    var switch_view = function () {
        $(document).on('click', '.mo-automation-code-toolbar-btn', function (e) {
            e.preventDefault();
            $('.mo-automation-code-toolbar-btn').removeClass('btn-active');
            $(this).addClass('btn-active');
            if ($(this).hasClass('preview')) {
                wp.customize.previewer.refresh();
                $('.mo-email-automation-editor-wrap').hide();
                $('#customize-preview iframe').show();
            }
            else {
                $('#customize-preview iframe').hide();
                $('.mo-email-automation-editor-wrap').show();
            }
        });
    };

    $(window).on('load', function () {
        if (mailoptin_email_campaign_is_code_your_own === false) return;
        add_toolbar();
        add_ace_editor();
        switch_view();
    });
})(jQuery);