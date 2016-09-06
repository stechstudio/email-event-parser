<?php

class SendGridWebhookAdapterTest extends \PHPUnit\Framework\TestCase
{
    public function testDeliveredEvent()
    {
        $payload = [
            'response' => '250 OK',
            'sg_event_id' => 'sendgrid_internal_event_id',
            'sg_message_id' => 'sendgrid_internal_message_id',
            'event' => 'delivered',
            'email' => 'email@example.com',
            'timestamp' => 1249948800,
            'smtp-id' => '<original-smtp-id@domain.com>',
            'unique_arg_key' => 'unique_arg_value',
            'category' =>
                array (
                    0 => 'category1',
                    1 => 'category2',
                ),
            'newsletter' =>
                array (
                    'newsletter_user_list_id' => '10557865',
                    'newsletter_id' => '1943530',
                    'newsletter_send_id' => '2308608',
                ),
            'asm_group_id' => 1,
            'ip' => '127.0.0.1',
            'tls' => '1',
            'cert_err' => '1',
        ];

        $adapter = new \STS\EmailEventParser\Adapters\SendGridWebhook($payload);

        $this->assertEquals("SendGrid", $adapter->getServiceName());
        $this->assertEquals(\STS\EmailEventParser\EmailEvent::EVENT_DELIVERED, $adapter->getType());
        $this->assertEquals("<original-smtp-id@domain.com>", $adapter->getMessageId());
        $this->assertEquals(2, $adapter->getTags()->count());
        $this->assertTrue($adapter->getTags()->contains("category1"));
        $this->assertEquals("unique_arg_value", $adapter->get("unique_arg_key"));
        $this->assertEquals(1, $adapter->getData()->count());
        $this->assertEquals("unique_arg_value", $adapter->getData()->get("unique_arg_key"));
        $this->assertEquals("email@example.com", $adapter->getRecipient());
        $this->assertEquals(1249948800, $adapter->getTimestamp());
        $this->assertEquals("250 OK", $adapter->getResponse());

        $this->assertNull($adapter->getCode());
    }

    public function testBounceEvent()
    {
        $payload = [
            'status' => '5.0.0',
            'sg_event_id' => 'sendgrid_internal_event_id',
            'sg_message_id' => 'sendgrid_internal_message_id',
            'event' => 'bounce',
            'email' => 'email@example.com',
            'timestamp' => 1249948800,
            'smtp-id' => '<original-smtp-id@domain.com>',
            'unique_arg_key' => 'unique_arg_value',
            'category' => 'category1',
            'asm_group_id' => 1,
            'reason' => '500 No Such User',
            'type' => 'bounce',
            'ip' => '127.0.0.1',
            'tls' => '1',
            'cert_err' => '0',
        ];

        $adapter = new \STS\EmailEventParser\Adapters\SendGridWebhook($payload);

        $this->assertEquals(\STS\EmailEventParser\EmailEvent::EVENT_BOUNCED, $adapter->getType());
        $this->assertEquals("<original-smtp-id@domain.com>", $adapter->getMessageId());
        $this->assertEquals(1, $adapter->getTags()->count());
        $this->assertTrue($adapter->getTags()->contains("category1"));

        $this->assertEquals("email@example.com", $adapter->getRecipient());
        $this->assertEquals(1249948800, $adapter->getTimestamp());
        $this->assertEquals("500 No Such User", $adapter->getResponse());
        $this->assertEquals("bounce", $adapter->getReason()); // type field
        $this->assertEquals("5.0.0", $adapter->getCode());
    }

    public function testDroppedEvent()
    {
        $payload = [
            'sg_event_id' => 'sendgrid_internal_event_id',
            'sg_message_id' => 'sendgrid_internal_message_id',
            'email' => 'email@example.com',
            'timestamp' => 1249948800,
            'smtp-id' => '<original-smtp-id@domain.com>',
            'unique_arg_key' => 'unique_arg_value',
            'reason' => 'Bounced Address',
            'event' => 'dropped',
        ];

        $adapter = new \STS\EmailEventParser\Adapters\SendGridWebhook($payload);

        $this->assertEquals(\STS\EmailEventParser\EmailEvent::EVENT_DROPPED, $adapter->getType());
        $this->assertEquals(0, $adapter->getTags()->count());
        $this->assertFalse($adapter->getTags()->contains("tag1"));
        $this->assertNull($adapter->get("my-var-2"));
        $this->assertEquals("Bounced Address", $adapter->getReason());

        $this->assertEmpty($adapter->getResponse());
    }

    public function testDeferredEvent()
    {
        $payload = [
            'response' => '400 Try again',
            'sg_event_id' => 'sendgrid_internal_event_id',
            'sg_message_id' => 'sendgrid_internal_message_id',
            'event' => 'deferred',
            'email' => 'email@example.com',
            'timestamp' => 1249948800,
            'smtp-id' => '<original-smtp-id@domain.com>',
            'unique_arg_key' => 'unique_arg_value',
            'category' =>
                array (
                    0 => 'category1',
                    1 => 'category2',
                ),
            'attempt' => '10',
            'asm_group_id' => 1,
            'ip' => '127.0.0.1',
            'tls' => '0',
            'cert_err' => '0',
        ];

        $adapter = new \STS\EmailEventParser\Adapters\SendGridWebhook($payload);

        $this->assertEquals(\STS\EmailEventParser\EmailEvent::EVENT_DEFERRED, $adapter->getType());
        $this->assertEquals("400 Try again", $adapter->getResponse());
        $this->assertEquals("10", $adapter->get("attempt"));
    }
}