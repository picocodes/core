<?php

namespace MailOptin\Core\Repositories;

use MailOptin\Core\Connections\ConnectionFactory;
use MailOptin\RegisteredUsersConnect\Connect as RegisteredUsersConnect;

class ConnectionsRepository
{
    /**
     * List of connections available.
     *
     * @return mixed
     */
    public static function get_connections($customizer_type = 'optin_campaign')
    {
        // hooked array must be a key/label pair eg $arg['sendy'] = 'Sendy'
        $connections = apply_filters('mailoptin_registered_connections', array('' => __('Select...', 'mailoptin')));

        /** remove unconnected connections.  eg if an api key is missing or oauth authorization missing */
        foreach ($connections as $className => $label) {
            $connection_class = "MailOptin\\$className\\Connect";

            if (class_exists($connection_class) &&
                method_exists($connection_class, 'is_connected') &&
                $connection_class::is_connected() === false) {
                unset($connections[$className]);
            }
        }

        // if lite version and we are in email campaign customizer UI, remove ESP.
        if (!defined('MAILOPTIN_DETACH_LIBSODIUM') && $customizer_type == 'email_campaign') {
            foreach ($connections as $key => $value) {
                if ($key == '') {
                    continue;
                }
                if ($key != RegisteredUsersConnect::$connectionName) {
                    unset($connections[$key]);
                }
            }
        }

        return $connections;
    }

    /**
     * Get the array of email list belonging to a connection/email provider.
     *
     * @param string $connection connection/email-provider email list to fetch.
     *
     * @return array
     */
    public static function connection_email_list($connection = '')
    {
        if (empty($connection) || $connection == 'RegisteredUsersConnect') {
            return array();
        }

        $email_list = get_transient("_mo_connection_cache_$connection");

        // re-fetch email list from connect service API if prior return email list that was cache was empty
        // or if there was no cache data.
        if (empty($email_list) || false === $email_list) {
            $connectInstance = ConnectionFactory::make($connection);

            $email_list = (array)$connectInstance->get_email_list();

            set_transient(
                "_mo_connection_cache_$connection",
                $email_list,
                apply_filters('mailoptin_email_list_cache_expiration', HOUR_IN_SECONDS)
            );
        }

        return $email_list;
    }
}