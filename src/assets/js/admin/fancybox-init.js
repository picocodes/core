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
    });
})(jQuery);