<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoBase\Ui\DataProvider\Form\Modifier\CustomCanonical;

use Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite;
use MageWorx\SeoBase\Ui\DataProvider\Form\Modifier\CustomCanonical\AbstractModifier as CustomCanonicalAbstractModifier;

class CmsPageFormModifier extends CustomCanonicalAbstractModifier
{
    /**
     * @var string
     */
    protected $dataScope = 'mageworx_seobase_cms_page_canonical';

    /**
     * @var string
     */
    protected $currentGroup = 'search_engine_optimisation';

    /**
     * @var string
     */
    protected $parentFormName = 'cms_page_form.cms_page_form';

    /**
     * @var \Magento\Cms\Model\Page
     */
    protected $currentEntity;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @param array $meta
     * @return bool|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function initParams(array &$meta)
    {
        $this->currentEntity = $this->registry->registry('cms_page');

        if (!$this->currentEntity) {
            return false;
        }

        $this->customCanonical = $this->customCanonicalRepository->getBySourceEntityData(
            Rewrite::ENTITY_TYPE_CMS_PAGE,
            $this->getEntityId(),
            $this->getStoreId()
        );

        $meta[$this->currentGroup]['children']                                     = [];
        $meta[$this->currentGroup]['arguments']['data']['config']['componentType'] =
            \Magento\Ui\Component\Form\Fieldset::NAME;

        return true;
    }

    /**
     * @return int
     */
    protected function getStoreId()
    {
        $storeIds = $this->currentEntity->getStoreId();

        if (is_array($storeIds)) {

            if (in_array('0', $storeIds)) {
                return '0';
            }

            if (!empty($storeIds)) {
                return array_shift($storeIds);
            }
        }

        return $storeIds;
    }

    /**
     * @return int|null
     */
    protected function getEntityId()
    {
        return $this->currentEntity->getId();
    }
}