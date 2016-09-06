<?php

class MailgunWebhookAdapterTest extends \PHPUnit\Framework\TestCase
{
    public function testDeliveredEvent()
    {
        $payload = [
            "token" => "9cb8c0254c6add3c348dad01fdc150cfcb740fe99b38baecce",
            "event" => "delivered",
            "my_var_1" => "Mailgun Variable #1",
            "my-var-2" => "awesome",
            "body-plain" => "",
            "timestamp" => 1473102218,
            "X-Mailgun-Sid" => "WyJjNjJhMSIsICJqb3NlcGhAcmVwcm9jb25uZWN0LmNvbSIsICIzYWMyZjYiXQ==",
            "signature" => "d4cd982eefe9d8fb93a0662584d56cdba8ab1fe4b56772e8ba505be712c1a348",
            "Message-Id" => "<20160902203022.89990.87738.3034A4A7@localhost.com>",
            "message-headers" => '[["Received", "by luna.mailgun.net with SMTP mgrt 8734663311733; Fri, 03 May 2013 18:26:27 +0000"], ["Content-Type", ["multipart/alternative", {"boundary": "eb663d73ae0a4d6c9153cc0aec8b7520"}]], ["Mime-Version", "1.0"], ["Subject", "Test deliver webhook"], ["From", "Bob <bob@example.com>"], ["To", "Alice <alice@example.com>"], ["Message-Id", "<20130503182626.18666.16540@example.com>"], ["X-Mailgun-Tag", "tag1"], ["X-Mailgun-Tag", "tag2"], ["X-Mailgun-Variables", "{\"my_var_1\": \"Mailgun Variable #1\", \"my-var-2\": \"awesome\"}"], ["Date", "Fri, 03 May 2013 18:26:27 +0000"], ["Sender", "test@example.com"]]',
            "recipient" => "alice@example.com",
            "domain" => "example.com"
        ];

        $adapter = new \STS\EmailEventParser\Adapters\MailgunWebhook($payload);

        $this->assertEquals("Mailgun", $adapter->getServiceName());
        $this->assertEquals(\STS\EmailEventParser\EmailEvent::EVENT_DELIVERED, $adapter->getType());
        $this->assertEquals("<20160902203022.89990.87738.3034A4A7@localhost.com>", $adapter->getMessageId());
        $this->assertEquals("alice@example.com", $adapter->getRecipient());
        $this->assertEquals(1473102218, $adapter->getTimestamp());

        $this->assertEquals(2, $adapter->getTags()->count());
        $this->assertTrue($adapter->getTags()->contains("tag1"));
        $this->assertEquals("awesome", $adapter->get("my-var-2"));
        $this->assertEquals("awesome", $adapter->getData()->get("my-var-2"));

        $this->assertNull($adapter->getResponse());
        $this->assertNull($adapter->getCode());
    }

    public function testBouncedEvent()
    {
        $payload = [
            "my-var-2" => " awesome",
            "message-headers" => '[["Received", "by luna.mailgun.net with SMTP mgrt 8734663311733; Fri, 03 May 2013 18:26:27 +0000"], ["Content-Type", ["multipart/alternative", {"boundary": "eb663d73ae0a4d6c9153cc0aec8b7520"}]], ["Mime-Version", "1.0"], ["Subject", "Test bounces webhook"], ["From", "Bob <bob@example.com>"], ["To", "Alice <alice@example.com>"], ["Message-Id", "<20130503182626.18666.16540@example.com>"], ["List-Unsubscribe", "<mailto:u+na6tmy3ege4tgnldmyytqojqmfsdembyme3tmy3cha4wcndbgaydqyrgoi6wszdpovrhi5dinfzw63tfmv4gs43uomstimdhnvqws3bomnxw2jtuhusteqjgmq6tm@example.com>"], ["X-Mailgun-Sid", "WyIwNzI5MCIsICJhbGljZUBleGFtcGxlLmNvbSIsICI2Il0="], ["X-Mailgun-Variables", "{\"my_var_1\": \"Mailgun Variable #1\", \"my-var-2\": \"awesome\"}"], ["Date", "Fri, 03 May 2013 18:26:27 +0000"], ["Sender", "bob@example.com"]]',
            "X-Mailgun-Sid: WyIwNzI5MCIsICJhbGljZUBleGFtcGxlLmNvbSIsICI2Il0=",
            "attachment-count" => 1,
            "domain" => "example.com",
            "Message-Id" => "<20130503182626.18666.16540@example.com>",
            "timestamp" => 1473104493,
            "my_var_1" => "Mailgun Variable #1",
            "event" => "bounced",
            "code" => "550",
            "token" => "52de819f7279d2613d05cdbc38e4fcd156653b5430edfd1832",
            "error" => "5.1.1 The email account that you tried to reach does not exist",
            "signature" => "a28adc8de91a4e5f66f4a01b153361881517909b7cc257323b88d218f475f229",
            "body-plain" => "",
            "recipient" => "alice@example.com",
        ];

        $adapter = new \STS\EmailEventParser\Adapters\MailgunWebhook($payload);

        $this->assertEquals(\STS\EmailEventParser\EmailEvent::EVENT_BOUNCED, $adapter->getType());
        $this->assertEquals("<20130503182626.18666.16540@example.com>", $adapter->getMessageId());
        $this->assertEquals("alice@example.com", $adapter->getRecipient());
        $this->assertEquals(1473104493, $adapter->getTimestamp());
        $this->assertEquals("5.1.1 The email account that you tried to reach does not exist", $adapter->getResponse());
        $this->assertEquals(550, $adapter->getCode());

        $this->assertEquals(0, $adapter->getTags()->count());
        $this->assertFalse($adapter->getTags()->contains("tag1"));
    }

    public function testDroppedEvent()
    {
        $payload = [
            "token" => "f2af2c88dcd297eb78fcfff80cdca1ea629975cf48dcd04bfa",
            "event" => "dropped",
            "recipient" => "alice@example.com",
            "timestamp" => 1473105258,
            "code" => 605,
            "description" => "Not delivering to previously bounced address",
            "reason" => "hardfail",
            "body-plain" => "",
            "message-headers" =>  '[["Received", "by luna.mailgun.net with SMTP mgrt 8755546751405; Fri, 03 May 2013 19:26:59 +0000"], ["Content-Type", ["multipart/alternative", {"boundary": "23041bcdfae54aafb801a8da0283af85"}]], ["Mime-Version", "1.0"], ["Subject", "Test drop webhook"], ["From", "Bob <bob@example.com>"], ["To", "Alice <alice@example.com>"], ["Message-Id", "<20130503192659.13651.20287@example.com>"], ["List-Unsubscribe", "<mailto:u+na6tmy3ege4tgnldmyytqojqmfsdembyme3tmy3cha4wcndbgaydqyrgoi6wszdpovrhi5dinfzw63tfmv4gs43uomstimdhnvqws3bomnxw2jtuhusteqjgmq6tm@example.com>"], ["X-Mailgun-Sid", "WyIwNzI5MCIsICJpZG91YnR0aGlzb25lZXhpc3RzQGdtYWlsLmNvbSIsICI2Il0="], ["Date", "Fri, 03 May 2013 19:26:59 +0000"], ["Sender", "bob@example.com"]]',
            "signature" => "bd9e491ca96f5ea183f75aa8f350caf60d3b1fb9d98e58a8d3b24dc9e408fb95",
            "Message-Id" => "<20130503192659.13651.20287@example.com>",
            "attachment-count" => 1,
            "X-Mailgun-Sid" => "WyIwNzI5MCIsICJpZG91YnR0aGlzb25lZXhpc3RzQGdtYWlsLmNvbSIsICI2Il0=",
            "domain" => "example.com"
        ];

        $adapter = new \STS\EmailEventParser\Adapters\MailgunWebhook($payload);

        $this->assertEquals(\STS\EmailEventParser\EmailEvent::EVENT_DROPPED, $adapter->getType());
        $this->assertEquals("alice@example.com", $adapter->getRecipient());
        $this->assertEquals("Not delivering to previously bounced address", $adapter->getReason());
        $this->assertEquals(605, $adapter->getCode());

        $this->assertEquals(0, $adapter->getTags()->count());
        $this->assertFalse($adapter->getTags()->contains("tag1"));
        $this->assertNull($adapter->get("my-var-2"));

        $this->assertEmpty($adapter->getResponse());
    }

    public function testSpamComplaint()
    {
        $payload = [
            "message-headers" => '[["Content-Type", ["text/plain", {"charset": "us-ascii"}]], ["Mime-Version", "1.0"], ["Return-Path", "<bounce+ad27a4.345-alice=example.com@example.com>"], ["Received-Spf", "pass (mta1122.mail.sk1.example.com: domain of bc=example+example.com=example@example.com designates 173.193.210.33 as permitted sender)"], ["X-Originating-Ip", "[173.193.210.33]"], ["Authentication-Results", "mta1122.mail.sk1.example.com from=example.com; domainkeys=pass (ok); from=example.com; dkim=pass (ok)"], ["Received", "from 127.0.0.1 (EHLO mail-luna33.mailgun.org) (173.193.210.33) by mta1122.mail.sk1.example.com with SMTP; Mon, 14 Feb 2011 21:57:01 -0800"], ["Dkim-Signature", "a=rsa-sha256; v=1; c=relaxed/relaxed; d=example.com; q=dns/txt; s=mg; t=1297749420; h=MIME-Version: Subject: From: To: Date: Message-Id: List-Id: Sender: Content-Type: Content-Transfer-Encoding; bh=gYbP9hMgpeW3ea3yNJlie/Yt+URsh5LwB24aU1Oe1Uo=; b=Vr6ipa2P79dYKAtYtgZSiMXInPvthTzaQBs2XzJLEu7lc0s6bmHEApy3r2dVsI+MoJ+GtjWt pkQVbwX2ZipJsdGUigT60aiTX45ll1QG5X83N+mKR4cIDmVJD8vtwjJcLfSMdDTuOK6jI41B NSYVlT1YWPh3sh3Tdl0ZxolDlys="], ["Domainkey-Signature", "a=rsa-sha1; c=nofws; d=example.com; s=mg; h=MIME-Version: Subject: From: To: Date: Message-Id: List-Id: Sender: Content-Type: Content-Transfer-Encoding; b=QhZX2rhdVYccjPsUTMw1WASPEgsDg0KSBGHHwItsZd0xopzvgK2iQAuSJiJXo7yomFgj5R /Cz/iTv9I4Jdt6JPaEc5wf5X2JWqBCO+F1FTyYcVWzMG+WhGCdFn6sw82ma8VVY7UUU0TGsS tJe+1JkAQ1ILlm4rdXmS9jlG4H/ZE="], ["Received", "from web3 (184-106-70-82.static.cloud-ips.com [184.106.70.82]) by mxa.mailgun.org with ESMTPSA id EB508F0127B for <alice@example.com>; Tue, 15 Feb 2011 05:56:45 +0000 (UTC)"], ["Subject", "Hi Alice"], ["From", "Bob <bob@example.com>"], ["To", "Alice <alice@example.com>"], ["Date", "Tue, 15 Feb 2011 05:56:45 -0000"], ["Message-Id", "<20110215055645.25246.63817@example.com>"], ["Sender", "SRS0=1U0y=VM=example.com=example@example.com"], ["Content-Length", "629"], ["Content-Transfer-Encoding", ["7bit", {}]]]',
            "domain" => "example.com",
            "attachment-count" => 1,
            "Message-Id" => "<20110215055645.25246.63817@example.com>",
            "timestamp" => 1473106415,
            "event" => "complained",
            "token" => "9b2f9bba709c8145eb2d2b879effc99503c1238cc1c403bda3",
            "signature" => "e3ea7418ade91d73bba6d918d01843bbb4924f83ca8c731e914c7ac7a33124e8",
            "body-plain" => "",
            "recipient" => "alice@example.com"
        ];

        $adapter = new \STS\EmailEventParser\Adapters\MailgunWebhook($payload);

        $this->assertEquals(\STS\EmailEventParser\EmailEvent::EVENT_COMPLAINED, $adapter->getType());
        $this->assertEquals("9b2f9bba709c8145eb2d2b879effc99503c1238cc1c403bda3", $adapter->get('token'));
    }
}