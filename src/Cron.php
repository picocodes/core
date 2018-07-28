<?php

namespace MailOptin\Core;


use Carbon\Carbon;

class Cron
{
    public function __construct()
    {
        add_action('init', [$this, 'create_recurring_schedule']);
        add_action('mo_hourly_recurring_job', [$this, 'run_job']);
    }

    public function create_recurring_schedule()
    {
        $tz = Carbon::now(0)->endOfHour()->timestamp;

        if (!wp_next_scheduled('mo_hourly_recurring_job')) {
            wp_schedule_event($tz, 'hourly', 'mo_hourly_recurring_job');
        }
    }

    public function run_job()
    {
        $timezone = get_option('timezone_string');
        if (empty($timezone)) {
            $timezone = get_option('gmt_offset');
        }



        error_log('cronzz123' . "\r\n", 3, WP_CONTENT_DIR . '/plugins/mo-text.txt');
    }

    /**
     * Singleton.
     *
     * @return Cron
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