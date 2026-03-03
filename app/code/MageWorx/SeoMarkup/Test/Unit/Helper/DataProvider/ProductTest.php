<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\SeoMarkup\Test\Unit\Helper\DataProvider;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use MageWorx\SeoMarkup\Helper\DataProvider\Product as ProductHelperDataProvider;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    private $helper;

    protected function setUp(): void
    {
        $context                     = $this->createMock(Context::class);
        $storeManager                = $this->createMock(StoreManagerInterface::class);
        $registry                    = $this->createMock(Registry::class);
        $productHelper               = $this->createMock(\MageWorx\SeoMarkup\Helper\Product::class);
        $imageBuilder                = $this->createMock(\Magento\Catalog\Block\Product\ImageBuilder::class);
        $resourceCategory            = $this->createMock(\Magento\Catalog\Model\ResourceModel\Category::class);
        $reviewFactory               = $this->createMock(\Magento\Review\Model\ReviewFactory::class);
        $reviewCollectionFactory     = $this->createMock(\Magento\Review\Model\ResourceModel\Review\CollectionFactory::class);
        $ratingVoteCollectionFactory = $this->createMock(\Magento\Review\Model\ResourceModel\Rating\Option\Vote\CollectionFactory::class);
        $timezone                    = $this->createMock(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class);
        $dateTime                    = $this->createMock(\Magento\Framework\Stdlib\DateTime::class);
        $helperVersion               = $this->createMock(\MageWorx\SeoAll\Helper\MagentoVersion::class);
        $imageUrlBuilder             = $this->createMock(\Magento\Catalog\Model\Product\Image\UrlBuilder::class);
        $getById                     = $this->createMock(\Magento\MediaGalleryApi\Model\Asset\Command\GetByIdInterface::class);
        $galleryReadHandler          = $this->createMock(\Magento\Catalog\Model\Product\Gallery\ReadHandler::class);

        $this->helper = new ProductHelperDataProvider(
            $productHelper,
            $storeManager,
            $imageBuilder,
            $imageUrlBuilder,
            $getById,
            $galleryReadHandler,
            $registry,
            $resourceCategory,
            $reviewFactory,
            $reviewCollectionFactory,
            $ratingVoteCollectionFactory,
            $context,
            $timezone,
            $dateTime,
            $helperVersion
        );
    }

    public function testCropHtmlTagsRemovesHtmlTags()
    {
        $description = '<p>This is a <strong>test</strong> description.</p>';
        $expected    = 'This is a test description.';
        $result      = $this->helper->cropHtmlTags($description);
        $this->assertEquals($expected, $result);
    }

    public function testCropHtmlTagsRemovesStyleTags()
    {
        $description = '<style>body {color: red;}</style>This is a description.';
        $expected    = 'This is a description.';
        $result      = $this->helper->cropHtmlTags($description);
        $this->assertEquals($expected, $result);
    }

    public function testCropHtmlTagsHandlesNullDescription()
    {
        $description = null;
        $expected    = '';
        $result      = $this->helper->cropHtmlTags($description);
        $this->assertEquals($expected, $result);
    }

    public function testCropHtmlTagsHandlesEmptyDescription()
    {
        $description = '';
        $expected    = '';
        $result      = $this->helper->cropHtmlTags($description);
        $this->assertEquals($expected, $result);
    }

    public function testCropHtmlTagsHandlesNoHtmlTags()
    {
        $description = 'This is a plain text description.';
        $expected    = 'This is a plain text description.';
        $result      = $this->helper->cropHtmlTags($description);
        $this->assertEquals($expected, $result);
    }

    public function testCropHtmlTagsArtur1()
    {
        $description = <<<ORIGDESCRIPTION
<style>#html-body [data-pb-style=I1GPG98]{text-align:left;border-style:dotted;border-color:#ffff0d;border-width:2px;border-radius:3px;margin-top:5px}</style></p><div data-content-type="html" data-appearance="default" data-element="main" data-pb-style="I1GPG98"><p>The sporty Joust Duffle Bag can't be beat - not in the gym, not on the luggage carousel, not anywhere. Big enough to haul a basketball or soccer ball and some sneakers with plenty of room to spare, it's ideal for athletes with places to go.<p> <ul> <li>Dual top handles.</li> <li>Adjustable shoulder strap.</li> <li>Full-length zipper.</li> <li>L 29" x W 13" x H 11".</li> </ul> <p>qweq e qwe qw ewq qw wqe</p></div>
ORIGDESCRIPTION;
        $expected    = <<<EXPECTED
The sporty Joust Duffle Bag can't be beat - not in the gym, not on the luggage carousel, not anywhere. Big enough to haul a basketball or soccer ball and some sneakers with plenty of room to spare, it's ideal for athletes with places to go. Dual top handles. Adjustable shoulder strap. Full-length zipper. L 29" x W 13" x H 11". qweq e qwe qw ewq qw wqe
EXPECTED;
        $result      = $this->helper->cropHtmlTags($description);
        $this->assertEquals($expected, $result);
    }
}
