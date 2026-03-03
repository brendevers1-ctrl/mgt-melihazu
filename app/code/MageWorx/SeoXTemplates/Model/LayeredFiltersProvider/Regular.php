<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoXTemplates\Model\LayeredFiltersProvider;

use Magento\Catalog\Model\Layer\Resolver as LayerResolver;

class Regular implements \MageWorx\SeoXTemplates\Model\LayeredFiltersProviderInterface
{
    /**
     * @var \Magento\Catalog\Model\Layer
     */
    protected $catalogLayer;

    public function __construct(
        LayerResolver $layerResolver
    ) {
        $this->catalogLayer = $layerResolver->get();
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCurrentLayeredFilters()
    {
        $filterData = [];

        if (is_object($this->catalogLayer)
            && is_object($this->catalogLayer->getState())
            && is_array($this->catalogLayer->getState()->getFilters())
        ) {
            $appliedFilters = $this->catalogLayer->getState()->getFilters();

            if (is_array($appliedFilters) && count($appliedFilters) > 0) {
                foreach ($appliedFilters as $item) {
                    $filterData[] = [
                        'name'  => $item->getName(),
                        'label' => $item->getLabel(),
                        'code'  => $item->getFilter()->getRequestVar()
                    ];
                }
            }
        }

        return $filterData;
    }
}
