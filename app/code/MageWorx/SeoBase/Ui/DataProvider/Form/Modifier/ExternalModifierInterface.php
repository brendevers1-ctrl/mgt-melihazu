<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoBase\Ui\DataProvider\Form\Modifier;

interface ExternalModifierInterface
{
    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta);
}
