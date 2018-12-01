(function (api, $) {
    wp.customize.controlConstructor["mailoptin-fields"] = wp.customize.Control.extend({

        ready: function () {
            "use strict";

            var _this = this;

            var contextual_display_init = function () {

                $('.mo-fields-widget.mo-custom-field').each(function (index) {
                    // re-order index
                    $(this).attr('data-field-index', index);

                    //index start at 0. Increment so it start from 1. Useful only for Field h3/title.
                    // I didnt do ++index because i dont want the new index copy to index variable.
                    $(this).find('.mo-fields-widget-title h3').text(mailoptin_globals.custom_field_label.replace('{ID}', index + 1));
                    _this.color_picker_init();
                    _this.chosen_select_init();
                });
            };

            var add_new_field = function (e) {
                e.preventDefault();
                var index = 0;
                var preceding_index = $('.mo-fields-widget').eq(-1).data('field-index');
                if (typeof preceding_index === 'number' && isNaN(preceding_index) === false) {
                    index = preceding_index + 1;
                }

                var template = wp.template('mo-fields-js-template');
                // replace index placeholder with actual value.
                var template_structure = template().replace(/{mo-fields-index}/g, index);
                $(template_structure).insertBefore('.mo-fields__add_new').addClass('mo-fields-widget-expanded').attr('data-field-index', index);
                contextual_display_init();

                // search and replace ID of fields
                $(this).parents('.mo-fields-block').attr('data-field-index', index);
            };


            var toggleAllWidget = function (e) {
                e.preventDefault();
                var $button = $(this);

                $('.mo-fields-widget').each(function () {
                    var parent = $(this);
                    if ($button.hasClass('mo-expand')) {
                        $('.mo-fields-widget-content', parent).slideDown(function () {
                            parent.addClass('mo-fields-widget-expanded');
                        });

                    } else {
                        $('.mo-fields-widget-content', parent).slideUp(function () {
                            parent.removeClass('mo-fields-widget-expanded');
                        });
                    }
                });

                if ($button.hasClass('mo-expand')) {
                    $button.text($button.data('collapse-text')).removeClass('mo-expand').addClass('mo-collapse');
                } else {
                    $button.text($button.data('expand-text')).removeClass('mo-collapse').addClass('mo-expand');
                }
            };
            
            contextual_display_init();
            $(document).on('click', '.mo-fields-expand-collapse-all', toggleAllWidget);
            $(document).on('click', '.mo-fields-widget-action', this.toggleWidget);
            $(document).on('click', '.mo-add-new-field', add_new_field);
            $(document).on('click', '.mo-fields-delete', this.remove_field);
            $(document).on('change keyup', '.mo-fields-widget.mo-custom-field select, .mo-fields-widget.mo-custom-field input, .mo-fields-widget.mo-custom-field textarea', this.save_changes);
        },

        save_changes: function () {
            var data_store = $('.mo-fields-save-field');

            var old_data = data_store.val();
            if (old_data === '' || typeof old_data === 'undefined') {
                old_data = [];
            }
            else {
                old_data = JSON.parse(old_data);
            }

            var parent = $(this).parents('.mo-fields-widget.mo-custom-field');
            var index = parent.attr('data-field-index');
            if (typeof old_data[index] === 'undefined') {
                old_data[index] = {};
            }

            var field_name = this.name;
            var field_value = this.value;

            // returning true continue/skip the iteration.
            if (field_name === '') return;

            // shim for single checkbox
            if ($(this).attr('type') === 'checkbox' && field_name.indexOf('[]') === -1) {
                old_data[index][field_name] = this.checked;
            }
            else if ($(this).attr('type') === 'checkbox' && field_name.indexOf('[]') !== -1) {
                var item_name = field_name.replace('[]', '');
                if (this.checked === true) {
                    old_data = _.without(old_data[index][item_name], field_value);
                }
                else {

                    if (typeof old_data[index][item_name] === 'undefined') {
                        old_data[index][item_name] = [];
                        old_data[index][item_name].push(field_value);
                    } else {
                        old_data[index][item_name].push(field_value);
                    }

                    old_data[index][item_name] = _.uniq(old_data[index][item_name]);
                }
            }
            else if (this.tagName === 'SELECT' && $(this).hasClass('mailoptin-field-chosen')) {
                old_data[index][field_name] = $(this).val();
            }
            else {
                old_data[index][field_name] = field_value;
            }

            // remove null and empty from array elements.
            old_data = _.without(old_data, null, '');

            data_store.val(JSON.stringify(old_data)).trigger('change');
        },

        toggleWidget: function (e) {
            e.preventDefault();
            var parent = $(this).parents('.mo-fields-widget');
            $('.mo-fields-widget-content', parent).slideToggle(function () {
                parent.toggleClass('mo-fields-widget-expanded');
            });
        },

        remove_field: function (e) {
            e.preventDefault();
            var cache = $('.mo-fields-widget.mo-custom-field');
            var fields_count = cache.length;

            var parent = $(this).parents('.mo-fields-widget.mo-custom-field');
            parent.slideUp(400, function () {
                $(this).remove();
                var index = parent.data('field-index');
                var data_store = $('.mo-fields-save-field');
                var old_data = JSON.parse(data_store.val());
                // remove field by index. see https://stackoverflow.com/a/1345122/2648410
                old_data.splice(index, 1);
                // remove null and empty from array elements.
                old_data = _.without(old_data, null, '');
                // store the data
                data_store.val(JSON.stringify(old_data)).trigger('change');
                // re-order index
                $('.mo-fields-widget.mo-custom-field').each(function (index) {
                    $(this).attr('data-field-index', index);
                });
            });
        },

        color_picker_init: function () {
            $('.mo-color-picker-hex').wpColorPicker({
                change: function () {
                    $(this).val($(this).wpColorPicker('color')).change();
                },
                clear: function () {
                    $(this).val('').change();
                }
            });
        },

        chosen_select_init: function () {
            $('.mailoptin-field-chosen').chosen({
                width: "100%"
            });
        },
    });

})(wp.customize, jQuery);