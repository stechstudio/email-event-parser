<?php
namespace STS\EmailEventParser;

use Illuminate\Support\Collection;

/**
 * Interface AdapterContract
 * @package STS\EmailEventParser
 */
interface AdapterContract
{
    /**
     * @return string
     */
    public function getServiceName();

    /**
     * @return string
     */
    public function getType();

    /**
     * @return string
     */
    public function getMessageId();

    /**
     * @return string
     */
    public function getRecipient();

    /**
     * @return int
     */
    public function getTimestamp();

    /**
     * @return mixed
     */
    public function getResponse();

    /**
     * @return mixed
     */
    public function getReason();

    /**
     * @return mixed
     */
    public function getCode();

    /**
     * @return Collection
     */
    public function getTags();

    /**
     * @return Collection
     */
    public function getData();

    /**
     * @param $payload
     *
     * @return bool
     */
    public static function handles($payload);
}