jQuery(window).on('load', function () {
    var control = this,
        isHueSlider = this.params.mode === 'hue',
        updating = false,
        picker;

    if (isHueSlider) {
        picker = this.container.find('.color-picker-hue');
        picker.val(control.setting()).wpColorPicker({
            change: function (event, ui) {
                updating = true;
                control.setting(ui.color.h());
                updating = false;
            }
        });
    } else {
        picker = this.container.find('.color-picker-hex');
        picker.val(control.setting()).wpColorPicker({
            change: function () {
                updating = true;
                control.setting.set(picker.wpColorPicker('color'));
                updating = false;
            },
            clear: function () {
                updating = true;
                control.setting.set('');
                updating = false;
            }
        });
    }

    control.setting.bind(function (value) {
        // Bail if the update came from the control itself.
        if (updating) {
            return;
        }
        picker.val(value);
        picker.wpColorPicker('color', value);
    });

    // Collapse color picker when hitting Esc instead of collapsing the current section.
    control.container.on('keydown', function (event) {
        var pickerContainer;
        if (27 !== event.which) { // Esc.
            return;
        }
        pickerContainer = control.container.find('.wp-picker-container');
        if (pickerContainer.hasClass('wp-picker-active')) {
            picker.wpColorPicker('close');
            control.container.find('.wp-color-result').focus();
            event.stopPropagation(); // Prevent section from being collapsed.
        }
    });
});