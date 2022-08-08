<?php

declare(strict_types=1);

namespace MasterRO\MailViewer\Services;

use MasterRO\MailViewer\Models\MailLog;
use Symfony\Component\Mime\Email;

class Logger
{
    public function __construct(
        protected AddressParser $addressParser,
        protected HeadersParser $headersParser,
        protected AttachmentsParser $attachmentsParser,
    ) {
    }

    public function log(Email $message)
    {
        $headers = $message->getHeaders();
        if ($headers->get('X-DONT-LOG')) {
            // Don't send with this header but do not log
            $headers->remove('X-DONT-LOG');
        }else {
            MailLog::create([
                'from' => $this->addressParser->parse($message, 'From'),
                'to' => $this->addressParser->parse($message, 'To'),
                'cc' => $this->addressParser->parse($message, 'Cc'),
                'bcc' => $this->addressParser->parse($message, 'Bcc'),
                'subject' => $message->getSubject(),
                'body' => $message->getHtmlBody(),
                'payload' => $message->getBody()->toString(),
                'headers' => $this->headersParser->parse($message),
                'attachments' => $this->attachmentsParser->parse($message),
                'date' => now(config('mail-viewer.timezone', config('app.timezone', 'UTC')))->toDateTimeString(),
            ]);
        }
    }
}
