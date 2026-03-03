<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoBase\Ui\DataProvider\Form\Modifier\CustomCanonical;

use Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product as ResourceProduct;
use Magento\Framework\App\CacheInterface;
use MageWorx\SeoAll\Helper\MagentoVersion;
use MageWorx\SeoBase\Api\CustomCanonicalRepositoryInterface;
use MageWorx\SeoBase\Model\Source\CustomCanonical\CanonicalUrlType;
use MageWorx\SeoBase\Model\Source\CustomCanonical\CmsPage as CmsPageSource;
use MageWorx\SeoBase\Model\Source\CustomCanonical\TargetStoreId as TargetStoreIdOptions;
use MageWorx\SeoBase\Model\Source\CustomCanonical\TargetTypeEntity as TargetTypeEntityOptions;
use MageWorx\SeoBase\Serializer\SerializeJson;
use MageWorx\SeoBase\Ui\DataProvider\Form\Modifier\CustomCanonical\AbstractModifier as CustomCanonicalAbstractModifier;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Framework\Registry;
use Magento\Catalog\Model\Locator\LocatorInterface;


class ProductFormModifier extends CustomCanonicalAbstractModifier
{
    /**
     * @var string
     */
    protected $dataScope = 'mageworx_seobase_product_canonical';

    /**
     * @var string
     */
    protected $currentGroup = 'search-engine-optimization';

    /**
     * @var string
     */
    protected $parentFormName = 'product_form.product_form';

    /**
     * Form prefix with separator on the end (dot)
     *
     * @var string
     */
    protected $formPrefix = 'product.';

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $currentEntity;

    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * ProductFormModifier constructor.
     *
     * @param SerializeJson $serializer
     * @param CacheInterface $cacheManager
     * @param ResourceProduct $resourceProduct
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param CmsPageSource $cmsPageSource
     * @param TargetStoreIdOptions $targetStoreIdOptions
     * @param TargetTypeEntityOptions $targetTypeEntityOptions
     * @param CanonicalUrlType $canonicalUrlType
     * @param CustomCanonicalRepositoryInterface $customCanonicalRepository
     * @param Registry $registry
     * @param MagentoVersion $helperVersion
     * @param LocatorInterface $locator
     */
    public function __construct(
        SerializeJson $serializer,
        CacheInterface $cacheManager,
        ResourceProduct $resourceProduct,
        CategoryCollectionFactory $categoryCollectionFactory,
        CmsPageSource $cmsPageSource,
        TargetStoreIdOptions $targetStoreIdOptions,
        TargetTypeEntityOptions $targetTypeEntityOptions,
        CanonicalUrlType $canonicalUrlType,
        CustomCanonicalRepositoryInterface $customCanonicalRepository,
        Registry $registry,
        MagentoVersion $helperVersion,
        LocatorInterface $locator
    ) {
        parent::__construct(
            $serializer,
            $cacheManager,
            $resourceProduct,
            $categoryCollectionFactory,
            $cmsPageSource,
            $targetStoreIdOptions,
            $targetTypeEntityOptions,
            $canonicalUrlType,
            $customCanonicalRepository,
            $registry,
            $helperVersion
        );

        $this->locator = $locator;
    }

    /**
     * @param array $meta
     * @return bool|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function initParams(array &$meta)
    {
        $this->currentEntity = $this->locator->getProduct();

        if (!$this->currentEntity) {
            return false;
        }

        $this->customCanonical = $this->customCanonicalRepository->getBySourceEntityData(
            Rewrite::ENTITY_TYPE_PRODUCT,
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
        return (int)$this->locator->getStore()->getId();
    }

    /**
     * @return int|null
     */
    protected function getEntityId()
    {
        return $this->currentEntity->getId();
    }
}
