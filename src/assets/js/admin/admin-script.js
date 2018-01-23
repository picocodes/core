(function ($) {
    // get field row to clone via data-repeatable-field attribute
    var mo_repeatable_cache = $('a.mo_add_repeatable');
    var field_row_to_clone_id = mo_repeatable_cache.attr('data-repeatable-field');

    // add repeatable field group.
    mo_repeatable_cache.click(function (e) {
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

    $('#mo-metabox-collapse').click(function (e) {
        e.preventDefault();
        $('#post-body-content').find('div.postbox').addClass('closed');
    });

    $('#mo-metabox-expand').click(function (e) {
        e.preventDefault();
        $('#post-body-content').find('div.postbox').removeClass('closed');
    });

    // handles activation and deactivation of optin
    $('.mo-optin-activate-switch').on('change', function () {
        var _this = this;
        $.post(ajaxurl, {
            action: 'mailoptin_toggle_optin_activated',
            id: $(_this).data('mo-optin-id'),
            status: _this.checked
        }, function () {
            // trigger act on activation immediately.
            $.post(ajaxurl, {action: 'mailoptin_act_on_toggle_optin_activated'});
        });
    });

    // handles activation and deactivation of email campaigns
    $('.mo-automation-activate-switch').on('change', function () {
        var _this = this;
        $.post(ajaxurl, {
            action: 'mailoptin_toggle_automation_activated',
            id: $(_this).data('mo-optin-id'),
            status: _this.checked
        }, function () {
            // trigger act on activation immediately.
            $.post(ajaxurl, {action: 'mailoptin_act_on_toggle_automation_activated'});
        });
    });

    // handle sidebar nav tag menu.
    $(function () {
        // Switches option sections
        $('.mailoptin-group-wrapper').hide();
        var active_tab = '';

        if (typeof(localStorage) !== 'undefined') {
            active_tab = localStorage.getItem("active-tab");
        }
        if (active_tab !== '' && $(active_tab).length) {
            $(active_tab).fadeIn();
        } else {
            $('.mailoptin-group-wrapper:first').fadeIn();
        }

        if (active_tab !== '' && $(active_tab + '-tab').length) {
            $(active_tab + '-tab').addClass('nav-tab-active');
        }
        else {
            $('.mailoptin-settings-wrap .nav-tab-wrapper a:first').addClass('nav-tab-active');
        }

        $('.mailoptin-settings-wrap .nav-tab-wrapper a').click(function (e) {
            $('.mailoptin-settings-wrap .nav-tab-wrapper a').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active').blur();
            var clicked_group = $(this).attr('href');
            if (typeof(localStorage) !== 'undefined') {
                localStorage.setItem("active-tab", $(this).attr('href'));
            }
            $('.mailoptin-group-wrapper').hide();
            $(clicked_group).fadeIn();
            e.preventDefault();
        });
    });

}(jQuery));
