<?php

namespace MageWorx\SeoAI\Model\Source\DefaultValue;

use Magento\Framework\Data\ValueSourceInterface;
use MageWorx\SeoAI\Helper\Config as ConfigProvider;

class DefaultValue implements ValueSourceInterface
{
    protected ConfigProvider $configProvider;
    protected ?string        $entity = '';

    public function __construct(
        ConfigProvider $configProvider,
        ?string        $entity = null
    ) {
        $this->configProvider = $configProvider;
        $this->entity         = $entity;
    }

    protected function getBasePath(): string
    {
        $path = 'mageworx_seo/mageworx_seoai/';
        if (!empty($this->entity)) {
            $path .= $this->entity . '/';
        }

        return $path;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($name)
    {
        $value = $this->configProvider->getValue($this->getBasePath() . $name);

        return is_numeric($value) ? (float)$value : $value;
    }
}
