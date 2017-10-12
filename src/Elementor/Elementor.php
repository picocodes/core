<?php

namespace MailOptin\Core\Elementor;

use Elementor\Controls_Manager;
use ElementorPro\Modules\Forms\Classes\Action_Base;
use MailOptin\Core\AjaxHandler;
use MailOptin\Core\OptinForms\ConversionDataBuilder;
use MailOptin\Core\Repositories\ConnectionsRepository;

class Elementor extends Action_Base
{
    public function get_label()
    {
        return __('MailOptin', 'mailoptin');
    }

    public function get_name()
    {
        return 'mailoptin';
    }

    public function on_export($element)
    {
        unset($element['settings']['mailoptin_connection']);

        return $element;
    }

    public function email_service_providers()
    {
        $connections = ConnectionsRepository::get_connections();

        return $connections;
    }

    public function email_providers_and_lists()
    {
        $data = [];

        foreach ($this->email_service_providers() as $key => $value) {
            $data[$value] = ConnectionsRepository::connection_email_list($key);
        }

        return $data;
    }

    public function register_settings_section($widget)
    {
        $widget->start_controls_section(
            'section_mailoptin',
            [
                'label' => __('MailOptin', 'mailoptin'),
                'condition' => [
                    'submit_actions' => $this->get_name(),
                ],
            ]
        );

        $widget->add_control(
            'mailoptin_connection',
            [
                'label' => __('Select Email Service', 'mailoptin'),
                'type' => Controls_Manager::SELECT,
                'options' => $this->email_service_providers()
            ]
        );

        $widget->add_control(
            'mailoptin_connection_list',
            [
                'label' => __('Select Email List', 'mailoptin'),
                'type' => 'moselect',
                'options' => $this->email_providers_and_lists()
            ]
        );

        $widget->end_controls_section();
    }

    public function run($record, $ajax_handler)
    {
        $connection_service = $record->get_form_settings('mailoptin_connection');
        $connection_email_list = $record->get_form_settings('mailoptin_connection_list');
        $POSTed_data = $record->get('sent_data');
        $full_name = isset($POSTed_data['mo_name']) ? $POSTed_data['mo_name'] : $POSTed_data['name'];
        $first_name = isset($POSTed_data['mo_first_name']) ? $POSTed_data['mo_first_name'] : $POSTed_data['first_name'];
        $last_name = isset($POSTed_data['mo_last_name']) ? $POSTed_data['mo_last_name'] : $POSTed_data['last_name'];

        $name = !empty($first_name) || !empty($last_name) ? $first_name . ' ' . $last_name : $full_name;
        $email = isset($POSTed_data['mo_email']) ? $POSTed_data['mo_email'] : $POSTed_data['email'];

        $optin_data = new ConversionDataBuilder();
        $optin_data->payload = $POSTed_data;
        $optin_data->name = $name;
        $optin_data->email = $email;
        $optin_data->optin_campaign_type = __('Elementor Form', 'mailoptin');
        $optin_data->connection_service = $connection_service;
        $optin_data->connection_email_list = $connection_email_list;
        $optin_data->is_timestamp_check_active = false;
        $optin_data->conversion_page = esc_url_raw($_REQUEST['referrer']);
        $optin_data->user_agent = esc_html($_SERVER['HTTP_USER_AGENT']);

        $response = AjaxHandler::do_optin_conversion($optin_data);

        if ($response['success'] === false) {
            $ajax_handler->add_error_message($response['message']);
            return;
        }

        $ajax_handler->set_success(true);
    }

    /**
     * Singleton poop.
     *
     * @return Elementor|null
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