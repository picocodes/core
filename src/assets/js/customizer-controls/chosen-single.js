wp.customize.controlConstructor.mailoptin_single_chosen = wp.customize.Control.extend({
    ready: function () {
        console.log('i am here');
        var control = this;
        wp.customize.Control.prototype.ready.call(control);

        $('.mailoptin-single-chosen').each(function () {
            var options = $(this).data('chosen-attr');
            console.log(options);
            $(this).chosen(options);
        });
    }
});