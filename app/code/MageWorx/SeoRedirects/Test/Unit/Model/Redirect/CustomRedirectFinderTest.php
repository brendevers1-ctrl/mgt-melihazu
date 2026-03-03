<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\SeoRedirects\Test\Unit\Model\Redirect;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use MageWorx\SeoAll\Helper\Page as HelperPage;
use MageWorx\SeoRedirects\Helper\CustomRedirect\Data as HelperData;
use MageWorx\SeoRedirects\Model\Redirect\CustomRedirectFinder;
use MageWorx\SeoRedirects\Model\Redirect\Source\CustomRedirect\RedirectTypeRewriteFragment as RedirectTypeRewriteFragmentSource;
use MageWorx\SeoRedirects\Model\ResourceModel\Redirect\CustomRedirect\CollectionFactory as RedirectCollectionFactory;
use PHPUnit\Framework\TestCase;

class CustomRedirectFinderTest extends TestCase
{
    /**
     * @var CustomRedirectFinder
     */
    protected $customRedirectFinder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperDataMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperPageMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlFinderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirectTypeRewriteFragmentSourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirectCollectionFactoryMock;

    protected function setUp(): void
    {
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
                                       ->disableOriginalConstructor()
                                       ->getMock();

        $this->helperDataMock = $this->getMockBuilder(HelperData::class)
                                     ->disableOriginalConstructor()
                                     ->getMock();

        $this->helperPageMock = $this->getMockBuilder(HelperPage::class)
                                     ->disableOriginalConstructor()
                                     ->getMock();

        $this->urlFinderMock = $this->getMockBuilder(UrlFinderInterface::class)
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $this->redirectTypeRewriteFragmentSourceMock = $this->getMockBuilder(RedirectTypeRewriteFragmentSource::class)
                                                            ->disableOriginalConstructor()
                                                            ->getMock();

        $this->redirectCollectionFactoryMock = $this->getMockBuilder(RedirectCollectionFactory::class)
                                                    ->disableOriginalConstructor()
                                                    ->getMock();

        $objectManager              = new ObjectManager($this);
        $this->customRedirectFinder = $objectManager->getObject(
            CustomRedirectFinder::class,
            [
                'redirectCollectionFactory'         => $this->redirectCollectionFactoryMock,
                'storeManager'                      => $this->storeManagerMock,
                'urlFinder'                         => $this->urlFinderMock,
                'helperData'                        => $this->helperDataMock,
                'helperPage'                        => $this->helperPageMock,
                'redirectTypeRewriteFragmentSource' => $this->redirectTypeRewriteFragmentSourceMock,
            ]
        );
    }

    /**
     * @covers \MageWorx\SeoRedirects\Model\Redirect\CustomRedirectFinder::getRedirectInfo
     */
    public function testGetRedirectInfoWithNullRequestRewriteTargetPath()
    {
        $storeId = 1;

        $requestMock = $this->getMockBuilder(HttpRequest::class)
                            ->disableOriginalConstructor()
                            ->getMock();
        $requestMock->method('getPathInfo')
                    ->willReturn('/some/path');

        $requestRewriteMock = $this->getMockBuilder(UrlRewrite::class)
                                   ->disableOriginalConstructor()
                                   ->getMock();
        $requestRewriteMock->method('getTargetPath')
                           ->willReturn(null);

        $this->redirectTypeRewriteFragmentSourceMock->method('toArray')
                                                    ->willReturn(
                                                        [
                                                            'category' => 'category/',
                                                            'product'  => 'product/',
                                                        ]
                                                    );

        $redirectCollectionMock = $this->getMockBuilder(\MageWorx\SeoRedirects\Model\ResourceModel\Redirect\CustomRedirect\Collection::class)
                                       ->disableOriginalConstructor()
                                       ->getMock();
        $redirectCollectionMock->method('addStoreFilter')
                               ->willReturnSelf();
        $redirectCollectionMock->method('addFieldToFilter')
                               ->willReturnSelf();
        $redirectCollectionMock->method('addFrontendFilter')
                               ->willReturnSelf();
        $redirectCollectionMock->method('addDateRangeFilter')
                               ->willReturnSelf();
        $redirectCollectionMock->method('getFirstItem')
                               ->willReturn($this->createMock(\MageWorx\SeoRedirects\Model\Redirect\CustomRedirect::class));

        $this->redirectCollectionFactoryMock->method('create')
                                            ->willReturn($redirectCollectionMock);

        $result = $this->customRedirectFinder->getRedirectInfo($requestMock, $storeId, $requestRewriteMock);

        $this->assertNull($result, 'Expected null when requestRewrite->getTargetPath() returns null');
    }
}
