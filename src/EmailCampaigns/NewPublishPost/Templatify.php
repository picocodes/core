<?php

namespace MailOptin\Core\EmailCampaigns\NewPublishPost;

use MailOptin\Core\Admin\Customizer\EmailCampaign\EmailCampaignFactory;
use MailOptin\Core\EmailCampaigns\TemplateTrait;
use MailOptin\Core\EmailCampaigns\TemplatifyInterface;
use MailOptin\Core\EmailCampaigns\VideoToImageLink;
use MailOptin\Core\Repositories\EmailCampaignRepository as ER;
use WP_Post;


class Templatify implements TemplatifyInterface
{
    use TemplateTrait;

    protected $post;
    protected $email_campaign_id;
    protected $template_class;
    protected $post_content_length;

    /**
     * @param null|int $email_campaign_id
     * @param mixed $post could be WP_Post object, post ID or stdClass for customizer preview
     * @param null|string $template_class
     */
    public function __construct($email_campaign_id, $post = null, $template_class = null)
    {
        //used for sending test emails.
        if ($post instanceof \stdClass) {
            $this->post = $post;
        } else {
            $this->post = get_post($post);
        }

        $this->email_campaign_id   = $email_campaign_id;
        $this->template_class      = ! is_null($template_class) ? $template_class : ER::get_template_class($email_campaign_id);
        $this->post_content_length = ER::get_customizer_value($email_campaign_id, 'post_content_length');
    }

    /**
     *
     * @return string
     */
    public function post_title()
    {
        return $this->post->post_title;
    }

    /**
     * @return string
     */
    public function post_content()
    {
        return do_shortcode($this->post->post_content);
    }

    /**
     * @return false|mixed|string
     */
    public function post_url()
    {
        if ($this->post instanceof \stdClass) {
            return $this->post->post_url;
        }

        return get_permalink($this->post->ID);
    }

    /**
     * Turn {@see WP_Post} object to email campaign template.
     *
     * @return mixed
     */
    public function forge()
    {
        $email_campaign_id = $this->email_campaign_id;
        $db_template_class = $this->template_class;

        do_action('mailoptin_email_template_before_forge', $email_campaign_id, $db_template_class);

        $instance = EmailCampaignFactory::make($email_campaign_id);

        $search = array(
            '{{post.title}}',
            '{{post.content}}',
            '{{post.feature.image}}',
            '{{post.url}}',
        );

        $post_content_length = absint($this->post_content_length);

        if (0 === $post_content_length) {
            $post_content = $this->post_content();
        } else {
            $post_content = \MailOptin\Core\limit_text(
                $this->post_content(),
                $post_content_length
            );
        }

        $replace = [
            $this->post_title(),
            wpautop($post_content),
            $this->feature_image($this->post->ID),
            $this->post_url(),
        ];

        $templatified_content = str_replace($search, $replace, $instance->get_preview_structure());

        $templatified_content = apply_filters('mo_new_publish_post_post_templatify_forge', $templatified_content, $this->post->ID, $this);

        $content = (new VideoToImageLink($templatified_content))->forge();

        if ( ! is_customize_preview()) {
            $emogrifier = new \Pelago\Emogrifier();
            $emogrifier->setHtml($content);
            $emogrifier->disableInvisibleNodeRemoval();

            $content = $emogrifier->emogrify();
        }

        return $this->replace_footer_placeholder_tags(
        // we found out urlendcode was been done especially to the url part. previously we were doing
        // str_replace(['%5B', '%5D', '%7B', '%7D'], ['[', ']', '{', '}'], $content) going forward
        // urldecode is the best way.
            urldecode($content)
        );
    }
}