<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoBase\Ui\DataProvider\Form\Modifier\CustomCanonical;

use MageWorx\SeoBase\Ui\DataProvider\Form\Modifier\CustomCanonical\AbstractModifier as CustomCanonicalAbstractModifier;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite;

class CategoryFormModifier extends CustomCanonicalAbstractModifier
{
    /**
     * @var string
     */
    protected $dataScope = 'mageworx_seobase_category_canonical';

    /**
     * @var string
     */
    protected $currentGroup = 'search_engine_optimization';

    /**
     * @var string
     */
    protected $parentFormName = 'category_form.category_form';

    /**
     * @var \Magento\Catalog\Model\Category
     */
    protected $currentEntity;

    /**
     * @param array $meta
     * @return bool|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function initParams(array &$meta)
    {
        $this->currentEntity = $this->registry->registry('category');

        if (!$this->currentEntity) {
            return false;
        }

        $this->customCanonical = $this->customCanonicalRepository->getBySourceEntityData(
            Rewrite::ENTITY_TYPE_CATEGORY,
            $this->getEntityId(),
            $this->getStoreId()
        );

        return true;
    }

    /**
     * @return int
     */
    protected function getStoreId()
    {
        return (int)$this->currentEntity->getStoreId();
    }

    /**
     * @return int|null
     */
    protected function getEntityId()
    {
        return $this->currentEntity->getId();
    }
}