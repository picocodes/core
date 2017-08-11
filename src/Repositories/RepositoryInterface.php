<?php

namespace MailOptin\Core\Repositories;


interface RepositoryInterface
{
    /**
     * Add item to repository.
     * 
     * @param mixed $data
     *
     * @return mixed
     */
    public static function add($data);

    /**
     * Add item to repository.
     *
     * @param mixed $item
     *
     * @return mixed
     */
    public static function get($item);

    /**
     * Delete item in repository.
     *
     * @param mixed $item
     *
     * @return mixed
     */
    public static function delete($item);
}