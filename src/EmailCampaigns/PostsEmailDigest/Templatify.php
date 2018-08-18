<?php

namespace MailOptin\Core\EmailCampaigns\PostsEmailDigest;

use MailOptin\Core\Admin\Customizer\EmailCampaign\EmailCampaignFactory;
use MailOptin\Core\EmailCampaigns\TemplateTrait;
use MailOptin\Core\EmailCampaigns\TemplatifyInterface;
use MailOptin\Core\EmailCampaigns\VideoToImageLink;


class Templatify implements TemplatifyInterface
{
    use TemplateTrait;

    protected $email_campaign_id;
    protected $posts;

    /**
     * @param int $email_campaign_id
     * @param array $posts
     */
    public function __construct($email_campaign_id, $posts = [])
    {
        $this->email_campaign_id = $email_campaign_id;
        $this->posts             = $posts;
    }

    public function forge()
    {
        do_action('mailoptin_email_template_before_forge', $this->email_campaign_id);

        $instance = EmailCampaignFactory::make($this->email_campaign_id, $this->posts);

        $templatified_content = $instance->get_preview_structure();

        $content = (new VideoToImageLink($templatified_content))->forge();

        if ( ! is_customize_preview()) {
            $emogrifier = new \Pelago\Emogrifier();
            $emogrifier->setHtml($content);

            $content = $emogrifier->emogrify();
        }

        return $this->replace_footer_placeholder_tags(
            str_replace(['%5B', '%5D', '%7B', '%7D'], ['[', ']', '{', '}'], $content)
        );
    }
}