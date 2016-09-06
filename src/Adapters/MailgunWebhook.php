<?php
namespace STS\EmailEventParser\Adapters;

use Illuminate\Support\Collection;
use STS\EmailEventParser\AbstractAdapter;
use STS\EmailEventParser\EmailEvent;

/**
 * Class MailgunWebhook
 * @package STS\EmailEventParser\Adapters
 */
class MailgunWebhook extends AbstractAdapter
{
    /**
     * @var string
     */
    protected $service = "Mailgun";

    /**
     * @var $this |array
     */
    protected $headers;

    /**
     * @var array
     */
    protected $eventMap = [
        'delivered' => EmailEvent::EVENT_DELIVERED,
        'bounced' => EmailEvent::EVENT_BOUNCED,
        'dropped' => EmailEvent::EVENT_DROPPED,
        'complained' => EmailEvent::EVENT_COMPLAINED,
        'opened' => EmailEvent::EVENT_OPENED,
        'clicked' => EmailEvent::EVENT_CLICKED
    ];

    /**
     * MailgunWebhook constructor.
     *
     * @param $payload
     */
    public function __construct($payload)
    {
        parent::__construct($payload);

        $this->headers = collect(json_decode($this->payload->get('message-headers'), true));
    }

    /**
     * @return mixed
     */
    public function getRecipient()
    {
        return $this->payload->get('recipient');
    }

    /**
     * @return mixed
     */
    public function getMessageId()
    {
        return $this->payload->get('Message-Id');
    }

    /**
     * @return mixed
     */
    public function getTimestamp()
    {
        return $this->payload->get('timestamp');
    }

    /**
     * @return mixed
     */
    public function getReason()
    {
        if ($this->getType() == EmailEvent::EVENT_DROPPED) {
            return $this->payload->get('description');
        }

        return $this->payload->get('reason');
    }

    /**
     * @return null|string
     */
    public function getResponse()
    {
        if ($this->getType() == EmailEvent::EVENT_BOUNCED) {
            return $this->payload->get('error');
        }

        return $this->payload->get('response');
    }

    /**
     * @return int|null
     */
    public function getCode()
    {
        return $this->payload->get('code');
    }

    /**
     * @return mixed
     */
    public function getTags()
    {
        return $this->headers
            ->filter(function($header) {
                return $header[0] == 'X-Mailgun-Tag';
            })
            ->pluck(1);
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return collect(json_decode(
            $this->headers
                ->filter(function($header) {
                    return $header[0] == 'X-Mailgun-Variables';
                })
                ->flatten()
                ->pull(1)
        , true));
    }

    /**
     * @param $key
     *
     * @return mixed|null
     */
    public function get($key)
    {
        if ($this->payload->has($key)) {
            return $this->payload->get($key);
        }

        if ($this->headers->has($key)) {
            return $this->headers->get($key);
        }

        return null;
    }

    /**
     * @param $payload
     *
     * @return bool
     */
    public static function handles($payload)
    {
        return array_key_exists('token', $payload) && array_key_exists('signature', $payload);
    }

    /**
     * @param Collection $headers
     *
     * @return Collection
     */
    protected function parseHeaders(Collection $headers)
    {
        return $headers
            // First element of each sub-array is our key
            ->pluck(0)
            // Second element is the value
            ->combine($headers->pluck(1))
            // Overwrite the X-Mailgun-Tag key with a collection of tags
            ->put('X-Mailgun-Tag', $headers->where(0, "=", 'X-Mailgun-Tag')->pluck(1))
            // Overwrite the X-Mailgun-Variables. Need to json_decode and wrap as a collection.
            ->put('X-Mailgun-Variables', collect(
                json_decode($headers->where(0, "=", 'X-Mailgun-Variables')->flatten()->pull(1), true)
            ));
    }
}