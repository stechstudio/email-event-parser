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
     * @return string|null
     */
    public function getResponse();

    /**
     * @return string|null
     */
    public function getReason();

    /**
     * @return int|null
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