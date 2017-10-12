<?php

namespace MailOptin\Core\Elementor;

use Elementor\Controls_Manager;
use ElementorPro\Modules\Forms\Module;

class Init
{
    public function __construct()
    {
        add_action('elementor_pro/init', [$this, 'load_integration']);
        add_action('elementor/controls/controls_registered', [$this, 'register_custom_control']);
    }

    public function load_integration()
    {
        Module::instance()->add_form_action('mailoptin', new Elementor());
    }

    public function register_custom_control(Controls_Manager $control_manager)
    {
        $control_manager->register_control('moselect', new CustomSelect());
    }

    /**
     * Singleton poop.
     *
     * @return Init|null
     */
    public static function get_instance()
    {
        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}