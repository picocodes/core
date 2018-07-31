<?php

namespace MailOptin\Core\EmailCampaigns\PostsEmailDigest;

use Carbon\Carbon;
use MailOptin\Core\Connections\ConnectionFactory;
use MailOptin\Core\EmailCampaigns\AbstractTriggers;
use MailOptin\Core\Repositories\EmailCampaignRepository as ER;
use MailOptin\Core\Repositories\EmailCampaignRepository;
use WP_Post;

class PostsEmailDigest extends AbstractTriggers
{
    public function __construct()
    {
        parent::__construct();

//        add_action('init', [$this, 'run_job']);
//        add_action('mo_hourly_recurring_job', [$this, 'run_job']);
    }

    public function run_job()
    {
        $postDigests = EmailCampaignRepository::get_by_email_campaign_type(ER::POSTS_EMAIL_DIGEST);

        if (empty($postDigestAutomations)) return;

        foreach ($postDigests as $postDigest) {

            $email_campaign_id = absint($postDigest['id']);
            if (ER::is_campaign_active($email_campaign_id) === false) continue;

            $schedule_interval = ER::get_merged_customizer_value($email_campaign_id, 'schedule_interval');
            $schedule_time = absint(ER::get_merged_customizer_value($email_campaign_id, 'schedule_time'));
            $schedule_day = absint(ER::get_merged_customizer_value($email_campaign_id, 'schedule_day'));
            $schedule_month_date = absint(ER::get_merged_customizer_value($email_campaign_id, 'schedule_month_date'));

            $timezone = get_option('timezone_string');
            if (empty($timezone)) {
                $timezone = get_option('gmt_offset');
            }

            $carbon_now = Carbon::now($timezone);
            $carbon_today = Carbon::today($timezone);


            switch ($schedule_interval) {
                case 'every_day':
                    $schedule_timestamp = Carbon::createFromTime($schedule_time, 0, 0, $timezone);
                    if ($schedule_timestamp->lessThanOrEqualTo($carbon_now)) {
                        // send email
                    }
                    break;
                case 'every_week':
                    if ($carbon_today->isDayOfWeek($schedule_day) && $carbon_today->hour($schedule_time)->lessThanOrEqualTo($carbon_now)) {
                        // send email
                    }
                    break;
                case 'every_month':
                    if ($carbon_now->day == $schedule_month_date && $carbon_today->hour($schedule_time)->lessThanOrEqualTo($carbon_now)) {
                        // send email
                    }
                    break;
            }

            $email_subject = ER::get_merged_customizer_value($email_campaign_id, 'email_campaign_subject');

            $content_html = (new Templatify($post, $email_campaign_id))->forge();

            $campaign_id = $this->save_campaign_log(
                $email_campaign_id,
                self::format_campaign_subject($email_subject, $post),
                $content_html
            );

            $this->send_campaign($email_campaign_id, $campaign_id);
        }
    }

    public function send_email()
    {

    }

    /**
     * Does the actual campaign sending.
     *
     * @param int $email_campaign_id
     * @param int $campaign_log_id
     */
    public function send_campaign($email_campaign_id, $campaign_log_id)
    {
        $campaign = $this->CampaignLogRepository->getById($campaign_log_id);
        $connection_service = $this->connection_service($email_campaign_id);

        $connection_instance = ConnectionFactory::make($connection_service);

        $response = $connection_instance->send_newsletter(
            $email_campaign_id,
            $campaign_log_id,
            $campaign->title,
            $connection_instance->replace_placeholder_tags($campaign->content_html, 'html'),
            $connection_instance->replace_placeholder_tags($campaign->content_text, 'text')
        );

        if (isset($response['success']) && (true === $response['success'])) {
            $this->update_campaign_status($campaign_log_id, 'processed');
        } else {
            $this->update_campaign_status($campaign_log_id, 'failed');
        }
    }

    /**
     * Replace any placeholder in email subject to correct value.
     *
     * @param string $email_subject
     * @param \stdClass|WP_Post $data_source
     *
     * @return mixed
     */
    public static function format_campaign_subject($email_subject, $data_source)
    {
        $search = ['{{title}}'];
        $replace = [$data_source->post_title];
        return str_replace($search, $replace, $email_subject);
    }

    /**
     * Singleton.
     *
     * @return PostsEmailDigest
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