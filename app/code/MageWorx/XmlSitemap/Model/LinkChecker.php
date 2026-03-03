<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\XmlSitemap\Model;

use Exception;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\HTTP\ClientFactory;
use Psr\Log\LoggerInterface;

class LinkChecker
{
    /**
     * @var ClientFactory
     */
    protected $client;

    /**
     * LinkChecker constructor.
     *
     * @param ClientFactory $clientFactory
     */
    public function __construct(
        ClientFactory $clientFactory
    ) {
        $this->client = $clientFactory;
    }

    /**
     * Retrieves the array key of first URL with response code = 200 from set.
     *
     * @param array $urls
     * @param int $storeId
     * @return string|false
     */
    public function checkUrls($urls, $storeId)
    {
        foreach ($urls as $urlType => $url) {

            $logger = ObjectManager::getInstance()->get(LoggerInterface::class);
            $logger->info($url);

            try {
                $curl = $this->client->create();
                $curl->setOptions(
                    [
                        CURLOPT_NOBODY         => true,
                        CURLOPT_CONNECTTIMEOUT => 5
                    ]
                );
                $curl->get($url);
                $status = $curl->getStatus();
            } catch (Exception $exception) {
                return false;
            }

            if ($status == 200) {
                return $urlType;
            }
        }

        return false;
    }
}