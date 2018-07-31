<?php

namespace MailOptin\Core\EmailCampaigns\PostsEmailDigest;

use MailOptin\Core\Admin\Customizer\EmailCampaign\EmailCampaignFactory;
use MailOptin\Core\EmailCampaigns\TemplateTrait;
use MailOptin\Core\EmailCampaigns\TemplatifyInterface;
use MailOptin\Core\EmailCampaigns\VideoToImageLink;
use MailOptin\Core\Repositories\EmailCampaignRepository as ER;


class Templatify implements TemplatifyInterface
{
    use TemplateTrait;

    protected $email_campaign_id;

    /**
     * @param mixed $post could be WP_Post object, post ID or stdClass for customizer preview
     * @param null|int $email_campaign_id
     */
    public function __construct($email_campaign_id = null)
    {
        $this->email_campaign_id = $email_campaign_id;
    }

    /**
     * Turn {@see WP_Post} object to email campaign template.
     *
     * @return mixed
     */
    public function forge()
    {
        do_action('mailoptin_email_template_before_forge', $this->email_campaign_id);

        $instance = EmailCampaignFactory::make($this->email_campaign_id);

        $templatified_content = $instance->get_preview_structure();

        $content = (new VideoToImageLink($templatified_content))->forge();

        $emogrifier = new \Pelago\Emogrifier();
        $emogrifier->setHtml($content);

        $content = $emogrifier->emogrify();

        return str_replace(['%5B', '%5D', '%7B', '%7D'], ['[', ']', '{', '}'], $content);
    }
}