<?php

namespace MageWorx\SeoAI\Model\Generators\Product;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Store\Model\StoreManagerInterface;
use MageWorx\OpenAI\Api\ModelInterface;
use MageWorx\OpenAI\Api\OptionsInterfaceFactory;
use MageWorx\OpenAI\Api\QueueManagementInterface;
use MageWorx\OpenAI\Api\QueueProcessManagementInterface;
use MageWorx\OpenAI\Api\RequestInterface;
use MageWorx\OpenAI\Api\RequestInterfaceFactory;
use MageWorx\OpenAI\Api\ResponseInterface;
use MageWorx\OpenAI\Model\Models\ModelsFactory;
use MageWorx\SeoAI\Api\Data\ProductRequestResponseLogInterfaceFactory as LogFactory;
use MageWorx\SeoAI\Api\GeneratorInterface;
use MageWorx\SeoAI\Api\ProductRequestResponseLogRepositoryInterface as LogRepository;
use MageWorx\SeoAI\Helper\Config as ModuleConfig;
use MageWorx\SeoAI\Model\Source\SeoGenerationStatuses;

abstract class AbstractProductGenerator implements GeneratorInterface
{
    const CATEGORY_ATTRIBUTE_CODE = 'category_ids';
    const IMAGE_ATTRIBUTE_CODE    = 'media_gallery';

    protected ModelsFactory               $modelsFactory;
    protected OptionsInterfaceFactory     $optionsFactory;
    protected RequestInterfaceFactory     $requestFactory;
    protected ProductRepositoryInterface  $productRepository;
    protected CategoryRepositoryInterface $categoryRepository;
    protected ModuleConfig                $moduleConfig;
    protected PricingHelper               $pricingHelper;
    protected StoreManagerInterface       $storeManager;

    protected QueueManagementInterface        $queueManagement;
    protected QueueProcessManagementInterface $queueProcessManagement;

    protected LogFactory            $logFactory;
    protected LogRepository         $logRepository;
    protected SeoGenerationStatuses $seoGenerationStatuses;

    protected string $type           = '';
    protected string $entityName     = 'product';
    protected array  $defaultContext = [

    ];

    protected array $variables = [
        'product_attributes' => [
            'accessMethod' => 'getProductDetails',
            'arguments'    => ['productId', 'selectedAttributes', 'storeId']
        ],
        'product_name'       => [
            'accessMethod' => 'getProductNameById',
            'arguments'    => ['productId', 'storeId']
        ],
        'short_description'  => [
            'accessMethod' => 'getProductShortDescriptionById',
            'arguments'    => ['productId', 'storeId']
        ],
        'description'        => [
            'accessMethod' => 'getProductDescriptionById',
            'arguments'    => ['productId', 'storeId']
        ],
        'product_price'      => [
            'accessMethod' => 'getProductPriceById',
            'arguments'    => ['productId', 'storeId']
        ],
        'categories_list'    => [
            'accessMethod' => 'getProductCategoriesListById',
            'arguments'    => ['productId', 'storeId']
        ],
        'max_length'         => [
            'accessMethod' => 'getMaxLength',
            'arguments'    => ['storeId']
        ],
        'seo_name'           => [
            'accessMethod' => 'getDataFromEntity',
            'arguments'    => ['productId', 'dataKey', 'storeId']
        ],
        'meta_description'   => [
            'accessMethod' => 'getDataFromEntity',
            'arguments'    => ['productId', 'dataKey', 'storeId']
        ],
        'meta_title'         => [
            'accessMethod' => 'getDataFromEntity',
            'arguments'    => ['productId', 'dataKey', 'storeId']
        ],
        'meta_keyword'       => [
            'accessMethod' => 'getDataFromEntity',
            'arguments'    => ['productId', 'dataKey', 'storeId']
        ],
    ];

    public function __construct(
        ModelsFactory                   $modelsFactory,
        OptionsInterfaceFactory         $optionsFactory,
        RequestInterfaceFactory         $requestFactory,
        ProductRepositoryInterface      $productRepository,
        CategoryRepositoryInterface     $categoryRepository,
        LogFactory                      $logFactory,
        LogRepository                   $logRepository,
        PricingHelper                   $pricingHelper,
        ModuleConfig                    $moduleConfig,
        StoreManagerInterface           $storeManager,
        QueueManagementInterface        $queueManagement,
        QueueProcessManagementInterface $queueProcessManagement,
        SeoGenerationStatuses           $seoGenerationStatuses
    ) {
        $this->modelsFactory          = $modelsFactory;
        $this->optionsFactory         = $optionsFactory;
        $this->requestFactory         = $requestFactory;
        $this->productRepository      = $productRepository;
        $this->categoryRepository     = $categoryRepository;
        $this->logFactory             = $logFactory;
        $this->logRepository          = $logRepository;
        $this->pricingHelper          = $pricingHelper;
        $this->moduleConfig           = $moduleConfig;
        $this->storeManager           = $storeManager;
        $this->queueManagement        = $queueManagement;
        $this->queueProcessManagement = $queueProcessManagement;
        $this->seoGenerationStatuses  = $seoGenerationStatuses;
    }

    public function execute(
        int    $entityId,
        int    $storeId,
        string $modelType,
        array  $selectedAttributes,
        float  $temperature,
        int    $variants
    ): string {
        /**
         * Getting chatGPT3.5 model
         */
        $model = $this->modelsFactory->create($modelType);

        /**
         * Create options object
         */
        $options = $this->optionsFactory->create();

        /**
         * Fill options data
         */
        $options->setModel($model->getType())
                ->setNumberOfResultOptions($variants)
                ->setTemperature($temperature);

        /**
         * Set default attributes if client has not selected any
         */
        if (empty($selectedAttributes)) {
            $selectedAttributes = $this->moduleConfig->getValue(
                'mageworx_seo/mageworx_seoai/' . $this->entityName . '/attributes',
                $storeId
            );
            $selectedAttributes = explode(',', $selectedAttributes);
        }

        /**
         * Generate request string and context
         */
        $content = $this->generateRequestMessage($entityId, $storeId, $selectedAttributes);
        $context = $this->generateRequestContext($storeId);
        $path    = $this->getRequestPath($model);

        /**
         * Create request with content message and context
         */
        $request = $this->requestFactory->create();

        /**
         * Add data to request object
         */
        $request->setPath($path)
                ->setContent($content)
                ->setContext($context);

        /**
         * Send request through model
         */
        $response = $model->sendRequest($request, $options);

        /**
         * Getting valuable data from response
         */
        $data = $this->unpackDataFromResponse($response);

        /**
         * Log request with response
         */
        $this->log($entityId, $request, $response);

        /**
         * Saving result
         */
        $this->saveData($data);

        /**
         * Return result
         */
        return $data;
    }

    /**
     * @param int $entityId
     * @param string $modelType
     * @param float $temperature
     * @param int $variants
     * @param string $content
     * @param array $context
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function executeWithPregeneratedRequest(
        int    $entityId,
        string $modelType,
        float  $temperature,
        int    $variants,
        string $content,
        array  $context
    ): string {
        /**
         * Getting chatGPT3.5 model
         */
        $model = $this->modelsFactory->create($modelType);

        /**
         * Create options object
         */
        $options = $this->optionsFactory->create();

        /**
         * Fill options data
         */
        $options->setModel($model->getType())
                ->setNumberOfResultOptions($variants)
                ->setTemperature($temperature);

        $path = $this->getRequestPath($model);

        /**
         * Create request with content message and context
         */
        $request = $this->requestFactory->create();

        /**
         * Add data to request object
         */
        $request->setPath($path)
                ->setContent($content)
                ->setContext($context);

        /**
         * Send request through model
         */
        $response = $model->sendRequest($request, $options);

        /**
         * Getting valuable data from response
         */
        $data = $this->unpackDataFromResponse($response);

        /**
         * Log request with response
         */
        $this->log($entityId, $request, $response);

        /**
         * Saving result
         */
        $this->saveData($data);

        /**
         * Return result
         */
        return $data;
    }

    /**
     * Add a task to the queue for generating product data using chatGPT model
     *
     * @param int $entityId The ID of the entity
     * @param int $storeId The ID of the store
     * @param string $modelType The type of the model
     * @param array $selectedAttributes The selected attributes for generating the product data
     * @param float $temperature The temperature for generating the product data
     * @param int $variants The number of result options to generate
     * @param string $content The content for generating the product data (optional)
     * @param array $context The context for generating the product data (optional)
     * @return array                      The queued items
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function addToQueue(
        int    $entityId,
        int    $storeId,
        string $modelType,
        array  $selectedAttributes,
        float  $temperature,
        int    $variants,
        string $content,
        array  $context,
        string $processCode
    ): array {
        /**
         * Getting chatGPT3.5 model
         */
        $model = $this->modelsFactory->create($modelType);

        /**
         * Create options object
         */
        $options = $this->optionsFactory->create();

        /**
         * Fill options data
         */
        $options->setModel($model->getType())
                ->setNumberOfResultOptions($variants)
                ->setTemperature($temperature);

        $content = $content ?: $this->generateRequestMessage($entityId, $storeId, $selectedAttributes);
        $context = $context ?: $this->generateRequestContext($storeId);

        $callback       = 'generate_product_' . $this->type;
        $dataKey        = $this->getDataKey();
        $additionalData = [
            'product_id' => $entityId,
            'store_id'   => $storeId,
            'data_key'   => $dataKey
        ];

        $processType   = $this->entityName . '_' . $this->type;
        $processName   = $this->getName();
        $processModule = 'mageworx_seoai';

        $process   = $this->queueProcessManagement->registerProcess($processCode, $processType, $processName, $processModule, 1);
        $queueItem = $this->queueManagement->addToQueue(
            $content,
            $options,
            $callback,
            $context,
            $process,
            $additionalData,
            null,
            'generate_product_seoai',
            true
        );

        $queuedItems = [$queueItem];

        return $queuedItems;
    }

    /**
     * Log request and response
     *
     * @param int $entityId
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return void
     */
    protected function log(int $entityId, RequestInterface $request, ResponseInterface $response): void
    {
        try {
            $chatResponse = $response->getChatResponse();
            $variants     = $chatResponse['choices'] ?? [];
            foreach ($variants as $variant) {
                $respMessage  =
                    ($variant['message']['content'] ?? null)
                        ?: ($variant['text'] ?? null)
                        ?: ($variant['error']['message'] ?? 'ERROR');
                $variantIndex = $variant['index'] ?? 0;
                /** @var \MageWorx\SeoAI\Api\Data\ProductRequestResponseLogInterface $log */
                $log = $this->logFactory->create();
                $log->setProductId($entityId)
                    ->setContext($request->getContext())
                    ->setMessageType($this->type)
                    ->setRequestMessage($request->getContent())
                    ->setResponseMessage($respMessage)
                    ->setVariantIndex($variantIndex);

                $this->logRepository->save($log);
            }
        } catch (\Exception $exception) {
            // Do not break when logging leads to error
            return;
        }
    }

    /**
     * Main message for request
     *
     * @param int $entityId
     * @param int $storeId
     * @param array $selectedAttributes
     * @return string
     * @throws NoSuchEntityException
     */
    public function generateRequestMessage(int $entityId, int $storeId, array $selectedAttributes): string
    {
        $message = $this->getConfigValue('content', $storeId);

        return $this->prepareContentVariables(
            $message,
            [
                'productId'          => $entityId,
                'storeId'            => $storeId,
                'selectedAttributes' => $selectedAttributes
            ]
        );
    }

    /**
     * Context for request
     *
     * @param int $storeId
     * @return string[]
     */
    public function generateRequestContext(int $storeId): array
    {
        $context = $this->getConfigValue('context', $storeId);
        $context = trim($context);
        $context = explode("\n", $context);

        return !empty($context) && is_array($context) ? $context : $this->defaultContext;
    }

    /**
     * Request path
     *
     * @param ModelInterface $model
     * @return string
     */
    protected function getRequestPath(ModelInterface $model): string
    {
        return $model->getPath();
    }

    /**
     * Product description based on the attributes
     *
     * @param int $productId
     * @param array $selectedAttributes
     * @return string
     * @throws NoSuchEntityException
     */
    protected function getProductDetails(int $productId, array $selectedAttributes, ?int $storeId = null): string
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->getById($productId, false, $storeId);

        $attributes = $product->getAttributes();

        $attributeStrings = []; // @TODO: possible to add required attributes?
        /** @var AbstractAttribute $attribute */
        foreach ($attributes as $attribute) {
            if (in_array($attribute->getAttributeCode(), $selectedAttributes)) {
                if ($attribute->getAttributeCode() === static::IMAGE_ATTRIBUTE_CODE) {
                    continue;
                }

                $value = $attribute->getFrontend()->getValue($product);
                if (empty($value)) {
                    continue;
                }

                if ($attribute->getAttributeCode() === static::CATEGORY_ATTRIBUTE_CODE && is_array($value)) {
                    $categoryLabels = [];
                    foreach ($value as $categoryId) {
                        $categoryLabels[] = $this->getCategoryNameById($categoryId);
                    }

                    $value = array_filter($categoryLabels);
                }

                if ($attribute->getFrontendInput() === 'price') {
                    $priceValue = $product->getData($attribute->getAttributeCode());
                    $value      = $this->pricingHelper->currencyByStore(
                        $priceValue,
                        $storeId,
                        true,
                        false
                    );
                }

                if (is_array($value)) {
                    $value = implode(',', $value);
                }

                // Clean value from tags
                $value = $this->cleanTags($value);

                $label              = $attribute->getFrontendLabel();
                $attributeStrings[] = $label . ': ' . $value;
            }
        }

        return implode('; ', $attributeStrings);
    }

    /**
     * Get category name by ID
     *
     * @param int $categoryId
     * @param int|null $storeId
     * @return string|null
     */
    public function getCategoryNameById(int $categoryId, ?int $storeId = null): ?string
    {
        try {
            $category = $this->categoryRepository->get($categoryId, $storeId);
            return $category->getName();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Get product name by ID
     *
     * @param int $productId
     * @param int|null $storeId
     * @return string|null
     */
    public function getProductNameById(int $productId, ?int $storeId = null): ?string
    {
        try {
            $product = $this->productRepository->getById($productId, false, $storeId);
            return $product->getName();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Get product short description by ID
     *
     * @param int $productId
     * @param int|null $storeId
     * @return string|null
     */
    public function getProductShortDescriptionById(int $productId, ?int $storeId = null): ?string
    {
        try {
            $product = $this->productRepository->getById($productId, false, $storeId);
            return $product->getShortDescription();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Get product description by ID
     *
     * @param int $productId
     * @param int|null $storeId
     * @return string|null
     */
    public function getProductDescriptionById(int $productId, ?int $storeId = null): ?string
    {
        try {
            $product = $this->productRepository->getById($productId, false, $storeId);
            return $product->getDescription();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Get product price by ID
     *
     * @param int $productId
     * @param int|null $storeId
     * @return string|null
     */
    public function getProductPriceById(int $productId, ?int $storeId = null): ?string
    {
        try {
            $product = $this->productRepository->getById($productId, false, $storeId);

            return $this->pricingHelper->currencyByStore(
                $product->getPrice(),
                $storeId,
                true,
                false
            );
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Get product categories list by ID (comma separated string)
     *
     * @param int $productId
     * @param int|null $storeId
     * @return string|null
     */
    public function getProductCategoriesListById(int $productId, ?int $storeId = null): ?string
    {
        try {
            // If store ID is not provided, get the default store ID
            if ($storeId === null) {
                $storeId = $this->storeManager->getDefaultStoreView()->getId();
            }

            $product     = $this->productRepository->getById($productId, false, $storeId);
            $categoryIds = $product->getCategoryIds();

            $categoryNames = [];
            foreach ($categoryIds as $categoryId) {
                $category        = $this->categoryRepository->get($categoryId, $storeId);
                $categoryNames[] = $category->getName();
            }

            return implode(', ', $categoryNames);
        } catch (\Exception $e) {
            // Log the exception or handle it as per your requirement
            return null;
        }
    }

    /**
     * @param int|null $storeId
     * @return int
     */
    public function getMaxLength(?int $storeId = null): int
    {
        return (int)$this->getConfigValue('max_length', $storeId);
    }

    /**
     * Get main content from response
     *
     * @param ResponseInterface $response
     * @return string
     */
    protected function unpackDataFromResponse(ResponseInterface $response): string
    {
        return json_encode($response->getChatResponse());
    }

    protected function saveData(string $value): void
    {
        //@TODO save
    }

    /**
     * Clean string from tags
     *
     * @param string $value
     * @return string
     */
    protected function cleanTags(string $value): string
    {
        $value = preg_replace('/(<script[^>]*>.+?<\/script>|<style[^>]*>.+?<\/style>)/is', '', $value);
        $value = strip_tags(html_entity_decode($value));

        return $value;
    }

    /**
     * @param string $field
     * @param int|null $storeId
     * @return string
     */
    protected function getConfigValue(string $field, ?int $storeId = null): string
    {
        return $this->moduleConfig->getValue(
            'mageworx_seo/mageworx_seoai/' . $this->entityName . '/' . $this->type . '/' . $field,
            $storeId
        );
    }

    /**
     * Search and replace variables in content message
     *
     * @param string $message
     * @param array|null $anySubArguments
     * @return string
     */
    protected function prepareContentVariables(
        string $message,
        ?array $anySubArguments = []
    ): string {
        foreach ($this->variables as $variable => $accessMethodData) {
            $variableMark = '{{' . $variable . '}}';

            if (stripos($message, $variableMark) === false) {
                continue;
            }

            if (empty($accessMethodData['accessMethod'])) {
                continue;
            }

            if (!method_exists($this, $accessMethodData['accessMethod'])) {
                continue;
            }

            $anySubArguments['dataKey'] = $variable;

            $arguments = [];
            foreach ($accessMethodData['arguments'] as $argumentKey) {
                $arguments[] = $anySubArguments[$argumentKey] ?? null;
            }

            try {
                $result = $this->{$accessMethodData['accessMethod']}(...$arguments);
                if (!empty($result)) {
                    if (is_string($result)) {
                        $result = $this->cleanTags($result);
                    }
                    $message = str_ireplace($variableMark, $result, $message);
                }
            } catch (\Exception $exception) {
                continue;
            }
        }

        return $message;
    }

    /**
     * Get data from entity by product ID and data key
     *
     * @param int $productId The product ID
     * @param string $dataKey The key of the data to retrieve
     * @param int|null $storeId The store ID (optional)
     * @return string|null The retrieved data as a string or null if the entity or data does not exist
     */
    private function getDataFromEntity(int $productId, string $dataKey, ?int $storeId = null): ?string
    {
        try {
            $product = $this->productRepository->get($productId, $storeId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }

        if (!$product instanceof \Magento\Catalog\Model\Product) {
            return null;
        }

        $data = $product->getData($dataKey);

        return $this->stringifyData($data);
    }

    /**
     * Converts data into a string if it can be encoded to a string,
     * otherwise returns null.
     *
     * @param mixed $data
     * @return string|null
     */
    private function stringifyData($data): ?string
    {
        if (is_array($data) || is_object($data)) {
            $data = json_encode($data) ?: null;
        }

        return is_string($data) || $data !== null ? (string)$data : null;
    }

    /**
     * @return string
     */
    private function getDataKey(): string
    {
        return $this->type === 'seo_name' ? 'product_seo_name' : $this->type;
    }

    /**
     * @return string
     */
    protected function getName(): string
    {
        return $this->seoGenerationStatuses->getLabelByValue($this->entityName . '_' . $this->type) ?: 'Product: ' . $this->type;
    }
}
