<?php
namespace STS\EmailEventParser;

use Illuminate\Support\Collection;
use STS\EmailEventParser\AdapterContract;
use STS\EmailEventParser\Adapters\MailgunWebhook;
use STS\EmailEventParser\Adapters\SendGridWebhook;

/**
 * Class EmailEvent
 * @package STS\EmailEventParser
 *
 * @method string getServiceName()
 * @method string getType()
 * @method string getMessageId()
 * @method string getRecipient()
 * @method int getTimestamp()
 * @method string getResponse()
 * @method string getReason()
 * @method mixed getCode()
 * @method Collection getTags()
 * @method Collection getData()
 * @method array getPayload()
 */
class EmailEvent
{
    const EMAIL_ACCEPTED = "accepted";
    const EVENT_SENT = "sent";
    const EVENT_DEFERRED = "deferred";
    const EVENT_DELIVERED = "delivered";
    const EVENT_BOUNCED = "bounced";
    const EVENT_DROPPED = "dropped";
    const EVENT_COMPLAINED = "complained";
    const EVENT_OPENED = "opened";
    const EVENT_CLICKED = "clicked";

    /**
     * @var array
     */
    protected static $adapters = [
        MailgunWebhook::class,
        SendGridWebhook::class,
    ];

    /**
     * @var AdapterContract
     */
    protected $adapter;

    /**
     * EmailEvent constructor.
     *
     * @param AdapterContract $adapter
     */
    public function __construct(AdapterContract $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @return AdapterContract
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @param $payload
     *
     * @return AdapterContract|null
     */
    public static function detect($payload)
    {
        foreach(self::$adapters AS $adapter) {
            if($adapter::handles($payload)) {
                return new static(new $adapter($payload));
            }
        }
    }

    /**
     * @param $method
     * @param $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->adapter, $method], $parameters);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'service' => $this->adapter->getServiceName(),
            'event' => $this->adapter->getType(),
            'timestamp' => $this->adapter->getTimestamp(),
            'recipient' => $this->adapter->getRecipient(),
            'messageId' => $this->adapter->getMessageId(),
            'tags' => $this->adapter->getTags()->toArray(),
            'data' => $this->adapter->getData()->toArray(),
            'response' => $this->adapter->getResponse(),
            'reason' => $this->adapter->getReason(),
            'code' => $this->adapter->getCode(),
        ];
    }
}