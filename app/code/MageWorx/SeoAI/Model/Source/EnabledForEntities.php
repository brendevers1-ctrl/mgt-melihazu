<?php

namespace MageWorx\SeoAI\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class EnabledForEntities implements OptionSourceInterface
{
    protected array $entities = [];
    protected array $options = [];

    /**
     * @param array $entities
     */
    public function __construct(
        array $entities = []
    ) {
        $this->entities = $entities;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        if (empty($this->options)) {
            foreach ($this->entities as $key => $label) {
                $this->options[] = [
                    'value' => $key,
                    'label' => __($label)
                ];
            }
        }

        return $this->options;
    }
}
