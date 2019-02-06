(function ($) {

    var add_toolbar = function () {
        var title = $('.panel-title.site-title').text();
        var markup = [
            '<div class="mo-automation-code-toolbar">',
            '<div class="mo-automation-code-toolbar-center"><span class="mo-automation-code-title">' + title + '</span></div>',
            '<div class="mo-automation-code-toolbar-right">',
            '<a href="#" title="Preview" class="mo-automation-code-toolbar-btn">',
            '<i class="fa fa-eye"></i>',
            '<span class="text">Preview</span>',
            '</a>',
            '<a href="#" title="Code Editor" class="mo-automation-code-toolbar-btn  btn-active">',
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
            '<textarea id="mo-email-automation-editor" style="display: none"></textarea>',
            '</div>'
        ].join('');

        $('#customize-preview iframe').before(markup);

        var editor = ace.edit('mo-email-automation-editor');
        var session = editor.getSession();
        // disable syntax checker https://stackoverflow.com/a/13016089/2648410
        session.setUseWorker(false);
        editor.setTheme("ace/theme/monokai");
        session.setMode("ace/mode/html");
        editor.$blockScrolling = Infinity;
        var textarea_val = $('#mo-email-automation-editor').val();
        if (textarea_val) {
            session.setValue(textarea_val);
        }
        editor.on('change', function () {
            console.log($("input[data-customize-setting-link*='[code_your_own]']"));
            $("input[data-customize-setting-link*='[code_your_own]']").val(session.getValue()).change();
        });
    };

    $(window).on('load', function () {
        add_toolbar();
        add_ace_editor();
    });
})(jQuery);