<?php

namespace MailOptin\Core\Admin\Customizer\CustomControls;

use WP_Customize_Control;

/**
 * Customize Color Control class.
 *
 */
class WP_Customize_MO_Color_Control extends WP_Customize_Control
{
    /**
     * Type.
     *
     * @var string
     */
    public $type = 'mo_color_control';

    /**
     * Statuses.
     *
     * @var array
     */
    public $statuses;

    /**
     * Mode.
     *
     * @since 4.7.0
     * @var string
     */
    public $mode = 'full';

    /**
     * Constructor.
     *
     * @since 3.4.0
     * @uses WP_Customize_Control::__construct()
     *
     * @param \WP_Customize_Manager $manager Customizer bootstrap instance.
     * @param string $id Control ID.
     * @param array $args Optional. Arguments to override class property defaults.
     */
    public function __construct($manager, $id, $args = array())
    {
        $this->statuses = array('' => __('Default'));
        parent::__construct($manager, $id, $args);
    }

    /**
     * Enqueue scripts/styles for the color picker.
     *
     * @since 3.4.0
     */
    public function enqueue()
    {
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_style('wp-color-picker');
    }

    /**
     * Don't render the control content from PHP, as it's rendered via JS on load.
     *
     * @since 3.4.0
     */
    public function render_content()
    {
        $defaultValue = '#RRGGBB';
        $defaultValueAttr = '';

        $isHueSlider = $this->mode === 'hue';

        if ($this->setting->default && is_string($this->setting->default) && !$isHueSlider) {
            if ('#' !== substr($this->setting->default, 0, 1)) {
                $defaultValue = '#' . $this->setting->default;
            } else {
                $defaultValue = $this->setting->default;
            }
            $defaultValueAttr = " data-default-color=\"$defaultValue\""; // Quotes added automatically.
        }

        if ($this->label) {
            echo '<span class="customize-control-title">' . $this->label . '</span>';
        }
        if ($this->description) {
            echo '<span class="description customize-control-description">' . $this->description . '</span>';
        }

        echo '<div class="customize-control-content">';
        echo '<label><span class="screen-reader-text">' . $this->label . '</span>';
        if ($isHueSlider) {
            echo '<input class="mo-color-picker-hue" type="text" data-type="hue" />';
        } else {
            echo '<input class="mo-color-picker-hex" type="text" maxlength="7" placeholder="' . $defaultValue . '"' . $defaultValueAttr . '/>';
        }
        echo '</label>';
        echo '</div>';
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function(){
                jQuery('.mo-color-picker-hex').wpColorPicker();
            });
        </script>
        <?php
    }
}
