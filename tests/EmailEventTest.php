<?php

class EmailEventTest extends \PHPUnit\Framework\TestCase
{
    public function testDetectMailgun()
    {
        $event = \STS\EmailEventParser\EmailEvent::detect([]);
        $this->assertNull($event);

        $event = \STS\EmailEventParser\EmailEvent::detect([
            'event' => 'delivered',
            'token' => 'foo',
            'signature' => 'bar',
            'body-plain' => ''
        ]);
        $this->assertInstanceOf(\STS\EmailEventParser\EmailEvent::class, $event);
        $this->assertInstanceOf(\STS\EmailEventParser\Adapters\MailgunWebhook::class, $event->getAdapter());
    }

    public function testDetectSendGrid()
    {
        $event = \STS\EmailEventParser\EmailEvent::detect([]);
        $this->assertNull($event);

        $event = \STS\EmailEventParser\EmailEvent::detect([
            'event' => 'delivered',
            'sg_message_id' => 'foo'
        ]);
        $this->assertInstanceOf(\STS\EmailEventParser\EmailEvent::class, $event);
        $this->assertInstanceOf(\STS\EmailEventParser\Adapters\SendGridWebhook::class, $event->getAdapter());
    }

    public function testMagicCall()
    {
        $event = new \STS\EmailEventParser\EmailEvent(new \STS\EmailEventParser\Adapters\MailgunWebhook([
            'event' => 'delivered',
            'token' => 'foo',
            'signature' => 'bar',
            'body-plain' => ''
        ]));

        $this->assertEquals("foo", $event->get('token'));
        $this->assertEquals(\STS\EmailEventParser\EmailEvent::EVENT_DELIVERED, $event->getType());
    }
}