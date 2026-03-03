<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoCrossLinks\Model;

use MageWorx\SeoCrossLinks\Model\ResourceModel\Catalog\ProductFactory;
use MageWorx\SeoCrossLinks\Model\ResourceModel\Catalog\CategoryFactory;
use MageWorx\SeoCrossLinks\Helper\Data as HelperData;
use MageWorx\SeoCrossLinks\Helper\StoreUrl as HelperStoreUrl;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Psr\Log\LoggerInterface;

class Replacer
{
    /**
     * @var \MageWorx\SeoCrossLinks\Model\ResourceModel\Catalog\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \MageWorx\SeoCrossLinks\Model\ResourceModel\Catalog\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \MageWorx\SeoCrossLinks\Helper\Data
     */
    protected $helperData;

    /**
     * @var \MageWorx\SeoCrossLinks\Helper\StoreUrl
     */
    protected $helperStoreUrl;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     *
     * @var array
     */
    protected $productDataList;

    /**
     *
     * @var array
     */
    protected $categoryDataList;

    /**
     *
     * @var array
     */
    protected $landingpageDataList;

    /** @var EventManagerInterface */
    protected $eventManager;

    /** @var \Psr\Log\LoggerInterface  */
    protected $logger;

    /**
     * The replaced pairs before converting.
     * The order is important.
     * The key is the original value, the value is the replaced value.
     * The replaced value should not contain the original value.
     *
     * @var array
     */
    protected $exceptFromConvert = array(
            '&amp;'  => '!%24#amp#%24!',
            '& '      => '!$#a#$!',
            '@click'  => '____alpine___js___click__',
            '@count'  => '____alpine___js___count__',
            '@keydown' => '____alpine___js___keydown__',
            '@keyup'   => '____alpine___js___keyup__',
            '@dblclick'   => '____alpine___js___dblclick__',
            '@submit' => '____alpine___js___submit__',
            '@auxclick' => '____alpine___js___auxclick__',
            '@context' => '____alpine___js___context__',
            '@mouseover' => '____alpine___js___mouseover__',
            '@mousemove' => '____alpine___js___mousemove__',
            '@mouseenter' => '____alpine___js___mouseenter__',
            '@mouseleave' => '____alpine___js___mouseleave__',
            '@mouseout' => '____alpine___js___mouseout__',
            '@mouseup' => '____alpine___js___mouseup__',
            '@mousedown' => '____alpine___js___mousedown__',
            '@input' => '____alpine___js___input__',
            '@scroll' => '____alpine___js___scroll__',
            '@abort' => '____alpine___js___abort__',
            '@beforeinput' => '____alpine___js___beforeinput__',
            '@blur' => '____alpine___js___blur__',
            '@compositionstart' => '____alpine___js___compositionstart__',
            '@compositionupdate' => '____alpine___js___compositionupdate__',
            '@compositionend' => '____alpine___js___compositionend__',
            '@focus' => '____alpine___js___focus__',
            '@focusin' => '____alpine___js___focusin__',
            '@focusout' => '____alpine___js___focusout__',
            '@select' => '____alpine___js___select__',
            '@error' => '____alpine___js___error__',
            '@load' => '____alpine___js___load__',
            '@unload' => '____alpine___js___unload__',
            '@wheel' => '____alpine___js___wheel__',
            '@contextmenu' => '____alpine___js___contextmenu__',
            '@change' => '____alpine___js___change__'
        );

    /**
     * Replacer constructor.
     *
     * @param LoggerInterface $logger
     * @param EventManagerInterface $eventManager
     * @param ProductFactory $productFactory
     * @param CategoryFactory $categoryFactory
     * @param HelperData $helperData
     * @param HelperStoreUrl $helperStoreUrl
     * @param UrlInterface $url
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        EventManagerInterface $eventManager,
        ProductFactory $productFactory,
        CategoryFactory $categoryFactory,
        HelperData $helperData,
        HelperStoreUrl $helperStoreUrl,
        UrlInterface $url,
        StoreManagerInterface $storeManager
    ) {
        $this->logger          = $logger;
        $this->eventManager    = $eventManager;
        $this->productFactory  = $productFactory;
        $this->categoryFactory = $categoryFactory;
        $this->helperData      = $helperData;
        $this->helperStoreUrl  = $helperStoreUrl;
        $this->url             = $url;
        $this->storeManager    = $storeManager;
    }

    /**
     * Replace keywords to links in html
     *
     * @param $collection
     * @param $html
     * @param $maxReplaceCount
     * @param null $ignoreProductSku
     * @param null $ignoreCategoryId
     * @param null $ignoreLandingPageId
     * @return bool|string
     */
    public function replace(
        $collection,
        $html,
        $maxReplaceCount,
        $ignoreProductSku = null,
        $ignoreCategoryId = null,
        $ignoreLandingPageId = null
    ) {
        if (!trim($html)) {
            return false;
        }
        $preparedHtml = $this->_prepareBeforeConvert($html);

        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;

        libxml_use_internal_errors(true);
        $dom->loadHTML($preparedHtml);
        libxml_clear_errors();

        $textParts   = array();
        $xpath       = new \DOMXPath($dom);
        $domNodeList = $xpath->query('//text()[not(ancestor::script)][not(ancestor::a)]');

        foreach ($domNodeList as $node) {
            if ($node->nodeType === 3) {
                $textParts[] = $node->wholeText;
            }
        }

        if (!$collection->getSize()) {
            return false;
        }

        $this->_specifyCollection($collection, $textParts, $maxReplaceCount);

        $pairs = array();
        $textPartsMod = $this->_dispatchByDestination(
            $collection,
            $textParts,
            $maxReplaceCount,
            $ignoreProductSku,
            $ignoreCategoryId,
            $ignoreLandingPageId,
            $pairs
        );

        foreach ($domNodeList as $node) {
            if ($node->nodeType !== 3) {
                continue;
            }

            $replace = array_shift($textPartsMod);
            $newNode = $dom->createDocumentFragment();
            $newNode->textContent = $replace;

            if (is_object($node->parentNode)) {
                $node->parentNode->replaceChild($newNode, $node);
            }
        }

        $convertedHtml = $dom->saveHTML();

        if (!$convertedHtml) {
            return false;
        }

        $modifyHtml = str_replace(array_keys($pairs), array_values($pairs), $convertedHtml);

        return $this->_recoveryAfterConvert($modifyHtml);
    }

    /**
     *
     * @param string $html
     * @return string
     */
    protected function _cropWrapper(string $html): string
    {
        return $this->getTagContentFromHtml($html, 'style', true) . $this->getTagContentFromHtml($html, 'body');
    }

    /**
     * @param string $html
     * @param string $tag
     * @param bool $includeTag
     * @return string
     */
    protected function getTagContentFromHtml(string $html, string $tag, bool $includeTag = false): string
    {
        $openingTag = "<$tag>";
        $closingTag = "</$tag>";
        $posStart   = mb_strpos($html, $openingTag);
        $posEnd     = mb_strpos($html, $closingTag);

        if ($posStart === false || $posEnd === false) {
            return '';
        }

        $start  = $includeTag ? $posStart : $posStart + strlen($openingTag);
        $length = $includeTag ? $posEnd + strlen($closingTag) - $start : $posEnd - $start;

        return mb_substr($html, $start, $length);
    }

    /**
     * Replaces certain characters
     *
     * @param type $html
     * @return type
     */
    protected function _prepareBeforeConvert($html)
    {
        $html = mb_encode_numericentity($html, [0x80, 0x10FFFF, 0, 0x1FFFFF], 'UTF-8');

        return str_replace(array_keys($this->exceptFromConvert), array_values($this->exceptFromConvert), $html);
    }

    /**
     * Recovers the characters replaced earlier
     *
     * @param string $html
     * @return string
     */
    protected function _recoveryAfterConvert($html)
    {
        $croppedHtml = $this->_cropWrapper($html);
        return str_replace(array_values($this->exceptFromConvert), array_keys($this->exceptFromConvert), $croppedHtml);
    }

    /**
     * Delegate replacements if URL exists
     *
     * @param \MageWorx\SeoCrossLinks\Model\ResourceModel\Crosslink\Collection $collection
     * @param array $textParts
     * @param int $maxGlobalCount
     * @param string $ignoreProductSku
     * @param int $ignoreCategoryId
     * @param array $pairs
     * @return array
     */
    protected function _dispatchByDestination(
        $collection,
        $textParts,
        $maxGlobalCount,
        $ignoreProductSku,
        $ignoreCategoryId,
        $ignoreLandingPageId,
        &$pairs
    ) {

        foreach ($collection->getItems() as $crosslink) {
            if (!$maxGlobalCount) {
                continue;
            }
            if ($crosslink->getRefProductSku()) {
                $productUrlData = $this->_getProductData($collection, $crosslink, $ignoreProductSku);

                if ($productUrlData) {
                    $textParts = $this->_preliminaryReplaceAndCreateReplacementPairs(
                        $textParts,
                        $crosslink,
                        $productUrlData['url'],
                        $productUrlData['name'],
                        $maxGlobalCount,
                        $pairs
                    );
                }
            } elseif ($crosslink->getRefCategoryId()) {
                $categoryUrlData = $this->_getCategoryData($collection, $crosslink, $ignoreCategoryId);
                if ($categoryUrlData) {
                    $textParts = $this->_preliminaryReplaceAndCreateReplacementPairs(
                        $textParts,
                        $crosslink,
                        $categoryUrlData['url'],
                        $categoryUrlData['name'],
                        $maxGlobalCount,
                        $pairs
                    );
                }
            } elseif ($crosslink->getRefStaticUrl()) {
                $staticUrl = $this->_getStaticUrl($crosslink);

                if ($staticUrl) {
                    $textParts = $this->_preliminaryReplaceAndCreateReplacementPairs(
                        $textParts,
                        $crosslink,
                        $staticUrl,
                        false,
                        $maxGlobalCount,
                        $pairs
                    );
                }
            } elseif ($crosslink->getRefLandingpageId()) {
                $landingPageData = $this->_getLandingpageData($collection, $crosslink, $ignoreLandingPageId);

                if ($landingPageData) {
                    $textParts = $this->_preliminaryReplaceAndCreateReplacementPairs(
                        $textParts,
                        $crosslink,
                        $landingPageData['url'],
                        $landingPageData['header'],
                        $maxGlobalCount,
                        $pairs
                    );
                }
            }
        }

        return $textParts;
    }

    /**
     * Retrive list of modified text parts ( ...keyword... => ...hash... )
     * Fill $pairs array (hash => URL)
     *
     *
     * @param array $textParts
     * @param \MageWorx\SeoCrossLinks\Model\Crosslink $crosslink
     * @param string $url
     * @param int $maxGlobalCount
     * @param array $pairs
     * @return array
     */
    protected function _preliminaryReplaceAndCreateReplacementPairs($textParts, $crosslink, $url, $name, &$maxGlobalCount, &$pairs)
    {
        $replaceCount = 0;
        if ($crosslink->getKeywords()) {
            foreach ($crosslink->getKeywords() as $keyword) {
                $availableCount = 1;

                if ($maxGlobalCount == 0) {
                    continue ;
                }

                $pattern        = $this->_getReplacePattern($keyword);
                $href           = $this->_getHtmlHref($crosslink, $keyword, $url, $name);

                for ($i= 0; $i < count($textParts); $i++) {
                    if ($maxGlobalCount == 0) {
                        break 2;
                    }

                    $key = substr(hash('md5', rand()), 0, 7);
                    $res = preg_replace($pattern, $key, $textParts[$i], $availableCount, $replaceCount);

                    if ($res && $replaceCount) {
                        $pairs[$key] = $href;
                        $availableCount -= $replaceCount;
                        $maxGlobalCount -= $replaceCount;
                        $textParts[$i] = $res;
                        break;
                    }
                }
            }
        } else {
            $keyword        = $crosslink->getKeyword();
            $availableCount = min(array($maxGlobalCount, $crosslink->getReplacementCount()));
            $pattern        = $this->_getReplacePattern($keyword);
            $href           = $this->_getHtmlHref($crosslink, $keyword, $url, $name);

            for ($i= 0; $i < count($textParts); $i++) {
                $key = substr(hash('md5', rand()), 0, 7);
                $res = preg_replace($pattern, $key, $textParts[$i], $availableCount, $replaceCount);

                if ($res && $replaceCount) {
                    $pairs[$key] = $href;
                    $availableCount -= $replaceCount;
                    $maxGlobalCount -= $replaceCount;
                    $textParts[$i] = $res;
                }
            }
        }

        return $textParts;
    }

    /**
     * Retrive product data (URL, name) if it is not current URL/product
     *
     * @param \MageWorx\SeoCrossLinks\Model\ResourceModel\Crosslink\Collection $collection
     * @param \MageWorx\SeoCrossLinks\Model\Crosslink $crosslink
     * @param string $ignoreProductSku
     * @return string
     */
    protected function _getProductData($collection, $crosslink, $ignoreProductSku)
    {
        if (is_null($this->productDataList)) {
            $prodSkuList = array();
            foreach ($collection as $item) {
                if ($item->getRefProductSku() && $item->getRefProductSku() != $ignoreProductSku) {
                    $prodSkuList[] = $item->getRefProductSku();
                }
            }

            $store     = $this->storeManager->getStore();
            $isUseName = ($this->helperData->isUseNameForTitle() !=
               \MageWorx\SeoCrossLinks\Model\CrossLink::USE_CROSSLINK_TITLE_ONLY
            );

            $this->productDataList = $this->productFactory->create()->getCollection($prodSkuList, $store, $isUseName);
        }

        if (!empty($this->productDataList[$crosslink->getRefProductSku()]['url']) &&
            !$this->_isCurrentUrl($this->productDataList[$crosslink->getRefProductSku()]['url'])
        ) {
            return $this->productDataList[$crosslink->getRefProductSku()];
        }

        return false;
    }

    /**
     * Retrive category data (URL, name) if it is not current URL/category
     *
     * @param \MageWorx\SeoCrossLinks\Model\ResourceModel\Crosslink\Collection $collection
     * @param \MageWorx\SeoCrossLinks\Model\Crosslink $crosslink
     * @param int $ignoreCategoryId
     * @return string
     */
    protected function _getCategoryData($collection, $crosslink, $ignoreCategoryId)
    {
        if (is_null($this->categoryDataList)) {
            $catIds = array();
            foreach ($collection as $item) {
                if ($item->getRefCategoryId() && $item->getRefCategoryId() != $ignoreCategoryId) {
                    $catIds[] = $item->getRefCategoryId();
                }
            }

            $store     = $this->storeManager->getStore();
            $isUseName = ($this->helperData->isUseNameForTitle() !=
               \MageWorx\SeoCrossLinks\Model\CrossLink::USE_CROSSLINK_TITLE_ONLY
            );
            $this->categoryDataList = $this->categoryFactory->create()->getCollection($catIds, $store, $isUseName);
        }

        if (!empty($this->categoryDataList[$crosslink->getRefCategoryId()]['url']) &&
            !$this->_isCurrentUrl($this->categoryDataList[$crosslink->getRefCategoryId()]['url'])
        ) {
            return $this->categoryDataList[$crosslink->getRefCategoryId()];
        }

        return false;
    }

    /**
     *  Retrive landing page data (URL, name) if it is not current landing page
     *
     * @param $collection
     * @param $crosslink
     * @param $ignoreLandingPageId
     * @return bool|mixed
     */
    protected function _getLandingpageData($collection, $crosslink, $ignoreLandingPageId)
    {
        if (is_null($this->landingpageDataList)) {
            $lpIds = array();
            foreach ($collection as $item) {
                if ($item->getRefLandingpageId() && $item->getRefLandingpageId() != $ignoreLandingPageId) {
                    $lpIds[] = $item->getRefLandingpageId();
                }
            }

            $data = new DataObject();
            $data->setIds($lpIds);
            $data->setLandingpagesData([]);
            $this->eventManager->dispatch(
                'mageworx_landingpages_get_landingpages_data',
                ['object' => $data]
            );

            $this->landingpageDataList = $data->getLandingpagesData();
        }

        if (!empty($this->landingpageDataList[$crosslink->getRefLandingpageId()]['url']) &&
            !$this->_isCurrentUrl($this->landingpageDataList[$crosslink->getRefLandingpageId()]['url'])
        ) {
            return $this->landingpageDataList[$crosslink->getRefLandingpageId()];
        }

        return false;
    }

    /**
     * Retrive URL
     *
     * @param \MageWorx\SeoCrossLinks\Model\Crosslink $crosslink
     * @return string
     */
    protected function _getStaticUrl($crosslink)
    {
        if (strpos($crosslink->getRefStaticUrl(), '://') === false) {
            $staticUrl = $this->helperStoreUrl->getUrl($crosslink->getRefStaticUrl());
        } else {
            $staticUrl = trim($crosslink->getRefStaticUrl());
        }

        if (!$this->_isCurrentUrl($staticUrl)) {
            return $staticUrl;
        }
        return false;
    }

    /**
     * Minimize collection using search keywords in text and keyword count
     *
     * @param /MageWorx/SeoCrossLinks/Model/ResourceModel/Crosslink/Collection $collection
     * @param array $textParts
     * @param int $maxReplaceAllCount
     */
    protected function _specifyCollection($collection, $textParts, $maxReplaceAllCount)
    {
        $keywords = $collection->loadKeywordsOnly();

        if ($collection->isLoaded()) {
            $this->logger->critical('Crosslink collection was loaded too early. This can cause performance problems.');
        }

        $text = implode(' ***mageworx*** ', $textParts);
        $replaceStaticUrlCount = 0;

        foreach ($keywords as $id => $keyword) {
            $replace = $this->_isCrosslinkFound($keyword, $text);
            if (!$replace) {
                unset($keywords[$id]);
            }
        }

        $collection->addFieldToFilter('crosslink_id', ['in' => array_keys($keywords)]);
        foreach ($collection->getItems() as $item) {
            if ($replaceStaticUrlCount > $maxReplaceAllCount) {
                $collection->removeItemByKey($item->getCrosslinkId());
                continue;
            }

            $replaceCount = $this->_renderCrossLink($item, $text);
            if ($item->getRefStaticUrl()) {
                $replaceStaticUrlCount += $replaceCount;
            }
        }
    }

    /**
     * Return count of matches or false.
     * Rewrite keyword value for crosslink:
     * if identical match found modify crosslink keyword "cak+" => cake
     * else create keywords property in crosslink model
     *
     * @param \MageWorx\SeoCrossLinks\Model\Crosslink $crosslink
     * @param string $text
     * @return int|false
     */
    protected function _renderCrossLink($crosslink, $text)
    {
        if (stripos($text, trim($crosslink->getKeyword(), '+')) !== false) {
            $pattern = $this->_getMatchPattern($crosslink->getKeyword());
            $matches = array();

            $res = preg_match_all($pattern, $text, $matches);

            if ($res) {
                $cropMatches = array_slice($matches[0], 0, $crosslink->getReplacementCount());
                if (count($cropMatches) == 1) {
                    $crosslink->setKeyword($cropMatches[0]);
                } else {
                    $crosslink->setKeywords($cropMatches);
                }
                return (count($cropMatches));
            }
        }

        return false;
    }

    /**
     * @param string $keyword
     * @param string $text
     * @return bool
     */
    protected function _isCrosslinkFound($keyword, $text)
    {
        if (stripos($text, trim($keyword, '+')) !== false) {
            $pattern = $this->_getMatchPattern($keyword);
            $matches = [];

            $res = preg_match_all($pattern, $text, $matches);

            if ($res) {
                return true;
            }
        }

        return false;
    }

    /**
     * Convert string to regexp
     *
     * @param string $keyword
     * @return string
     */
    protected function _getMatchPattern($keyword)
    {
        $keyword = trim($keyword);
        if (substr($keyword, 0, 1) == '+') {
            $leftPlus = true;
            $keyword  = ltrim($keyword, '+');
        }
        if (substr($keyword, -1, 1) == '+') {
            $rightPlus = true;
            $keyword   = rtrim($keyword, '+');
        }

        $keyword = preg_quote($keyword, '/');

        if (!empty($leftPlus)) {
            $keyword = '[^\s.<,]*' . $keyword;
        } else {
            $keyword = '(\b)' . $keyword;
        }

        if (!empty($rightPlus)) {
            $keyword = rtrim($keyword, '+') . '[^\s.<,]*';
        } else {
            $keyword = $keyword . '(\b)';
        }

        return '/' . $keyword . '/iu';
    }

    /**
     * Convert string to regexp
     *
     * @param string $keyword
     * @return string
     */
    protected function _getReplacePattern($keyword)
    {
        return '/(\b)' . preg_quote($keyword, '/') . '(\b)/iu';
    }

    /**
     * Retrive HTML link
     *
     * @param \MageWorx\SeoCrossLinks\Model\Crosslink $crosslink
     * @param string $keyword
     * @param string $urlRaw
     * @return string
     */
    protected function _getHtmlHref($crosslink, $keywordRaw, $urlRaw, $name)
    {
        $url     = htmlspecialchars($urlRaw, ENT_COMPAT, 'UTF-8', false);
        $target  = $crosslink->getTargetLinkValue($crosslink->getLinkTarget());

        switch ($this->helperData->isUseNameForTitle()) {
            case Crosslink::USE_CROSSLINK_TITLE_ONLY:
                $title = $crosslink->getLinkTitle();
                break;
            case Crosslink::USE_NAME_IF_EMPTY_TITLE:
                $title = trim($crosslink->getLinkTitle()) ? $crosslink->getLinkTitle() : $name;
                break;
            case Crosslink::USE_NAME_ALWAYS:
                $title = $name;
                break;
            default:
                $title = '';
        }

        $title   = htmlspecialchars(strip_tags((string)$title));
        $keyword = htmlspecialchars((string)$keywordRaw);
        $class   = $this->helperData->getLinkClass();

        $crosslinkHtml = "<a " . $class . " href=\"" . $url . "\" target=\"" . $target . "\" title=\"" . $title . "\"";
        if ($crosslink->getNofollowRel()) {
            $crosslinkHtml .= ' rel="nofollow"';
        }
        return   $crosslinkHtml . ">" . $keyword . "</a>";
    }

    /**
     * Check if input string is current URL
     *
     * @param string $url
     * @return bool
     */
    protected function _isCurrentUrl($url)
    {
        [$currentUrl] = explode('?', $this->url->getCurrentUrl());

        return (mb_substr($currentUrl, mb_strpos($currentUrl, '://')) == mb_substr($url, mb_strpos($url, '://')));
    }
}
