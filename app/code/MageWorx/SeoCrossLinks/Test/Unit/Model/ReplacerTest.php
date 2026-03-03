<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\SeoCrossLinks\Test\Unit\Model;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use MageWorx\SeoCrossLinks\Helper\Data as HelperData;
use MageWorx\SeoCrossLinks\Helper\StoreUrl as HelperStoreUrl;
use MageWorx\SeoCrossLinks\Model\Crosslink;
use MageWorx\SeoCrossLinks\Model\Replacer;
use MageWorx\SeoCrossLinks\Model\ResourceModel\Catalog\CategoryFactory;
use MageWorx\SeoCrossLinks\Model\ResourceModel\Catalog\ProductFactory;
use Psr\Log\LoggerInterface;

/**
 * Class ReplacerTest
 */
class ReplacerTest extends \PHPUnit\Framework\TestCase
{
    protected ObjectManager         $objectManager;
    protected Replacer              $replacer;
    protected LoggerInterface       $loggerMock;
    protected ManagerInterface      $eventManagerMock;
    protected ProductFactory        $productFactoryMock;
    protected CategoryFactory       $categoryFactoryMock;
    protected HelperData            $helperDataMock;
    protected HelperStoreUrl        $helperStoreUrlMock;
    protected UrlInterface          $urlInterfaceMock;
    protected StoreManagerInterface $storeManagerInterfaceMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->loggerMock          = $this->createMock(LoggerInterface::class);
        $this->eventManagerMock    = $this->createMock(ManagerInterface::class);
        $this->productFactoryMock  = $this->getMockBuilder(ProductFactory::class)
                                          ->disableOriginalConstructor()
                                          ->getMockForAbstractClass();
        $this->categoryFactoryMock = $this->getMockBuilder(CategoryFactory::class)
                                          ->disableOriginalConstructor()
                                          ->getMockForAbstractClass();
        $this->helperDataMock      = $this->createMock(HelperData::class);
        $this->helperStoreUrlMock  = $this->createMock(HelperStoreUrl::class);
        $this->urlInterfaceMock    = $this->getMockBuilder(UrlInterface::class)
                                          ->disableOriginalConstructor()
                                          ->getMockForAbstractClass();

        $this->storeManagerInterfaceMock = $this->getMockBuilder(StoreManagerInterface::class)
                                                ->disableOriginalConstructor()
                                                ->getMockForAbstractClass();

        /** @var Replacer $replacer */
        $replacer = $this->objectManager->getObject(
            Replacer::class,
            [
                'logger'          => $this->loggerMock,
                'eventManager'    => $this->eventManagerMock,
                'productFactory'  => $this->productFactoryMock,
                'categoryFactory' => $this->categoryFactoryMock,
                'helperData'      => $this->helperDataMock,
                'helperStoreUrl'  => $this->helperStoreUrlMock,
                'url'             => $this->urlInterfaceMock,
                'storeManager'    => $this->storeManagerInterfaceMock
            ]
        );

        $this->replacer = $replacer;
    }

    /**
     * @return void
     */
    public function testReplaceWhenHtmlIsEmpty(): void
    {
        $collectionMock = $this->objectManager->getCollectionMock(
            \MageWorx\SeoCrossLinks\Model\ResourceModel\Crosslink\Collection::class,
            [

            ]
        );

        $html            = '';
        $maxReplaceCount = 5;

        $result = $this->replacer->replace($collectionMock, $html, $maxReplaceCount);

        $this->assertFalse($result);
    }

    /**
     * @return void
     */
    public function testReplaceWhenCollectionIsEmpty(): void
    {
        $collection = $this->createMock(\MageWorx\SeoCrossLinks\Model\ResourceModel\Crosslink\Collection::class);
        $collection->method('getSize')->willReturn(0);
        $html            = '<p>Sample HTML content</p>';
        $maxReplaceCount = 5;

        $result = $this->replacer->replace($collection, $html, $maxReplaceCount);

        $this->assertFalse($result);
    }

    /**
     * @param string $html
     * @return void
     * @dataProvider dataProviderForTestReplaceWhenValidHtmlAndCollection
     */
    public function testReplaceWhenValidHtmlAndCollection(string $html, array $needles): void
    {
        $keywords        = [
            1 => 'keyword'
        ];
        $maxReplaceCount = 5;

        // CrossLink entity setup
        $crossLink = $this->getMockBuilder(\MageWorx\SeoCrossLinks\Model\Crosslink::class)
                          ->disableOriginalConstructor()
                          ->addMethods(['getKeywords', 'getKeyword', 'getRefStaticUrl', 'getLinkTitle'])
                          ->getMock();
        $crossLink->method('getKeywords')->willReturn(['keyword']);
        $crossLink->method('getKeyword')->willReturn('keyword');
        $crossLink->method('getLinkTitle')->willReturn('keyword title');
        $crossLink->method('getRefStaticUrl')->willReturn('http://example.com');

        $crossLinks = [
            $crossLink
        ];

        // CrossLink collection setup
        $collectionMock = $this->objectManager->getCollectionMock(
            \MageWorx\SeoCrossLinks\Model\ResourceModel\Crosslink\Collection::class,
            $crossLinks
        );
        $collectionMock->method('getSize')->willReturn(1);
        $collectionMock->method('getItems')->willReturn($crossLinks);
        $collectionMock->expects($this->once())->method('loadKeywordsOnly')
                       ->willReturn($keywords);
        $collectionMock->expects($this->once())->method('addFieldToFilter')
                       ->with('crosslink_id', ['in' => array_keys($keywords)]);

        // Setup other critical mocks for the test
        $this->urlInterfaceMock->expects($this->once())->method('getCurrentUrl')
                               ->willReturn('http://theNotExample.com');
        $this->helperDataMock->expects($this->once())->method('isUseNameForTitle')
                             ->willReturn(Crosslink::USE_CROSSLINK_TITLE_ONLY);
        $this->helperDataMock->expects($this->once())->method('getLinkClass')
                             ->willReturn('class="keyword_class"');

        // Call main method
        $result = $this->replacer->replace($collectionMock, $html, $maxReplaceCount);

        // Do assertions
        foreach ($needles as $needle) {
            $this->assertStringContainsString($needle, $result, "The needle '$needle' was not found in the result");
        }
    }

    /**
     * @return array[]
     */
    public function dataProviderForTestReplaceWhenValidHtmlAndCollection(): array
    {
        $html1 = <<<HTML
<script>
    'use strict';
    function initDatasetExample() {
        return {
            items: [
                {sku: 'Item A', price: 10, isSalable: true},
                {sku: 'Item B', price: 7, isSalable: true},
                {sku: 'Item C', price: 5, isSalable: false},
            ]
        };
    }
</script>
<div x-data="initDatasetExample()">
    <h2>Nested Components with dataset</h2>
    <div x-data="">
        <button @click.prevent="\$dispatch('name-changed', { name: 'John Doe' })">John Doe</button>
        <button @click.prevent="\$dispatch('name-changed', { name: 'Jane Doe' })">Jane Doe</button>
    </div>
    <template x-for="item in items">
        <div :data-item="JSON.stringify(item)">
            <div x-data="{open: false, item: null}"
                 x-init="item = JSON.parse(\$el.parentElement.dataset.item)">
                <button type="button" class="btn" @click="open = !open" x-text="`Show \${item.sku}`"></button>
                <p>Sample HTML content with keyword</p>
                <template x-if="open">
                    <table class="my-4">
                        <tr>
                            <th class="text-left">SKU</th>
                            <td x-text="item.sku"></td>
                        </tr>
                        <tr>
                            <th class="text-left">Price</th>
                            <td x-text="hyva.formatPrice(item.price)"></td>
                        </tr>
                        <tr>
                            <th class="text-left">Salable?</th>
                            <td x-text="item.isSalable ? 'In Stock' : 'Out of Stock'"></td>
                        </tr>
                    </table>
                </template>
            </div>
        </div>
    </template>
    <div x-data="initMethodCallExample()" @count="count()">
    <h2>Calling parent component methods</h2>
    <div class="prose">This solution uses custom events to trigger parent component methods</div>
    <template x-for="item in items">
        <div>
            <table class="my-4">
                <tr>
                    <th class="text-left">SKU</th>
                    <td x-text="item.sku"></td>
                </tr>
                <tr>
                    <th class="text-left">Price</th>
                    <td x-text="hyva.formatPrice(item.price)"></td>
                </tr>
                <tr>
                    <th class="text-left">Salable?</th>
                    <td x-text="item.isSalable ? 'In Stock' : 'Out of Stock'"></td>
                </tr>
            </table>
        </div>
        <div x-data="">
            <button type="button" @click="\$dispatch('count')">Count from nested component</button>
        </div>
    </template>
</div>
</div>
HTML;

        $html2 = <<<HTML
<div>
    <p>Sample HTML content with keyword</p>
    <button @click="open = !open">Toggle</button>
    <div @keydown="handleKeydown" @keyup="handleKeyup">
        <p>Another keyword in the content</p>
    </div>
    <div @mouseover="handleMouseover" @mouseout="handleMouseout">
        <p>More content with keyword</p>
    </div>
</div>
HTML;

        return [
            'test_data_1' => [
                'html'    => $html1,
                'needles' => [
                    'keyword',
                    '<a class="keyword_class" href="http://example.com" target="_self" title="keyword title">keyword</a>',
                    '@click="open = !open"',
                    '@count="count()',
                    '@click.prevent=',
                    ':data-item="JSON.stringify(item)"',
                    'x-data="initMethodCallExample()"',
                    '<td x-text="hyva.formatPrice(item.price)"></td>'
                ]
            ],
            'test_data_2' => [
                'html'    => $html2,
                'needles' => [
                    'keyword',
                    '<a class="keyword_class" href="http://example.com" target="_self" title="keyword title">keyword</a>',
                    '@click="open = !open"',
                    '@keydown="handleKeydown"',
                    '@keyup="handleKeyup"',
                    '@mouseover="handleMouseover"',
                    '@mouseout="handleMouseout"'
                ]
            ],
        ];
    }
}
