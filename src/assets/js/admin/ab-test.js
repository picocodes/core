(function ($) {

    var open_split_test_form = function (e, parent_optin_id) {
        e.preventDefault();
        // remove active popover
        $('.mo-ellipsis-tooltipster').tooltipster('close');
        $.fancybox.open({
            href: '#mo-optin-add-split',
            type: 'inline',
            padding: 0
        });

        $('#mo-split-parent-id').val(parent_optin_id);
    };

    $(document.body).on('click', '.mo-split-test', function (e) {
        var parent_optin_id = $(this).data('optin-id');
        open_split_test_form(e, parent_optin_id);
    });

    $(document.body).on('click', '.mo-split-test-add-variant', function (e) {
        var parent_optin_id = $(this).data('parent-optin-id');
        open_split_test_form(e, parent_optin_id);
    });

    $(document.body).on('click', '#mo-split-submit', function (e) {
        e.preventDefault();
        var _this = this;

        var variant_name_obj = $('#mo-variant-name');
        var split_note_obj = $('#mo-split-notes');

        var variant_name = variant_name_obj.val();
        var split_note = split_note_obj.val();

        var isEmpty = function (str) {
            return (str.length === 0 || !str.trim());
        };

        if (isEmpty(variant_name)) {
            variant_name_obj.addClass('mailoptin-input-error');
        }
        else {
            variant_name_obj.removeClass('mailoptin-input-error');
        }

        if (isEmpty(split_note)) {
            split_note_obj.addClass('mailoptin-input-error');
        }
        else {
            split_note_obj.removeClass('mailoptin-input-error');
        }

        if (isEmpty(variant_name) || isEmpty(split_note)) return;

        $(_this).prop("disabled", true);
        $('#mo-split-submit-error').hide();
        $('#mo-split-submit-spinner').show();

        $.post(ajaxurl, {
            action: 'mailoptin_create_optin_split_test',
            variant_name: variant_name,
            split_note: split_note,
            parent_optin_id: $('#mo-split-parent-id').val(),
            nonce: mailoptin_globals.nonce
        }, function (response) {
            if ('success' in response && response.success === true && typeof response.data.redirect !== 'undefined') {
                window.location.assign(response.data.redirect);
            }
            else {
                $(_this).prop("disabled", false);
                $('#mo-split-submit-error').show().html(response.data);
                $('#mo-split-submit-spinner').hide();
            }
        });

    });

    // handle click of A/B test pause button
    $('.mo-split-test-pause').click(function (e) {
        e.preventDefault();
        var _this = this;
        var parent_optin_id = $(this).data('parent-id');
        var split_test_action = $(this).data('split-test-action');

        console.log(parent_optin_id);
        $(_this).next('#mo-split-pause-spinner').show();

        $.post(ajaxurl, {
            action: 'mailoptin_pause_optin_split_test',
            split_test_action: split_test_action,
            parent_optin_id: parent_optin_id,
            nonce: mailoptin_globals.nonce
        }, function (response) {
            if ('success' in response && response.success === true) {
                $(_this).next('.mo-split-test-pause').text(mo_mailoptin_js_globals['split_test_' + split_test_action + '_label']);
            }
            $(_this).next('#mo-split-pause-spinner').hide();
        });
    });


    // handle click of A/B flag

}(jQuery));
