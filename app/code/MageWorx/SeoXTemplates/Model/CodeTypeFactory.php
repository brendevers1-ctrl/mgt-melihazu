<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\SeoXTemplates\Model;

class CodeTypeFactory
{
    /**
     * @var array
     */
    protected $map;

    /**
     * CodeTypeFactory constructor.
     *
     * @param array $map
     */
    public function __construct(
        $map = []
    ) {
        $this->map = $map;
    }

    /**
     * Retrieve "text" or "html" renderer type
     *
     * @param int $code
     * @return string
     */
    public function getFieldTypeByCode(int $code): string
    {
        if (isset($this->map[$code]) && in_array($this->map[$code], ['text', 'html'])) {
            return $this->map[$code];
        }

        return 'text';
    }
}
