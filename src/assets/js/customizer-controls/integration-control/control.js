wp.customize.controlConstructor["mailoptin-integration"] = wp.customize.Control.extend({
    ready: function () {
        "use strict";
        $(document).on('click', '.mo-integration-widget-action', this.toggleWidget);
    },

    toggleWidget: function (e) {
        e.preventDefault();
        var parent = $(this).parents('.mo-integration-widget');
        $('.mo-integration-widget-content', parent).slideToggle(function () {
            parent.toggleClass('mo-integration-widget-expanded');
        });
    }
});