<?php

namespace MailOptin\Core\Admin\SettingsPage;

use MailOptin\Core\Core;
use MailOptin\Core\Repositories\EmailCampaignRepository;

if ( ! class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Email_Campaign_List extends \WP_List_Table
{
    private $table;

    /** @var \wpdb */
    private $wpdb;

    /**
     * Class constructor
     */
    public function __construct($wpdb)
    {
        $this->wpdb  = $wpdb;
        $this->table = $this->wpdb->prefix . Core::email_campaigns_table_name;
        parent::__construct(array(
                'singular' => __('email_campaign', 'mailoptin'), //singular name of the listed records
                'plural'   => __('email_campaigns', 'mailoptin'), //plural name of the listed records
                'ajax'     => false //does this table support ajax?
            )
        );
    }

    /**
     * Retrieve campaigns data from the database
     *
     * @param int $per_page
     * @param int $current_page
     * @param string $campaign_type
     *
     * @return mixed
     */
    public function get_email_campaigns($per_page, $current_page = 1, $campaign_type = '')
    {
        $offset = ($current_page - 1) * $per_page;
        $sql    = "SELECT * FROM {$this->table}";
        if ( ! empty($campaign_type)) {
            $campaign_type = esc_sql($campaign_type);
            $sql           .= "  WHERE campaign_type = '$campaign_type'";
        }

        $sql .= "  ORDER BY id DESC";

        $sql .= " LIMIT $per_page";
        if ($current_page > 1) {
            $sql .= "  OFFSET $offset";
        }

        $result = $this->wpdb->get_results($sql, 'ARRAY_A');

        return $result;
    }


    /**
     * Delete a campaign record.
     *
     * @param int $email_campaign_id campaign ID
     */
    public function delete_email_campaign($email_campaign_id)
    {
        EmailCampaignRepository::delete_campaign_by_id($email_campaign_id);

        // remove the campaign meta data.
        EmailCampaignRepository::delete_settings_by_id($email_campaign_id);
    }

    /**
     * Returns the count of records in the database.
     *
     * @return null|string
     */
    public function record_count($campaign_type)
    {
        global $wpdb;
        $sql = "SELECT COUNT(*) FROM $this->table";
        if ( ! empty($campaign_type)) {
            $campaign_type = esc_sql($campaign_type);
            $sql           .= "  WHERE campaign_type = '$campaign_type'";
        }

        return $wpdb->get_var($sql);
    }

    /**
     * Generate URL to delete email campaign.
     *
     * @param int $item_id
     *
     * @return string
     */
    public static function _campaign_delete_url($item_id)
    {
        $delete_nonce = wp_create_nonce('mailoptin_delete_email_campaign');

        return sprintf(
            '?page=%s&action=%s&email-campaign-id=%s&_wpnonce=%s"',
            sanitize_text_field($_REQUEST['page']), 'delete', absint($item_id), $delete_nonce
        );
    }

    /**
     * Generate URL to clone email campaign.
     *
     * @param int $item_id
     *
     * @return string
     */
    public static function _campaign_clone_url($item_id)
    {
        $clone_nonce = wp_create_nonce('mailoptin_clone_email_campaign');

        return sprintf(
            '?page=%s&action=%s&email-campaign-id=%s&_wpnonce=%s"',
            sanitize_text_field($_REQUEST['page']), 'clone', absint($item_id), $clone_nonce
        );
    }

    /**
     * Generate URL to customize email campaign.
     *
     * @param int $item_id
     *
     * @return string
     */
    public static function _campaign_customize_url($item_id)
    {
        return add_query_arg(
            apply_filters('mo_email_campaign_customize_url', array(
                    'mailoptin_email_campaign_id' => $item_id,
                )
            ),
            admin_url('customize.php')
        );
    }

    /**
     * Generate URL to deactivate email campaign.
     *
     * @param int $item_id
     *
     * @return string
     */
    public function _email_campaign_deactivate_url($item_id)
    {
        $deactivate_nonce = wp_create_nonce('mailoptin_deactivate_email_campaign');

        return sprintf(
            '?page=%s&action=%s&email-campaign-id=%s&_wpnonce=%s"',
            sanitize_text_field($_REQUEST['page']), 'deactivate', absint($item_id), $deactivate_nonce
        );
    }

    /**
     * Generate URL to activate email campaign.
     *
     * @param int $item_id
     *
     * @return string
     */
    public function _email_campaign_activate_url($item_id)
    {
        $activate_nonce = wp_create_nonce('mailoptin_activate_email_campaign');

        return sprintf(
            '?page=%s&action=%s&email-campaign-id=%s&_wpnonce=%s"',
            sanitize_text_field($_REQUEST['page']), 'activate', absint($item_id), $activate_nonce
        );
    }

    /** Text displayed when no email campaign is available */
    public function no_items()
    {
        _e('No email campaign is available.', 'mailoptin');
    }

    /**
     * Render the bulk edit checkbox
     *
     * @param array $item
     *
     * @return string
     */
    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="email_campaign_ids[]" value="%s" />', $item['id']
        );
    }

    /**
     * Method for name column
     *
     * @param array $item an array of DB data
     *
     * @return string
     */
    function column_name($item)
    {
        $email_campaign_id = absint($item['id']);

        $customize_url = self::_campaign_customize_url($email_campaign_id);

        $delete_url = self::_campaign_delete_url($email_campaign_id);
        $name       = "<a href=\"$customize_url\"><strong>" . $item['name'] . '</strong></a>';

        $actions = array(
            'delete' => sprintf("<a href=\"$delete_url\">%s</a>", __('Delete', 'mailoptin')),
        );

        return $name . $this->row_actions($actions);
    }

    /**
     * Render a column when no column specific method exist.
     *
     * @param array $item
     *
     * @return mixed
     */
    public function column_action($item)
    {
        $email_campaign_id = absint($item['id']);

        $delete_url    = self::_campaign_delete_url($email_campaign_id);
        $clone_url     = self::_campaign_clone_url($email_campaign_id);
        $customize_url = self::_campaign_customize_url($email_campaign_id);

        $action = sprintf(
            '<a class="mo-tooltipster button action mailoptin-btn-blue" href="%s" title="%s">%s</a> &nbsp;',
            esc_url_raw($customize_url),
            __('Customize', 'mailoptin'),
            '<i class="fa fa-pencil" aria-hidden="true"></i>'
        );
        $action .= sprintf(
            '<a class="mo-tooltipster button action" href="%s" title="%s">%s</a> &nbsp;',
            $clone_url,
            __('Clone', 'mailoptin'),
            '<i class="fa fa-clone" aria-hidden="true"></i>'
        );
        $action .= sprintf(
            '<a class="mo-tooltipster button action mailoptin-btn-red" href="%s" title="%s">%s</a> &nbsp;',
            $delete_url,
            __('Delete', 'mailoptin'),
            '<i class="fa fa-trash" aria-hidden="true"></i>'
        );

        return $action;
    }

    /**
     * Method for activated column
     *
     * @param array $item an array of DB data
     *
     * @return string
     */
    public function column_activated($item)
    {
        $email_campaign_id = absint($item['id']);

        $input_value = EmailCampaignRepository::is_campaign_active($email_campaign_id) ? 'yes' : 'no';
        $checked     = ($input_value == 'yes') ? 'checked="checked"' : null;

        $switch = sprintf(
            '<input data-mo-automation-id="%1$s" id="mo-automation-activate-switch-%1$s" type="checkbox" class="mo-automation-activate-switch tgl tgl-light" value="%%3$s" %3$s />',
            $email_campaign_id,
            $input_value,
            $checked
        );

        $switch .= sprintf(
            '<label for="mo-automation-activate-switch-%1$s" style="margin:auto;" class="tgl-btn"></label>',
            $email_campaign_id
        );

        return $switch;
    }

    /**
     * Display campaign type data and any other data.
     *
     * @param object $item
     * @param string $column_name
     *
     * @return string
     */
    public function column_default($item, $column_name)
    {
        return EmailCampaignRepository::get_type_name(
            sanitize_text_field($item[$column_name])
        );
    }

    /**
     *  Associative array of columns
     *
     * @return array
     */
    public function get_columns()
    {
        $columns = array(
            'cb'            => '<input type="checkbox">',
            'name'          => __('Name', 'mailoptin'),
            'campaign_type' => __('Automation Type', 'mailoptin'),
            'action'        => __('Actions', 'mailoptin'),
            'activated'     => __('Activated', 'mailoptin'),
        );

        return $columns;
    }

    /**
     * Columns to make sortable.
     *
     * @return array
     */
    public function get_sortable_columns()
    {
        $sortable_columns = array(
            'name' => array('name', true),
        );

        return $sortable_columns;
    }

    /**
     * Returns an associative array containing the bulk action
     *
     * @return array
     */
    public function get_bulk_actions()
    {
        $actions = array(
            'bulk-delete'     => __('Delete', 'mailoptin'),
            'bulk-activate'   => __('Activate', 'mailoptin'),
            'bulk-deactivate' => __('Deactivate', 'mailoptin'),
        );

        return $actions;
    }

    /**
     * Handles data query and filter, sorting, and pagination.
     */
    public function prepare_items()
    {
        /** Process bulk action */
        $this->process_actions();

        $campaign_type = isset($_GET['page']) && $_GET['page'] == MAILOPTIN_EMAIL_CAMPAIGNS_SETTINGS_SLUG && ! empty($_GET['type']) ? sanitize_text_field($_GET['type']) : '';

        $this->_column_headers = $this->get_column_info();
        $per_page              = defined('MAILOPTIN_DETACH_LIBSODIUM') ? $this->get_items_per_page('email_campaign_per_page', 15) : 1;
        $current_page          = $this->get_pagenum();
        $total_items           = defined('MAILOPTIN_DETACH_LIBSODIUM') ? self::record_count($campaign_type) : 1;
        $this->set_pagination_args(array(
                'total_items' => $total_items, //WE have to calculate the total number of items
                'per_page'    => $per_page //WE have to determine how many items to show on a page
            )
        );

        $this->items = $this->get_email_campaigns($per_page, $current_page, $campaign_type);
    }

    public function process_actions()
    {
        // bail if user is not an admin or without admin privileges.
        if ( ! current_user_can('administrator')) {
            return;
        }

        $email_campaign_id = @absint($_GET['email-campaign-id']);

        // Detect when a bulk action is being triggered...
        if ('delete' === $this->current_action()) {
            // In our file that handles the request, verify the nonce.
            $nonce = esc_attr($_REQUEST['_wpnonce']);
            if ( ! wp_verify_nonce($nonce, 'mailoptin_delete_email_campaign')) {
                die('Go get a life script kiddies');
            } else {
                self::delete_email_campaign($email_campaign_id);
                // esc_url_raw() is used to prevent converting ampersand in url to "#038;"
                // add_query_arg() return the current url
                wp_redirect(esc_url_raw(MAILOPTIN_EMAIL_CAMPAIGNS_SETTINGS_PAGE));
                exit;
            }
        }

        // clone when the current action is clone.
        if ('clone' === $this->current_action()) {

            if (apply_filters('mailoptin_add_new_email_campaign_limit', true) && EmailCampaignRepository::campaign_count() > 1) {
                return;
            }

            // In our file that handles the request, verify the nonce.
            $nonce = esc_attr($_REQUEST['_wpnonce']);
            if ( ! wp_verify_nonce($nonce, 'mailoptin_clone_email_campaign')) {
                die('Go get a life script kiddies');
            } else {
                (new CloneEmailCampaign($email_campaign_id))->forge();
                wp_redirect(esc_url_raw(MAILOPTIN_EMAIL_CAMPAIGNS_SETTINGS_PAGE));
                exit;
            }
        }

        // Activate email campaign.
        if ('activate' === $this->current_action()) {
            // In our file that handles the request, verify the nonce.
            $nonce = sanitize_text_field($_REQUEST['_wpnonce']);
            if ( ! wp_verify_nonce($nonce, 'mailoptin_activate_email_campaign')) {
                die('Go get a life script kiddies');
            } else {
                EmailCampaignRepository::activate_email_campaign($email_campaign_id);
                wp_redirect(esc_url_raw(MAILOPTIN_EMAIL_CAMPAIGNS_SETTINGS_PAGE));
                exit;
            }
        }

        // Deactivate email campaign.
        if ('deactivate' === $this->current_action()) {
            // In our file that handles the request, verify the nonce.
            $nonce = sanitize_text_field($_REQUEST['_wpnonce']);
            if ( ! wp_verify_nonce($nonce, 'mailoptin_deactivate_email_campaign')) {
                die('Go get a life script kiddies');
            } else {
                EmailCampaignRepository::deactivate_email_campaign($email_campaign_id);
                wp_redirect(esc_url_raw(MAILOPTIN_EMAIL_CAMPAIGNS_SETTINGS_PAGE));
                exit;
            }
        }


        // If the delete bulk action is triggered
        if ('bulk-delete' === $this->current_action()) {
            $delete_ids = array_map('absint', $_POST['email_campaign_ids']);
            // loop over the array of record IDs and delete them
            foreach ($delete_ids as $id) {
                self::delete_email_campaign($id);
            }
            wp_redirect(esc_url_raw(add_query_arg()));
            exit;
        }

        // If the activate bulk action is triggered
        if ('bulk-activate' === $this->current_action()) {
            $activate_ids = array_map('absint', $_POST['email_campaign_ids']);
            // loop over the array of campaign IDs and actvate them
            foreach ($activate_ids as $id) {
                EmailCampaignRepository::activate_email_campaign($id);
            }
            wp_redirect(esc_url_raw(add_query_arg()));
            exit;
        }

        // If the deactivate bulk action is triggered
        if ('bulk-deactivate' === $this->current_action()) {
            $deactivate_ids = array_map('absint', $_POST['email_campaign_ids']);
            // loop over the array of campaign IDs and deactivate them
            foreach ($deactivate_ids as $id) {
                EmailCampaignRepository::deactivate_email_campaign($id);
            }
            wp_redirect(esc_url_raw(add_query_arg()));
            exit;
        }
    }

    /**
     * @return Email_Campaign_List
     */
    public static function get_instance()
    {
        static $instance = null;

        if (is_null($instance)) {
            $instance = new self($GLOBALS['wpdb']);
        }

        return $instance;
    }
}