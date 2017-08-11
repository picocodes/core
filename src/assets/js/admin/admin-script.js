(function ($) {
    // get field row to clone via data-repeatable-field attribute
    var field_row_to_clone_id = $('a.mo_add_repeatable').attr('data-repeatable-field');

    // add repeatable field group.
    $('a.mo_add_repeatable').click(function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        // eq(-1) is used to get the last repeatable field.
        var clone = $('tr.' + field_row_to_clone_id + '_fields_row').eq(-1).clone()
            // convert cloned copy to string. [0] is used because result could be an array (albeit unlikely here)
            [0].outerHTML
            // increment by 1, the index number in name attribute.
            .replace(/(.+\[.+\])\[(.+)\](\[.+\])/g, function (fullMatch, $1, $2, $3) {
                return $1 + '[' + (Number($2) + 1) + ']' + $3;
            })
            // increment by 1, the index number in 'data-index' attribute.
            .replace(/(data-index=")(.+)(")/g, function (fullMatch, $1, $2, $3) {
                return $1 + (Number($2) + 1) + $3;
            })
            // empty out the value
            .replace(/(value=")(.+)("\s)/g, '$1' + '' + '$3');

        var position = $(this).parents('tr').prev('.' + field_row_to_clone_id + '_fields_row');

        $(position).after(clone);
    });

    // remove repeatable field group
    $(document.body).on('click', '.mo_remove_repeatable', function (e) {
        e.preventDefault();

        if ($('tr.' + field_row_to_clone_id + '_fields_row').length === 1) return false;

        // get parent tr row and remove it.
        $(this).parent().parent().remove();
    });


    $('form#mo-clear-stat').submit(function (e) {
        e.stopImmediatePropagation();

        var response = confirm(mailoptin_globals.js_clear_stat_text);

        if (response === true) {
            HTMLFormElement.prototype.submit.call($(this).get(0));
            return false;
        }

        return false;
    });

}(jQuery));
