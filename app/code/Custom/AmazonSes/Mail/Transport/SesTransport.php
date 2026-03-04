<?php

namespace Custom\AmazonSes\Mail\Transport;

use Magento\Framework\Mail\TransportInterface;
use Magento\Framework\Mail\EmailMessageInterface;
use Magento\Framework\Mail\MessageInterface;
use Custom\AmazonSes\Helper\Data;
use Aws\SesV2\SesV2Client;
use Symfony\Component\Mime\Part\TextPart;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\AbstractMultipartPart;


class SesTransport implements TransportInterface
{
    private EmailMessageInterface $message;
    private Data $helper;


    public function __construct(
        EmailMessageInterface $message,
        Data                  $helper
    )
    {
        $this->message = $message;
        $this->helper = $helper;
    }


    public function getMessage(): MessageInterface
    {
        return $this->message;
    }

    public function sendMessage()
    {
        if (!$this->helper->isEnabled()) {
            return;
        }

        $client = new SesV2Client([
            'region' => $this->helper->getRegion(),
            'version' => 'latest',
            'credentials' => [
                'key' => $this->helper->getKey(),
                'secret' => $this->helper->getSecret(),
            ],
        ]);

        $to = array_map(fn($a) => $a->getEmail(), $this->message->getTo());
        $cc = array_map(fn($a) => $a->getEmail(), $this->message->getCc() ?? []);
        $bcc = array_map(fn($a) => $a->getEmail(), $this->message->getBcc() ?? []);

        $client->sendEmail([
            'FromEmailAddress' => $this->helper->getFrom(),
            'Destination' => [
                'ToAddresses' => $to,
                'CcAddresses' => $cc,
                'BccAddresses' => $bcc,
            ],
            'Content' => [
                'Raw' => [
                    'Data' => $this->message
                        ->getSymfonyMessage()
                        ->toString(),
                ],
            ],
        ]);
    }
}
