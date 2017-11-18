<?php

namespace MailOptin\Core\Admin\Customizer\CustomControls;

use WP_Customize_Control;

class WP_Customize_Custom_Content extends WP_Customize_Control
{
    // Whitelist content parameter
    public $content = '';

    /**
     * Render the control's content.
     *
     * Allows the content to be overridden without having to rewrite the wrapper.
     *
     * @since   1.0.0
     * @return  void
     */
    public function render_content()
    {
        if (isset($this->label)) {
            echo '<span class="customize-control-title">' . $this->label . '</span>';
        }
        if (isset($this->content)) {
            echo '<div class="customize-mo-custom-content-block">' . $this->content . '</div>';
        }
        if (isset($this->description)) {
            echo '<span class="description customize-control-description">' . $this->description . '</span>';
        }
    }
}