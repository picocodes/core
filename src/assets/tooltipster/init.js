(function ($) {
    $(document).on('ready', function () {
        $('.mo-tooltipster').tooltipster({
            theme: 'tooltipster-borderless'
        });

        $('.mo-ellipsis-tooltipster').tooltipster({
            functionInit: function (instance, helper) {
                var content = $(helper.origin).parent('.mo-actions-popover-wrapper').find('.mo-actions-popover-content');
                instance.content(content);
            },
            theme: 'tooltipster-light',
            trigger: 'click',
            side: 'right',
            contentAsHTML: true,
            interactive: true,
            contentCloning: true
        });
    });

})(jQuery);