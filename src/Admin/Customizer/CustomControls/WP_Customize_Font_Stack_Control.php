<?php
/**
 * Copyright (C) 2016  Agbonghama Collins <me@w3guy.com>
 */

namespace MailOptin\Core\Admin\Customizer\CustomControls;

/**
 * A class to create a dropdown for all google fonts
 */
class WP_Customize_Font_Stack_Control extends \WP_Customize_Control
{
    public function __construct($manager, $id, $args = array())
    {
        $this->count = isset($args['count']) ? $args['count'] : $this->count;
        parent::__construct($manager, $id, $args);
    }

    /**
     * Render the content of the category dropdown
     *
     * @return string
     */
    public function render_content()
    {
        $fonts = $this->get_font_stack();

        if ( ! empty($fonts)) {
            ?>
            <label>
                <span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
                <select <?php $this->link(); ?>>
                    <?php
                    foreach ($fonts as $font) {
                        printf('<option value="%s" %s>%s</option>', $font,
                            selected($this->value(), $font, false), $font);
                    }
                    ?>
                </select>
                <?php if ( ! empty($this->description)) : ?>
                    <span class="description customize-control-description"><?php echo $this->description; ?></span>
                <?php endif; ?>
            </label>
            <?php
        }
    }

    /**
     * Get the google fonts from the API or in the cache
     *
     * @return array
     */
    public function get_font_stack()
    {
        return array(
            "Arial, Helvetica, sans-serif",
            "Times New Roman, Times, serif",
            "Tahoma, Geneva, sans-serif",
            "Consolas, Lucida Console, monospace",
            "Futura, Century Gothic, sans-serif",
            "Franklin Gothic Medium, sans-serif",
            "Courier New, Courier, monospace",
            "Copperplate Light, serif",
            "Cambria, Georgia, Times, serif",
            "Georgia, Times New Roman, serif",
            "Corbel, Lucida Grande, sans-serif",
            "Century Gothic, Apple Gothic, sans-serif",
            "Trebuchet MS, Arial, sans-serif",
            "Verdana, Geneva, sans-serif",
            "Helvetica Neue, Helvetica, Helvetica, Arial, sans-serif",
            "Gill Sans, Calibri, sans-serif",
            "Segoe UI, Candara, Bitstream Vera Sans, Verdana",
            "Impact, Haettenschweiler, sans-serif",
            "Lucida Sans, Lucida Grande, sans-serif",
            "Garamond, Hoefler Text, serif",
            "Palatino Linotype, Book Antiqua, serif"
        );
    }
}