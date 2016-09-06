<?php
namespace STS\EmailEventParser\Adapters;

use Illuminate\Support\Collection;
use STS\EmailEventParser\AbstractAdapter;
use STS\EmailEventParser\EmailEvent;

/**
 * Class SendGridWebhook
 * @package STS\EmailEventParser\Adapters
 */
class SendGridWebhook extends AbstractAdapter
{
    /**
     * @var string
     */
    protected $service = "SendGrid";

    /**
     * @var array
     */
    protected $eventMap = [
        'processed' => EmailEvent::EMAIL_ACCEPTED,
        'deferred' => EmailEvent::EVENT_DEFERRED,
        'delivered' => EmailEvent::EVENT_DELIVERED,
        'bounce' => EmailEvent::EVENT_BOUNCED,
        'dropped' => EmailEvent::EVENT_DROPPED,
        'spamreport' => EmailEvent::EVENT_COMPLAINED,
        'open' => EmailEvent::EVENT_OPENED,
        'click' => EmailEvent::EVENT_CLICKED
    ];

    /**
     * We need to track which fields we _expect_ from the API, in order to determine
     * which fields are additional custom data. SendGrid merges custom data into
     * the main list, this is the only way we're going to pull those out if needed.
     *
     * @var array
     */
    protected $expectedFields = [
        "status", "sg_event_id", "sg_message_id", "event", "email", "timestamp", "smtp-id", "category", "newsletter",
        "asm_group_id", "reason", "type", "ip", "tls", "cert_err", "pool", "useragent", "url", "url_offset", "attempt", "response",
        "marketing_campaign_id", "marketing_campaign_name", "post_type", "marketing_campaign_version", "marketing_campaign_split_id"
    ];

    /**
     * @return mixed
     */
    public function getRecipient()
    {
        return $this->payload->get('email');
    }

    /**
     * @return mixed
     */
    public function getTimestamp()
    {
        return $this->payload->get("timestamp");
    }

    /**
     * @return mixed
     */
    public function getMessageId()
    {
        return $this->payload->get("smtp-id");
    }

    /**
     * @return Collection
     */
    public function getTags()
    {
        return collect((array)$this->payload->get('category'));
    }

    /**
     * @return Collection
     */
    public function getData()
    {
        return collect(
            array_diff_key(
                $this->payload->toArray(), array_flip($this->expectedFields)
            )
        );
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        if ($this->getType() == EmailEvent::EVENT_BOUNCED) {
            return $this->payload->get('reason');
        }

        return $this->payload->get('response');
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        if ($this->getType() == EmailEvent::EVENT_BOUNCED) {
            return $this->payload->get('status');
        }
    }

    /**
     * @return mixed
     */
    public function getReason()
    {
        if ($this->getType() == EmailEvent::EVENT_BOUNCED) {
            return $this->payload->get('type');
        }
        if ($this->getType() == EmailEvent::EVENT_DROPPED) {
            return $this->payload->get('reason');
        }
    }

    /**
     * @param $payload
     *
     * @return bool
     */
    public static function handles($payload)
    {
        return array_key_exists('sg_message_id', $payload);
    }
}