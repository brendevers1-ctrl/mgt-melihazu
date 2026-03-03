<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoBase\Plugin\CustomCanonicalFormUpdate;

use MageWorx\SeoBase\Ui\DataProvider\Form\Modifier\CustomCanonical\CmsPageFormModifier;

class AddToCmsPageFormPlugin
{
    /**
     * @var CmsPageFormModifier
     */
    protected $cmsPageFormModifier;

    /**
     * AddToCmsPageFormPlugin constructor.
     *
     * @param CmsPageFormModifier $cmsPageFormModifier
     */
    public function __construct(
        CmsPageFormModifier $cmsPageFormModifier
    ) {
        $this->cmsPageFormModifier = $cmsPageFormModifier;
    }

    /**
     * @param \Magento\Ui\DataProvider\AbstractDataProvider $subject
     * @param array $result
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetMeta(\Magento\Ui\DataProvider\AbstractDataProvider $subject, $result)
    {
        if ($subject instanceof \Magento\CmsStaging\Model\Page\Identifier\DataProvider) {
            return $result;
        }

        return $this->cmsPageFormModifier->modifyMeta($result);
    }
}