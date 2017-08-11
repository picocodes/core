<?php

namespace MailOptin\Core\Connections;


use MailOptin\Core\Repositories\EmailCampaignRepository;

class ConnectionFactory
{
    /**
     * Return instance of a connection object.
     *
     * @param string $connection
     *
     * @return ConnectionInterface
     */
    public static function make($connection)
    {
        /** @var ConnectionInterface $connectClass */
        $connectClass = "MailOptin\\$connection\\Connect";

        return $connectClass::get_instance();
    }

}