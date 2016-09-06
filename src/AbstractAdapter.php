<?php
namespace STS\EmailEventParser;

use Illuminate\Support\Collection;

/**
 * Class AbstractAdapter
 * @package STS\EmailEventParser
 */
abstract class AbstractAdapter implements AdapterContract
{
    /**
     * @var Collection
     */
    protected $payload;

    /**
     * @var array
     */
    protected $eventMap = [];

    /**
     * @var string
     */
    protected $service;

    /**
     * AbstractAdapter constructor.
     *
     * @param $payload
     */
    public function __construct($payload)
    {
        $this->payload = collect($payload);
    }

    /**
     * @return string
     */
    public function getServiceName()
    {
        return $this->service;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return array_get($this->eventMap, $this->payload->get('event'));
    }

    /**
     * @return Collection
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @param $method
     * @param $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->payload, $method], $parameters);
    }
}