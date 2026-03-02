<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Hidden\ShopkartLite\Setup;

use Magento\Framework\Setup;

class Installer implements Setup\SampleData\InstallerInterface {

    /**
     * @var \Magento\CmsSampleData\Model\Page
     */
    private $page;

    /**
     * @var \Magento\CmsSampleData\Model\Block
     */
    private $block;

    /**
     * @param \Hidden\ShopkartLite\Model\Page $page
     * @param \Hidden\ShopkartLite\Model\Block $block
     */
    public function __construct(
    \Hidden\ShopkartLite\Model\Page $page, 
            \Hidden\ShopkartLite\Model\Block $block
    ) {
        $this->page = $page;
        $this->block = $block;
    }

    /**
     * {@inheritdoc}
     */
    public function install() {

        //$this->page->install(['Hidden_ShopkartLite::fixtures/pages/pages.csv']);
        $this->page->install(
                [

                    'Hidden_ShopkartLite::DemoPages/pages.csv',
                ]
        );
        $this->block->install(
                [

                    'Hidden_ShopkartLite::DemoBlocks/blocks.csv',
                ]
        );
    }

}
