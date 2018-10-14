<?php

namespace MailOptin\Core\EmailCampaigns\PostsEmailDigest;

use Carbon\Carbon;
use MailOptin\Core\Connections\ConnectionFactory;
use MailOptin\Core\EmailCampaigns\AbstractTriggers;
use MailOptin\Core\Repositories\EmailCampaignMeta;
use MailOptin\Core\Repositories\EmailCampaignRepository;
use MailOptin\Core\Repositories\EmailCampaignRepository as ER;

class PostsEmailDigest extends AbstractTriggers
{
    public function __construct()
    {
        parent::__construct();

        add_action('mo_hourly_recurring_job', [$this, 'run_job']);
    }

    public function post_collection($email_campaign_id)
    {
        $item_count = EmailCampaignRepository::get_merged_customizer_value($email_campaign_id, 'item_number');

        $newer_than_timestamp = EmailCampaignMeta::get_meta_data($email_campaign_id, 'created_at', true);

        $last_processed_at = EmailCampaignMeta::get_meta_data($email_campaign_id, 'last_processed_at', true);

        if ( ! empty($last_processed_at)) {
            $newer_than_timestamp = $last_processed_at;
        }

        $parameters = [
            'posts_per_page' => $item_count,
            'post_status'    => 'publish',
            'post_type'      => 'post',
            'order'          => 'DESC',
            'orderby'        => 'post_date'
        ];

        $categories = ER::get_merged_customizer_value($email_campaign_id, 'post_categories');
        $tags       = ER::get_merged_customizer_value($email_campaign_id, 'post_tags');

        if ( ! empty($categories)) {
            $parameters['category'] = implode(',', array_map('trim', $categories));
        }

        if ( ! empty($tags)) {
            $parameters['tag_id'] = implode(',', array_map('trim', $tags));
        }

        $parameters['date_query'] = array(
            array(
                'column' => 'post_date',
                'after'  => $newer_than_timestamp
            )
        );

        return get_posts(apply_filters('mo_post_digest_get_posts_args', $parameters));
    }

    /**
     * @param $email_campaign_id
     * @param $carbon_now
     * @param $carbon_today
     * @param Carbon $schedule_hour
     * @param $timezone
     *
     * @return bool
     */
    public function should_send($email_campaign_id, $carbon_now, $schedule_hour, $timezone)
    {
        $status            = false;
        $last_processed_at = EmailCampaignMeta::get_meta_data($email_campaign_id, 'last_processed_at', true);

        // this ensures at least an hour will have to pass before next email digest would be published
        if ( ! empty($last_processed_at) && Carbon::parse($last_processed_at, $timezone)->diffInRealHours($carbon_now) >= 1) {
            $status = true;
        }

        // add an hour grace so missed schedule can still run.
        // the diffInRealHours condition below is important so it wont always return true even when the set
        // hour has past.
        if ($schedule_hour->diffInRealHours($carbon_now) <= 1) {
            $status = true;
        }

        return $status;
    }

    public function run_job()
    {
        if ( ! defined('MAILOPTIN_DETACH_LIBSODIUM')) return;

        $postDigests = EmailCampaignRepository::get_by_email_campaign_type(ER::POSTS_EMAIL_DIGEST);

        if (empty($postDigests)) return;

        foreach ($postDigests as $postDigest) {

            $email_campaign_id = absint($postDigest['id']);

            if (ER::is_campaign_active($email_campaign_id) === false) continue;

            $schedule_interval   = ER::get_merged_customizer_value($email_campaign_id, 'schedule_interval');
            $schedule_time       = absint(ER::get_merged_customizer_value($email_campaign_id, 'schedule_time'));
            $schedule_day        = absint(ER::get_merged_customizer_value($email_campaign_id, 'schedule_day'));
            $schedule_month_date = absint(ER::get_merged_customizer_value($email_campaign_id, 'schedule_month_date'));

            $timezone = get_option('timezone_string');
            if (empty($timezone)) {
                $timezone = get_option('gmt_offset');
            }

            $carbon_now   = Carbon::now($timezone);
            $carbon_today = Carbon::today($timezone);

            $schedule_hour = $carbon_today->hour($schedule_time);

            switch ($schedule_interval) {
                case 'every_day':
                    if ($schedule_hour->lessThanOrEqualTo($carbon_now) &&
                        $this->should_send($email_campaign_id, $carbon_now, $schedule_hour, $timezone)) {
                        $this->create_and_send_campaign($email_campaign_id);
                    }
                    break;
                case 'every_week':
                    if ($carbon_today->isDayOfWeek($schedule_day) &&
                        $schedule_hour->lessThanOrEqualTo($carbon_now) &&
                        $this->should_send($email_campaign_id, $carbon_now, $schedule_hour, $timezone)) {
                        $this->create_and_send_campaign($email_campaign_id);
                    }
                    break;
                case 'every_month':
                    if ($carbon_now->day == $schedule_month_date &&
                        $schedule_hour->lessThanOrEqualTo($carbon_now) &&
                        $this->should_send($email_campaign_id, $carbon_now, $schedule_hour, $timezone)) {
                        $this->create_and_send_campaign($email_campaign_id);
                    }
                    break;
            }
        }
    }

    public function create_and_send_campaign($email_campaign_id)
    {
        $campaign_id = $this->create_campaign($email_campaign_id);

        if ($campaign_id) {
            $this->send_campaign($email_campaign_id, $campaign_id);
        }
    }

    /**
     * @param $email_campaign_id
     *
     * @return bool|int
     */
    public function create_campaign($email_campaign_id)
    {
        $email_subject   = ER::get_merged_customizer_value($email_campaign_id, 'email_campaign_subject');
        $post_collection = $this->post_collection($email_campaign_id);
        if (empty($post_collection)) return false;

        $content_html = (new Templatify($email_campaign_id, $post_collection))->forge();

        return $this->save_campaign_log(
            $email_campaign_id,
            $email_subject,
            $content_html
        );
    }

    /**
     * Does the actual campaign sending.
     *
     * @param int $email_campaign_id
     * @param int $campaign_log_id
     */
    public function send_campaign($email_campaign_id, $campaign_log_id)
    {
        $campaign           = $this->CampaignLogRepository->getById($campaign_log_id);
        $connection_service = $this->connection_service($email_campaign_id);

        $connection_instance = ConnectionFactory::make($connection_service);

        EmailCampaignMeta::update_meta_data($email_campaign_id, 'last_processed_at', current_time('mysql'));

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