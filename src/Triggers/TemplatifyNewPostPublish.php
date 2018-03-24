<?php

namespace MailOptin\Core\Triggers;

use MailOptin\Core\Admin\Customizer\EmailCampaign\EmailCampaignFactory;
use MailOptin\Core\Repositories\EmailCampaignRepository as ER;
use WP_Post;


class TemplatifyNewPostPublish
{
    protected $post;
    protected $email_campaign_id;
    protected $template_class;
    protected $campaign_type;
    protected $post_content_length;
    protected $default_feature_image;

    /**
     * @param mixed $post could be WP_Post object, post ID or stdClass for customizer preview
     * @param null|int $email_campaign_id
     * @param null|string $template_class
     */
    public function __construct($post, $email_campaign_id = null, $template_class = null, $campaign_type = null)
    {
        //used for sending test emails.
        if ($post instanceof \stdClass) {
            $this->post = $post;
        } else {
            $this->post = get_post($post);
        }

        $this->email_campaign_id = $email_campaign_id;
        $this->template_class = !is_null($template_class) ? $template_class : ER::get_template_class($email_campaign_id);
        $this->campaign_type = !is_null($campaign_type) ? $campaign_type : ER::get_email_campaign_type($email_campaign_id);
        $this->post_content_length = ER::get_customizer_value($email_campaign_id, 'post_content_length');
        $this->default_feature_image = ER::get_customizer_value($email_campaign_id, 'default_image_url');
    }

    /**
     * @return int
     */
    public function post_id()
    {
        return $this->post->ID;
    }

    /**
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
     * @return int
     */
    public function post_content_length()
    {
        return absint($this->post_content_length);
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
     * @return mixed
     */
    public function feature_image()
    {
        if (has_post_thumbnail($this->post_id())) {
            $image_data = wp_get_attachment_image_src(get_post_thumbnail_id($this->post_id()), 'full');
            if (!empty($image_data[0])) {
                return $image_data[0];
            }
        }

        return $this->default_feature_image;
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
        $campaign_type = $this->campaign_type;

        do_action('mailoptin_email_template_before_forge', $email_campaign_id, $db_template_class);

        $instance = EmailCampaignFactory::make($email_campaign_id);

        $search = array(
            '{{post.title}}',
            '{{post.content}}',
            '{{post.feature.image}}',
            '{{post.url}}',
        );

        if (0 === $this->post_content_length()) {
            $post_content = $this->post_content();
        } else {
            $post_content = \MailOptin\Core\limit_text(
                $this->post_content(),
                $this->post_content_length()
            );
        }

        $replace = array(
            $this->post_title(),
            wpautop($post_content),
            $this->feature_image(),
            $this->post_url(),
        );

        $templatified_content = str_replace($search, $replace, $instance->get_preview_structure());

        $emogrifier = new \Pelago\Emogrifier();
        $emogrifier->setHtml($templatified_content);

        $content = $emogrifier->emogrify();

        return str_replace(['%5B', '%5D', '%7B', '%7D'], ['[', ']', '{', '}'], $content);
    }
}