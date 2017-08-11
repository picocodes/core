<?php

namespace MailOptin\Core\Triggers;

use MailOptin\Core\EmailCampaign\TemplatifyNewPostPublish;
use MailOptin\Core\Connections\ConnectionFactory;
use MailOptin\Core\Repositories\EmailCampaignRepository as ER;
use WP_Post;

class NewPublishPost extends AbstractTriggers
{
    public function __construct()
    {
        parent::__construct();

        add_action('publish_post', array($this, 'new_publish_post'), 10, 2);
    }

    /**
     * @param int $post_id
     * @param WP_Post $post
     */
    public function new_publish_post($post_id, $post)
    {
        $new_publish_post_campaigns = ER::get_by_email_campaign_type(ER::NEW_PUBLISH_POST);

        foreach ($new_publish_post_campaigns as $npp_campaign) {
            $email_campaign_id = absint($npp_campaign['id']);
            $is_activated = ER::is_campaign_active($email_campaign_id);
            $send_immediately_active = $this->send_immediately($email_campaign_id);
            $email_subject = ER::get_customizer_value($email_campaign_id, 'email_campaign_subject');

            $content_html = (new TemplatifyNewPostPublish($post, $email_campaign_id))->forge();

            if ($is_activated === false) continue;

            $campaign_id = $this->save_campaign_log(
                $email_campaign_id,
                self::format_campaign_subject($email_subject, $post),
                $content_html
            );

            if ($send_immediately_active) {
                $this->send_campaign($email_campaign_id, $campaign_id);
            } else {
                // convert schedule time to timestamp.
                $schedule_time_timestamp = strtotime($this->schedule_time($email_campaign_id));

                $response = wp_schedule_single_event(
                    $schedule_time_timestamp,
                    'mailoptin_send_scheduled_email_campaign',
                    [$email_campaign_id, $campaign_id]
                );

                // wp_schedule_single_event() return false if event wasn't scheduled.
                if (false !== $response) {
                    $this->update_campaign_status($campaign_id, 'queued', $schedule_time_timestamp);
                }
            }
        }
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
     * @return NewPublishPost
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