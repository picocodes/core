(function ($) {
    $(document).ready(function () {
        $(".campaign-preview").click(function (e) {
            e.preventDefault();
            $.fancybox.open({
                href: $(this).attr("href"),
                type: 'iframe',
                padding: 0
            });
        });

        $(document.body).on('click', '.mo-split-test', function (e) {
            e.preventDefault();
            // remove active popover
            $('.mo-ellipsis-tooltipster').tooltipster('close');
            $.fancybox.open({
                href: '#mo-optin-add-split',
                type: 'inline',
                padding: 0
            });

            var parent_optin_id = $(this).data('optin-id');
            $('#mo-split-parent-id').val(parent_optin_id);
        });
    });
})(jQuery);