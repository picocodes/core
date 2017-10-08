<?php

namespace MailOptin\Core\Admin\Customizer\CustomControls;

use WP_Customize_Control;

class WP_Customize_Date_Picker_Control extends WP_Customize_Control
{
    public $type = 'mailoptin_range';

    public function enqueue()
    {
        wp_enqueue_script(
            'mailoptin-datetime-transition',
            MAILOPTIN_ASSETS_URL . 'js/customizer-controls/datetime-control/transition.js',
            array('jquery'),
            false,
            true
        );

        wp_enqueue_script(
            'mailoptin-datetime-collapse',
            MAILOPTIN_ASSETS_URL . 'js/customizer-controls/datetime-control/collapse.js',
            array('jquery'),
            false,
            true
        );

        wp_enqueue_script(
            'mailoptin-datetime-moment',
            MAILOPTIN_ASSETS_URL . 'js/customizer-controls/datetime-control/moment-with-locales.js',
            array('jquery'),
            false,
            true
        );

        wp_enqueue_script(
            'mailoptin-datetime-datetimepicker',
            MAILOPTIN_ASSETS_URL . 'js/customizer-controls/datetime-control/bootstrap-datetimepicker.min.js',
            array('jquery'),
            false,
            true
        );

        wp_enqueue_style(
            'mailoptin-bootstrap-datetimepicker-standalone',
            MAILOPTIN_ASSETS_URL . 'js/customizer-controls/datetime-control/bootstrap/css/bootstrap.css'
        );

        wp_enqueue_style(
            'mailoptin-bootstrap-datetimepicker',
            MAILOPTIN_ASSETS_URL . 'js/customizer-controls/datetime-control/bootstrap-datetimepicker.min.css',
            array('mailoptin-bootstrap-datetimepicker-standalone')
        );
    }

    public function render_content()
    {
        ?>
        <label>
            <span class="customize-date-picker-control"><?php echo esc_html($this->label); ?></span>
            <input type="text" id="mo-date-picker"/>
        </label>

        <script>
            console.log(typeof $);
            $(window).load(function () {
                $("#mo-date-picker").datetimepicker();
            });
        </script>
        <?php
    }
}