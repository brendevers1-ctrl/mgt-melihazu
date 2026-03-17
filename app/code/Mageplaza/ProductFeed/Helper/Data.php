<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_ProductFeed
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ProductFeed\Helper;

use DateTimeZone;
use Exception;
use Liquid\Tag\TagFor;
use Liquid\Template;
use Liquid\Variable;
use Magento\Backend\Model\UrlInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Filesystem\Io\Ftp;
use Magento\Framework\Filesystem\Io\Sftp;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Review\Model\Review;
use Magento\Review\Model\ReviewFactory;
use Magento\Review\Model\Review\SummaryFactory;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData as CoreHelper;
use Mageplaza\ProductFeed\Block\Adminhtml\LiquidFilters;
use Mageplaza\ProductFeed\Model\Config\Source\Delivery;
use Mageplaza\ProductFeed\Model\Config\Source\Events;
use Mageplaza\ProductFeed\Model\Config\Source\Status;
use Mageplaza\ProductFeed\Model\Feed;
use Mageplaza\ProductFeed\Model\FeedFactory;
use Mageplaza\ProductFeed\Model\HistoryFactory;
use RuntimeException;
use Magento\InventoryApi\Api\GetSourceItemsBySkuInterface;

/**
 * Class Data
 * @package Mageplaza\ProductFeed\Helper
 */
class Data extends CoreHelper {
	const CONFIG_MODULE_PATH = 'product_feed';
	const XML_PATH_EMAIL = 'email';
	const FEED_FILE_PATH = BP . '/pub/media/mageplaza/feed/';

	/**
	 * @var ProductFactory
	 */
	protected $productFactory;

	/**
	 * @var File
	 */
	protected $file;

	/**
	 * @var LiquidFilters
	 */
	protected $liquidFilters;

	/**
	 * @var CategoryCollectionFactory
	 */
	protected $categoryCollectionFactory;

	/**
	 * @var Ftp
	 */
	protected $ftp;

	/**
	 * @var Sftp
	 */
	protected $sftp;

	/**
	 * @var ManagerInterface
	 */
	protected $messageManager;

	/**
	 * @var DateTime
	 */
	protected $date;

	/**
	 * @var HistoryFactory
	 */
	protected $historyFactory;

	/**
	 * @var TransportBuilder
	 */
	protected $transportBuilder;

	/**
	 * @var ReviewFactory
	 */
	protected $reviewFactory;

	/**
	 * @var SummaryFactory
	 */
	protected $reviewSummaryFactory;

	/**
	 * @var FeedFactory
	 */
	protected $feedFactory;

	/**
	 * @var Resolver
	 */
	protected $resolver;

	/**
	 * @var TimezoneInterface
	 */
	protected $timezone;

	/**
	 * @var UrlInterface
	 */
	protected $backendUrl;

	/**
	 * @var StockRegistryInterface
	 */
	protected $stockState;

	/**
	 * @var PriceCurrencyInterface
	 */
	private $priceCurrency;

	/**
	 * @var CollectionFactory
	 */
	private $prdAttrCollectionFactory;

    /**
     * @var GetSourceItemsBySkuInterface
     */
	private $getSourceItemBySku;

	/**
	 * Data constructor.
	 *
	 * @param Context $context
	 * @param ObjectManagerInterface $objectManager
	 * @param StoreManagerInterface $storeManager
	 * @param ProductFactory $productFactory
	 * @param CategoryCollectionFactory $categoryCollectionFactory
	 * @param UrlInterface $backendUrl
	 * @param Ftp $ftp
	 * @param Sftp $sftp
	 * @param ManagerInterface $messageManager
	 * @param TransportBuilder $transportBuilder
	 * @param DateTime $date
	 * @param TimezoneInterface $timezone
	 * @param Resolver $resolver
	 * @param File $file
	 * @param ReviewFactory $reviewFactory
	 * @param SummaryFactory $reviewSummaryFactory
	 * @param StockRegistryInterface $stockState
	 * @param LiquidFilters $liquidFilters
	 * @param HistoryFactory $historyFactory
	 * @param FeedFactory $feedFactory
	 * @param PriceCurrencyInterface $priceCurrency
	 * @param CollectionFactory $prdAttrCollectionFactory
	 */
	public function __construct(
		Context $context,
		ObjectManagerInterface $objectManager,
		StoreManagerInterface $storeManager,
		ProductFactory $productFactory,
		CategoryCollectionFactory $categoryCollectionFactory,
		UrlInterface $backendUrl,
		Ftp $ftp,
		Sftp $sftp,
		ManagerInterface $messageManager,
		TransportBuilder $transportBuilder,
		DateTime $date,
		TimezoneInterface $timezone,
		Resolver $resolver,
		File $file,
		ReviewFactory $reviewFactory,
		SummaryFactory $reviewSummaryFactory,
		StockRegistryInterface $stockState,
		LiquidFilters $liquidFilters,
		HistoryFactory $historyFactory,
		FeedFactory $feedFactory,
		PriceCurrencyInterface $priceCurrency,
		CollectionFactory $prdAttrCollectionFactory,
        GetSourceItemsBySkuInterface $getSourceItemBySku
	) {
		$this->productFactory = $productFactory;
		$this->file = $file;
		$this->liquidFilters = $liquidFilters;
		$this->categoryCollectionFactory = $categoryCollectionFactory;
		$this->ftp = $ftp;
		$this->sftp = $sftp;
		$this->messageManager = $messageManager;
		$this->date = $date;
		$this->historyFactory = $historyFactory;
		$this->transportBuilder = $transportBuilder;
		$this->reviewFactory = $reviewFactory;
		$this->reviewSummaryFactory = $reviewSummaryFactory;
		$this->feedFactory = $feedFactory;
		$this->resolver = $resolver;
		$this->timezone = $timezone;
		$this->backendUrl = $backendUrl;
		$this->stockState = $stockState;
		$this->priceCurrency = $priceCurrency;
		$this->prdAttrCollectionFactory = $prdAttrCollectionFactory;
        $this->getSourceItemBySku = $getSourceItemBySku;

		parent::__construct($context, $objectManager, $storeManager);
	}

	/**
	 * @param $time
	 *
	 * @return \DateTime|string
	 * @throws Exception
	 */
	public function convertToLocaleTime($time) {
		$localTime = new \DateTime($time, new DateTimeZone('UTC'));
		$localTime->setTimezone(new DateTimeZone($this->timezone->getConfigTimezone()));
		$localTime = $localTime->format('Y-m-d H:i:s');

		return $localTime;
	}

	/**
	 * @param string $code
	 * @param null $storeId
	 *
	 * @return mixed
	 */
	public function getEmailConfig($code = '', $storeId = null) {
		$code = ($code !== '') ? '/' . $code : '';

		return $this->getModuleConfig(self::XML_PATH_EMAIL . $code, $storeId);
	}

	/**
	 * @param array $sendTo
	 * @param $mes
	 * @param $emailTemplate
	 * @param $storeId
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function sendMail($sendTo, $mes, $emailTemplate, $storeId) {
		if (!isset($sendTo)) {
			$this->messageManager->addErrorMessage(__('Please enter the email before send.'));

			return false;
		}
		try {
			$sendTo = array_map('trim', $sendTo);
			$this->transportBuilder
				->setTemplateIdentifier($emailTemplate)
				->setTemplateOptions([
					'area' => Area::AREA_FRONTEND,
					'store' => $storeId,
				])
				->setTemplateVars([
					'viewLogUrl' => $this->backendUrl->getUrl('mpproductfeed/logs/'),
					'mes' => $mes,
				])
				->setFrom('general');
			foreach ($sendTo as $email) {
				$this->transportBuilder->addTo($email);
			}
			$transport = $this->transportBuilder->getTransport();
			$transport->sendMessage();

			return true;
		} catch (MailException $e) {
			$this->_logger->critical($e->getLogMessage());
		} catch (Exception $e) {
			$this->messageManager->addErrorMessage(__(
				'Something went wrong while sending Email. %1',
				$e->getMessage()
			));
		}

		return false;
	}

	/**
	 * @param Feed $feed
	 * @param bool $forceGenerate
	 * @param bool $isCron
	 *
	 * @throws Exception
	 */
	public function generateAndDeliveryFeed($feed, $forceGenerate = false, $isCron = false) {
		if (!$this->isEnabled()) {
			$this->messageManager->addErrorMessage(__('Please enable Mageplaza_ProductFeed module'));

			return;
		}
		if (!$forceGenerate && !$feed->getStatus()) {
			return;
		}

		$status = Status::ERROR;
		$delivery = Delivery::ERROR;
		$productCount = 0;
		try {
			$productCount = $this->generateLiquidTemplate($feed);
			$this->messageManager->addSuccessMessage(__('%1 feed has been generated successfully.', $feed->getName()));
			$this->feedFactory->create()->load($feed->getId())->setLastGenerated($this->date->date())->save();
			$status = Status::SUCCESS;
		} catch (LocalizedException $e) {
			$this->messageManager->addErrorMessage($e->getMessage());
		} catch (RuntimeException $e) {
			$this->messageManager->addErrorMessage($e->getMessage());
		} catch (Exception $e) {
			$this->messageManager->addExceptionMessage(
				$e,
				__('Something went wrong while generating %1 Feed. %2', $feed->getName(), $e->getMessage())
			);
		}
		if ($status === Status::SUCCESS) {
			if ($feed->getDeliveryEnable()) {
				try {
					$this->deliveryFeed($feed);
					$this->messageManager->addSuccessMessage(
						__('%1 feed has been uploaded successfully', $feed->getName())
					);
					$delivery = Delivery::SUCCESS;
				} catch (LocalizedException $e) {
					$this->messageManager->addErrorMessage($e->getMessage());
				} catch (RuntimeException $e) {
					$this->messageManager->addErrorMessage($e->getMessage());
				} catch (Exception $e) {
					$this->messageManager->addErrorMessage(
						__('Something went wrong while uploading %1 Feed. %2', $feed->getName(), $e->getMessage())
					);
				}
			} else {
				$delivery = Delivery::DISABLED;
			}
		}
		$successMessage = [];
		$errorMessage = [];

		foreach ($this->messageManager->getMessages()->getItems() as $message) {
			if ($message->getType() === 'success') {
				$successMessage[] = $message->getText();
			} else {
				$errorMessage[] = $message->getText();
			}
		}
		$successMessage = implode("\n", $successMessage);
		$errorMessage = implode("\n", $errorMessage);

		if ($this->getEmailConfig('enabled')) {
			$generateStt = $status === Status::SUCCESS ? Events::GENERATE_SUCCESS : Events::GENERATE_ERROR;
			$generateMes = $generateStt === Events::GENERATE_SUCCESS
			? ('<p style="color: green">' . __('%1 feed generated successful', $feed->getName()) . '</p>')
			: ('<p style="color: red">' . __('%1 feed generated fail', $feed->getName()) . '</p>');
			$deliveryStt = $delivery === Delivery::SUCCESS
			? Events::DELIVERY_SUCCESS
			: ($delivery === Delivery::ERROR ? Events::DELIVERY_ERROR : Events::DELIVERY_DISABLED);
			$deliveryMes = $deliveryStt === Events::DELIVERY_SUCCESS
			? '<p style="color: green">' . __('%1 feed delivery successful', $feed->getName()) . '</p>'
			: ($deliveryStt === Events::DELIVERY_ERROR
				? ('<p style="color: red">' . __('%1 feed delivery fail', $feed->getName()) . '</p>') : '');
			$events = explode(',', $this->getEmailConfig('events'));
			$sendTo = empty($this->getEmailConfig('send_to'))
			? null : explode(',', $this->getEmailConfig('send_to'));
			if (in_array($generateStt, $events, true) || in_array($deliveryStt, $events, true)) {
				$this->sendMail(
					$sendTo,
					$generateMes . $deliveryMes,
					'product_feed_email_template',
					$feed->getStoreId()
				);
			}
		}

		$history = $this->historyFactory->create();
		$history->setData([
			'feed_id' => $feed->getId(),
			'feed_name' => $feed->getName(),
			'status' => $status,
			'delivery' => $delivery,
			'type' => $isCron ? 'cron' : 'manual',
			'product_count' => $productCount,
			'file' => $feed->getFileName() . '.' . $feed->getFileType(),
			'success_message' => $successMessage,
			'error_message' => $errorMessage,
		])->save();

		if ($isCron) {
			$this->messageManager->getMessages()->clear();
		}
	}

	/**
	 * @param $protocol
	 * @param $host
	 * @param $passive
	 * @param $user
	 * @param $pass
	 *
	 * @return int
	 */
	public function testConnection($protocol, $host, $passive, $user, $pass) {
		try {
			if ($protocol === 'sftp') {
				if (!isset($args['timeout'])) {
					$args['timeout'] = Sftp::REMOTE_TIMEOUT;
				}
				if (strpos($host, ':') !== false) {
					[$host, $port] = explode(':', $host, 2);
				} else {
					$port = Sftp::SSH2_PORT;
				}
				$connection = new \phpseclib\Net\SFTP($host, $port, 10);

				return $connection->login($user, $pass) ? 1 : 0;
			}

			$open = $this->ftp->open([
				'host' => $host,
				'user' => $user,
				'password' => $pass,
				'ssl' => true,
				'passive' => $passive,
			]);

			return $open ? 1 : 0;
		} catch (Exception $e) {
			return 0;
		}
	}

	/**
	 * @param Feed $feed
	 *
	 * @throws LocalizedException
	 */
	public function deliveryFeed($feed) {
		$host = $feed->getHostName();
		$username = $feed->getUserName();
		$password = $feed->getPassword();
		$timeout = '20';
		$passiveMode = $feed->getPassiveMode();
		$fileName = $feed->getFileName() . '.' . $feed->getFileType();
		$fileUrl = $this->getFileUrl($fileName);
		$directoryPath = $feed->getDirectoryPath() . $fileName;
		if ($feed->getProtocol() === 'sftp') {
			// Fix Magento bug in 2.1.x
			if (!isset($args['timeout'])) {
				$args['timeout'] = Sftp::REMOTE_TIMEOUT;
			}
			if (strpos($host, ':') !== false) {
				[$host, $port] = explode(':', $host, 2);
			} else {
				$port = Sftp::SSH2_PORT;
			}
			$connection = new \phpseclib\Net\SFTP($host, $port, $timeout);
			if (!$connection->login($username, $password)) {
				throw new RuntimeException(__('Unable to open SFTP connection as %1@%2', $username, $password));
			}
			$content = $this->file->read($fileUrl);
			$mode = is_readable($content)
			? \phpseclib\Net\SFTP::SOURCE_LOCAL_FILE : \phpseclib\Net\SFTP::SOURCE_STRING;
			$connection->put($directoryPath, $content, $mode);
			$connection->disconnect();

			//2.2.x

			//            $this->sftp->open([
			//                'host' => $host,
			//                'username' => $username,
			//                'password' => $password,
			//            ]);
			//            $content = file_get_contents($fileUrl);
			//            $this->sftp->write($directoryPath, $content);
			//            $this->sftp->close();

		} else {
			$open = $this->ftp->open([
				'host' => $host,
				'user' => $username,
				'password' => $password,
				'ssl' => true,
				'passive' => $passiveMode,
			]);
			if ($open) {
				$content = $this->file->read($fileUrl);
				$this->ftp->write($directoryPath, $content);
				$this->ftp->close();
			} else {
				throw new RuntimeException(__('Unable to authenticate with server'));
			}
		}
	}

	/**
	 * @param Feed $feed
	 *
	 * @return int
	 * @throws Exception
	 * @throws NoSuchEntityException
	 */
	public function generateLiquidTemplate($feed) {
		$template = new Template;
		$filtersMethods = $this->liquidFilters->getFiltersMethods();

		$template->registerFilter($this->liquidFilters);
		$fileType = $feed->getFileType();

		if ($fileType === 'xml') {
			$templateHtml = $feed->getTemplateHtml();
		} else {
			$fieldSeparate = $feed->getFieldSeparate() === 'tab' ? "\t"
			: ($feed->getFieldSeparate() === 'comma' ? ',' : ';');
			$fieldAround = $feed->getFieldAround() === 'none' ? ''
			: ($feed->getFieldAround() === 'quote' ? "'" : '"');
			$includeHeader = $feed->getIncludeHeader();
			$fieldsMap = self::jsonDecode($feed->getFieldsMap());
			$row = [];
			foreach ($fieldsMap as $field) {
				$row[0][] = $field['col_name'];

				if ($field['col_type'] === 'attribute') {
					$row[1][] = $fieldAround . $field['col_val'] . $fieldAround;
				} else {
					$row[1][] = $fieldAround . $field['col_pattern_val'] . $fieldAround;
				}
			}

			$row[0] = implode($fieldSeparate, $row[0]);
			$row[1] = implode($fieldSeparate, $row[1]);

			if ($includeHeader) {
				$templateHtml = $row[0] . '
' . '{% for product in products %}' . $row[1] . '
{% endfor %}';
			} else {
				$templateHtml = '{% for product in products %}' . $row[1] . '
{% endfor %}';
			}

			$templateHtml = str_replace(
				'}}',
				"| mpCorrect: '" . $feed->getFieldAround() . "', '" . $feed->getFieldSeparate() . "'}}",
				$templateHtml
			);
		}

		$filtersMethods[] = 'mpCorrect';
		$reviewCollection = $this->getReviewCollection();

		$prdAttr = [];
		$template->parse($templateHtml, $filtersMethods);
		$root = $template->getRoot();
		$prdAttr = $this->getProductAttr($root->getNodelist(), $prdAttr);
		$productCollection = $this->getProductsData($feed, $prdAttr);

		$content = $template->render([
			'products' => $productCollection,
			'store' => $this->getStoreData($feed->getStoreId()),
			'reviews' => $reviewCollection,
		]);
		$this->file->checkAndCreateFolder(self::FEED_FILE_PATH);
		$fileName = $feed->getFileName() . '.' . $feed->getFileType();
		$fileUrl = self::FEED_FILE_PATH . '/' . $fileName;
		$this->file->write($fileUrl, $content);

		return $productCollection->getSize();
	}

	/**
	 * @param $nodeList
	 * @param $prdAttr
	 *
	 * @return array
	 */
	public function getProductAttr($nodeList, $prdAttr) {
		/** @var Variable|TagFor $node */
		foreach ($nodeList as $node) {
			if (!is_object($node)) {
				continue;
			}
			if (get_class($node) === 'Liquid\Variable' && strncmp($node->getName(), 'product.', 8) === 0) {
				$prdAttr[] = str_replace('product.', '', $node->getName());
			} elseif (method_exists($node, 'getNodelist')) {
				$prdAttr = array_merge($this->getProductAttr($node->getNodelist(), $prdAttr), $prdAttr);
			}
		}

		return $prdAttr;
	}

	/**
	 * @param $id
	 *
	 * @return array
	 * @throws NoSuchEntityException
	 */
	public function getStoreData($id) {
		$store = $this->storeManager->getStore($id);
		$locale = $this->resolver->getLocale();
		$storeData = [
			'locale_code' => $locale,
			'base_url' => $store->getBaseUrl(),
		];

		return $storeData;
	}

    /**
     * @return AbstractCollection
     */
    public function getReviewCollection() {
        $collection = $this->reviewFactory->create()->getCollection();
        /** @var $review Review */
        foreach ($collection as $review) {
            $review->setUrl($review->getReviewUrl());
            $product = $this->productFactory->create()->load($review->getEntityPkValue());
            $product->setUrl($product->getProductUrl());
            $ratingSum = $this->reviewSummaryFactory
                ->create()
                ->load($review->getEntityPkValue())
                ->getRatingSummary();
            $ratingSum =$ratingSum * 5 / 100;
            $review->setRating($ratingSum);
            $review->setProduct($product);
            $dateTime = new \DateTime($review->getCreatedAt());
            $review->setCreatedAt($dateTime->format('c'));
        }

        return $collection;
    }

	/**
	 * @param Feed $feed
	 *
	 * @param array $productAttributes
	 *
	 * @return Collection
	 * @throws LocalizedException
	 * @throws NoSuchEntityException
	 */
	public function getProductsData($feed, $productAttributes = []) {
		$campaignUrl = '';
		$campaignUrl .= $feed->getCampaignSource() ? '?utm_source=' . $feed->getCampaignSource() : '';
		$campaignUrl .= $feed->getCampaignMedium() ? '&utm_medium=' . $feed->getCampaignMedium() : '';
		$campaignUrl .= $feed->getCampaignName() ? '&utm_campaign=' . $feed->getCampaignName() : '';
		$campaignUrl .= $feed->getCampaignTerm() ? '&utm_term=' . $feed->getCampaignTerm() : '';
		$campaignUrl .= $feed->getCampaignContent() ? '&utm_content=' . $feed->getCampaignContent() : '';

		$categoryMap = $this->unserialize($feed->getCategoryMap());

		$allCategory = $this->categoryCollectionFactory->create()->addAttributeToSelect('name');
		$categoriesName = [];
		/** @var $item Category */
		foreach ($allCategory as $item) {
			$categoriesName[$item->getId()] = $item->setStoreId($feed->getStoreId())->getName();
		}

		$allSelectProductAttributes = $this->prdAttrCollectionFactory->create()
			->addFieldToFilter('frontend_input', ['in' => ['multiselect', 'select']])
			->getColumnValues('attribute_code');

		$matchingProductIds = $feed->getMatchingProductIds();
		$productCollection = $this->productFactory->create()->getCollection()->addFieldToSelect('*')
			->addAttributeToSelect($productAttributes)->addStoreFilter($feed->getStoreId())
			->addFieldToFilter('entity_id', ['in' => $matchingProductIds])->addMediaGalleryData();
		/** @var $product Product */
		foreach ($productCollection as $product) {
			$typeInstance = $product->getTypeInstance();
			$childProductCollection = $typeInstance->getAssociatedProducts($product);
			if ($childProductCollection) {
				$product->setAssociatedProducts($childProductCollection);
			} else {
				$product->setAssociatedProducts([]);
			}

			$stockItem = $this->stockState->getStockItem(
				$product->getId(),
				$feed->getStoreId()
			);
			$stockAndQty = $this->getStockAndQty($stockItem,$product->getSku());
			$stockStatus = $stockAndQty['isInstock'];
			$qty = $stockAndQty['qty'];

			$categories = $product->getCategoryCollection()->addAttributeToSelect('*');
			$relatedProducts = $product->getRelatedProducts();
			$crossSellProducts = $product->getCrossSellProducts();
			$upSellProducts = $product->getUpSellProducts();
			$finalPrice = $this->convertPrice($product->getFinalPrice(), $feed->getStoreId());

			$storeId = $feed->getStoreId() ?: $this->storeManager->getDefaultStoreView()->getId();
			$product->setStoreId($storeId);
			$productLink = $product->getUrlModel()->getUrlInStore($product, ['_escape' => true]) . $campaignUrl;
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$parentId = $objectManager->create('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable')->getParentIdsByChild($product->getId());
			if (sizeof($parentId)) {
                if (!in_array($parentId[0],[7164,7601])) {
                    $parentProduct = $this->productFactory->create()->load($parentId[0]);
                    $parentProduct->setStoreId($storeId);
                    $parentUrl = $parentProduct->getUrlModel()->getUrlInStore($parentProduct, ['_escape' => true]) . $campaignUrl;
                    if ($parentUrl != '') {
                        $productLink = $parentUrl;
                    }
                    $imageUrl = $product->getImage() != '' ? $product->getImage() : $parentProduct->getImage();
                }
			} else {
				$imageUrl = $product->getImage();
			}
            $imageLink = '';
            if ($imageUrl) {
                $imageUrl = str_replace('_/', '/_/', $imageUrl);
                $imageUrl = str_replace('a/', '/a/', $imageUrl);
                $imageUrl = str_replace('m/', '/m/', $imageUrl);
                $imageUrl = str_replace('s/', '/s/', $imageUrl);
                $imageUrl = str_replace('/s//', '/s/', $imageUrl);
                $imageUrl = str_replace('//', '/', $imageUrl);
                $imageLink = $this->storeManager->getStore($feed->getStoreId())
                        ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
                    . 'catalog/product' . $imageUrl;
            }
			$images = $product->getMediaGalleryImages()->getSize() ? $product->getMediaGalleryImages() : [[]];
			/** @var $category Category */
			$lv = 0;
			$categoryPath = '';
			$cat = new DataObject();
			foreach ($categories as $category) {
				if ($lv < $category->getLevel()) {
					$lv = $category->getLevel();
					$cat = $category;
				}
			}
			$mapping = '';
			if (isset($categoryMap[$cat->getId()])) {
				$mapping = $categoryMap[$cat->getId()];
			}
            if ($cat->getPathInStore()) {
                $catPaths = array_reverse(explode(',', $cat->getPathInStore()));
                foreach ($catPaths as $index => $catId) {
                    if ($index === (count($catPaths) - 1)) {
                        $categoryPath .= isset($categoriesName[$catId]) ? $categoriesName[$catId] : '';
                    } else {
                        $categoryPath .= (isset($categoriesName[$catId]) ? $categoriesName[$catId] : '') . ' > ';
                    }
                }
            }

			$stockStatus ? $product->setData('quantity_and_stock_status', 'in stock')
			: $product->setData('quantity_and_stock_status', 'out of stock');

			$noneAttr = [
				'categoryCollection',
				'relatedProducts',
				'crossSellProducts',
				'upSellProducts',
				'final_price',
				'link',
				'image_link',
				'images',
				'category_path',
				'mapping',
				'qty',
			];

			// Convert attribute value to attribute text
			foreach ($productAttributes as $attributeCode) {
				try {
					if ($attributeCode === 'quantity_and_stock_status'
						|| in_array($attributeCode, $noneAttr, true)
						|| !in_array($attributeCode, $allSelectProductAttributes, true)
						|| !$product->getData($attributeCode)
					) {
						continue;
					}
					$attributeText = $product->getResource()->getAttribute($attributeCode)
						->setStoreId($feed->getStoreId())->getFrontend()->getValue($product);
					if (is_array($attributeText)) {
						$attributeText = implode(',', $attributeText);
					}
					if ($attributeText) {
						$product->setData($attributeCode, $attributeText);
					}
				} catch (Exception $e) {
					continue;
				}
			}

			$product->setData('categoryCollection', $categories);
			$product->setData('relatedProducts', $relatedProducts);
			$product->setData('crossSellProducts', $crossSellProducts);
			$product->setData('upSellProducts', $upSellProducts);
			$product->setData('final_price', $finalPrice);
			$product->setData('link', $productLink);
			$product->setData('image_link', $imageLink);
			$product->setData('images', $images);
			$product->setData('category_path', $categoryPath);
			$product->setData('mapping', $mapping);
			$product->setData('qty', $qty);
		}
		return $productCollection;
	}

	/**
	 * @param int $amount
	 * @param null $storeId
	 *
	 * @return float|int
	 * @throws NoSuchEntityException
	 */
	public function convertPrice($amount = 0, $storeId = null) {
		if ($storeId === null) {
			$storeId = $this->storeManager->getStore()->getStoreId();
		}

		return (float) $this->priceCurrency->convert($amount, $storeId);
	}

	/**
	 * @param $filename
	 *
	 * @return string
	 */
	public function getFileUrl($filename) {
		$fileUrl = $this->_urlBuilder->getBaseUrl([
			'_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA,
		]) . 'mageplaza/feed/' . $filename;
		$fileUrl = str_replace('cdn', 'www', $fileUrl);
		return $fileUrl;
	}

    /**
     * @param $stockItem
     * @param $sku
     * @return array
     */
	public function getStockAndQty($stockItem,$sku)
    {
        $result = [
            'qty' => 0,
            'isInstock' => 0
        ];
        $sourceItem = $this->getSourceItemBySku->execute($sku);
        if ($sourceItem) {
            foreach ($sourceItem as $item) {
                $status = $item->getStatus();
                $quantity = $item->getQuantity();
                if ($status) {
                    $result['isInstock'] = 1;
                    $result['qty'] += (int)$quantity;
                }
            }
        } else {
            $result['qty'] = $stockItem->getQty();
            $result['isInstock'] = $stockItem->getIsInStock();
        }

        return $result;
    }
}
