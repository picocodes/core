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

    $(window).on('load', function () {
        add_toolbar();
    });
})(jQuery);