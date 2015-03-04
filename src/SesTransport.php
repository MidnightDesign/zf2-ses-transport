<?php

namespace Midnight\Mail\Transport;

use Aws\Ses\SesClient;
use Zend\Mail;
use Zend\Mail\Message;
use Zend\Mail\Transport\TransportInterface;

/**
 * Class SesTransport
 *
 * @package Midnight\Mail\Transport
 */
class SesTransport implements TransportInterface
{
    /** @var SesClient */
    private $client;
    /** @var string */
    private $returnPath;

    /**
     * @param SesClient $client
     */
    public function __construct(SesClient $client)
    {
        $this->client = $client;
    }

    /**
     * Send a mail message
     *
     * @param Message $message
     */
    public function send(Message $message)
    {
        foreach ($message->getFrom() as $from) {
            $to = [];
            foreach ($message->getTo() as $t) {
                $to[] = $t->toString();
            }
            $replyTo = [];
            foreach ($message->getReplyTo() as $r) {
                $replyTo[] = $r->toString();
            }
            $data = [
                'Source' => $from->toString(),
                'Destination' => [
                    'ToAddresses' => $to,
                ],
                'Message' => [
                    'Subject' => [
                        'Data' => $message->getSubject(),
                        'Charset' => $message->getEncoding(),
                    ],
                    'Body' => [
                        'Text' => [
                            'Data' => $message->getBodyText(),
                            'Charset' => $message->getEncoding(),
                        ],
                    ],
                ],
                'ReplyToAddresses' => $replyTo,
            ];
            if ($this->returnPath) {
                $data['ReturnPath'] = $this->returnPath;
            }
            $this->client->sendEmail($data);
        }
    }

    /**
     * @param string $returnPath
     */
    public function setReturnPath($returnPath)
    {
        $this->returnPath = $returnPath;
    }
}
