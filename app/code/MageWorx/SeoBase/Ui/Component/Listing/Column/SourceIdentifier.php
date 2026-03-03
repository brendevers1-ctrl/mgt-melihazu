<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoBase\Ui\Component\Listing\Column;

use MageWorx\SeoBase\Api\Data\CustomCanonicalInterface;
use Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite;

class SourceIdentifier extends \MageWorx\SeoBase\Ui\Component\Listing\Column\AbstractIdentifier
{
    /**
     * @var string
     */
    protected $entityType = CustomCanonicalInterface::SOURCE_ENTITY_TYPE;

    /**
     * @var string
     */
    protected $entityIdentifier = CustomCanonicalInterface::SOURCE_ENTITY_ID;

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        $dataSource = parent::prepareDataSource($dataSource);

        if (isset($dataSource['data']['items'])) {
            $productIds = [];
            $pageIds    = [];

            foreach ($dataSource['data']['items'] as & $item) {
                switch ($item[$this->entityType]) {
                    case Rewrite::ENTITY_TYPE_PRODUCT:
                        $productIds[] = $item[$this->entityIdentifier];
                        break;
                    case Rewrite::ENTITY_TYPE_CMS_PAGE:
                        $pageIds[] = $item[$this->entityIdentifier];
                        break;
                }
            }

            foreach ($dataSource['data']['items'] as & $item) {
                if (!isset($item[CustomCanonicalInterface::ENTITY_ID]) || !isset($item[$this->entityType])) {
                    continue;
                }

                switch ($item[$this->entityType]) {
                    case Rewrite::ENTITY_TYPE_CATEGORY:
                        $this->modifyCategoryIdentifier($item);
                        break;
                    case Rewrite::ENTITY_TYPE_PRODUCT:
                        $this->modifyProductIdentifier($item, $productIds);
                        break;
                    case Rewrite::ENTITY_TYPE_CMS_PAGE:
                        $this->modifyPageIdentifier($item, $pageIds);
                        break;
                }
            }
        }

        return $dataSource;
    }
}
