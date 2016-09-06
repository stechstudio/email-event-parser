# Email Event Parser

Easily parse event data from a variety of email service providers. Currently supports SendGrid and Mailgun webhook formats.

## Installation

```
composer require stechstudio/email-event-parser
```

## Basic usage

When you know where the event is coming from, you can create the adapter yourself:

```
$event = new \STS\EmailEventParser\EmailEvent(
    new \STS\EmailEventParser\Adapters\MailgunWebhook($payload)
);
```

The adapter `$payload` should be an array of event data. It's up to you to `json_decode` this if needed. In some cases it might be appropriate to just pass in `$_POST` as the payload.

## Automatic service detection

If you are expecting webhooks from multiple service providers, you can use the automatic detection:

```
$event = \STS\EmailEventParser\EmailEvent::detect($payload);
```

This will examine the event data and figure out which service sent the event, and which adapter to use.

## API

Once you have an event object, here is a list of methods that are available:

| **Method** | **Description** |
| ---------- | --------------- |
| getType() | Event type (delivered, bounced, etc) |
| getService() | Service provider name (SendGrid, Mailgun) |
| getMessageId() | SMTP ID of the email |
| getRecipient() | Recipient email address |
| getTimestamp() | Unix timestamp for the event |
| getResponse() | Response from the remote SMTP server |
| getReason() | Additional explanation for the event type |
| getCode() | Status or error code |
| getTags() | Collection of tag values |
| getData() | Additional custom data attached to the email, as a collection of key/value pairs |
| get($key) | Retrieve any `$key` from the original event data payload |
| toArray() | Returns... ya know |
