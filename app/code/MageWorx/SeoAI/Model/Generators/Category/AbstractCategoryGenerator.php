<?php

namespace MageWorx\SeoAI\Model\Generators\Category;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use MageWorx\OpenAI\Api\ModelInterface;
use MageWorx\OpenAI\Api\OptionsInterfaceFactory;
use MageWorx\OpenAI\Api\QueueManagementInterface;
use MageWorx\OpenAI\Api\QueueProcessManagementInterface;
use MageWorx\OpenAI\Api\RequestInterface;
use MageWorx\OpenAI\Api\RequestInterfaceFactory;
use MageWorx\OpenAI\Api\ResponseInterface;
use MageWorx\OpenAI\Model\Models\ModelsFactory;
use MageWorx\SeoAI\Api\CategoryRequestResponseLogRepositoryInterface as LogRepository;
use MageWorx\SeoAI\Api\Data\CategoryRequestResponseLogInterfaceFactory as LogFactory;
use MageWorx\SeoAI\Api\GeneratorInterface;
use MageWorx\SeoAI\Helper\Config as ModuleConfig;
use MageWorx\SeoAI\Model\Source\SeoGenerationStatuses;

abstract class AbstractCategoryGenerator implements GeneratorInterface
{
    protected ModelsFactory                   $modelsFactory;
    protected OptionsInterfaceFactory         $optionsFactory;
    protected RequestInterfaceFactory         $requestFactory;
    protected ProductRepositoryInterface      $productRepository;
    protected CategoryRepositoryInterface     $categoryRepository;
    protected QueueManagementInterface        $queueManagement;
    protected QueueProcessManagementInterface $queueProcessManagement;

    protected ModuleConfig          $moduleConfig;
    protected LogFactory            $logFactory;
    protected LogRepository         $logRepository;
    protected SeoGenerationStatuses $seoGenerationStatuses;

    protected string $type           = '';
    protected string $entityName     = 'category';
    protected array  $defaultContext = [

    ];

    protected array $variables = [
        'category_name'        => [
            'accessMethod' => 'getCategoryNameById',
            'arguments'    => ['categoryId', 'storeId']
        ],
        'category_description' => [
            'accessMethod' => 'getCategoryDescriptionById',
            'arguments'    => ['categoryId', 'storeId']
        ],
        'category_products'    => [
            'accessMethod' => 'getCategoryProductsListById',
            'arguments'    => ['categoryId', 'limit', 'storeId']
        ],
        'max_length'           => [
            'accessMethod' => 'getMaxLength',
            'arguments'    => ['storeId']
        ],
        'seo_name'             => [
            'accessMethod' => 'getDataFromEntity',
            'arguments'    => ['categoryId', 'dataKey', 'storeId']
        ],
        'meta_description'     => [
            'accessMethod' => 'getDataFromEntity',
            'arguments'    => ['categoryId', 'dataKey', 'storeId']
        ],
        'meta_title'           => [
            'accessMethod' => 'getDataFromEntity',
            'arguments'    => ['categoryId', 'dataKey', 'storeId']
        ],
        'meta_keywords'        => [
            'accessMethod' => 'getDataFromEntity',
            'arguments'    => ['categoryId', 'dataKey', 'storeId']
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
        ModuleConfig                    $moduleConfig,
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
        $this->moduleConfig           = $moduleConfig;
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
     * @inheritDoc
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

        $callback       = 'generate_category_' . $this->type;
        $dataKey        = $this->getDataKey();
        $additionalData = [
            'category_id' => $entityId,
            'store_id'    => $storeId,
            'data_key'    => $dataKey
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
            'generate_category_seoai',
            true
        );

        $queuedItems = [$queueItem];

        return $queuedItems;
    }

    /**
     * Log request and response
     *
     * @param int $categoryId
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return void
     */
    protected function log(int $categoryId, RequestInterface $request, ResponseInterface $response): void
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
                /** @var \MageWorx\SeoAI\Api\Data\CategoryRequestResponseLogInterface $log */
                $log = $this->logFactory->create();
                $log->setCategoryId($categoryId)
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
     * @param int $categoryId
     * @param int $storeId
     * @param array $selectedAttributes
     * @return string
     */
    public function generateRequestMessage(int $categoryId, int $storeId, array $selectedAttributes): string
    {
        $message = $this->getConfigValue('content', $storeId);

        return $this->prepareContentVariables(
            $message,
            [
                'categoryId'         => $categoryId,
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
     * Get category description by its id
     *
     * @param int $categoryId
     * @param int|null $storeId
     * @return string|null
     */
    public function getCategoryDescriptionById(int $categoryId, ?int $storeId = null): ?string
    {
        try {
            $category    = $this->categoryRepository->get($categoryId, $storeId);
            $description = $category->getDescription();

            return $description ? $this->cleanTags($description) : null;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Get list (as a comma separated string) of a products name of category by category id.
     *
     * @param int $categoryId
     * @param int|null $limit
     * @param int|null $storeId
     * @return string|null
     */
    public function getCategoryProductsListById(int $categoryId, ?int $limit = 10, ?int $storeId = null): ?string
    {
        try {
            if ($limit === null) {
                $limit = 10;
            }
            $category           = $this->categoryRepository->get($categoryId, $storeId);
            $productsCollection = $category->getProductCollection()
                                           ->addAttributeToSelect('name')
                                           ->setPageSize($limit)
                                           ->setCurPage(1);

            $productsName = [];
            foreach ($productsCollection as $product) {
                $productsName[] = $product->getName();
            }

            return implode(', ', $productsName);
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
            $product = $this->productRepository->get($productId, false, $storeId);
            return $product->getName();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
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
                $result  = $this->{$accessMethodData['accessMethod']}(...$arguments);
                $message = str_ireplace($variableMark, $result, $message);
            } catch (\Exception $exception) {
                continue;
            }
        }

        return $message;
    }

    /**
     * Get data from category
     *
     * @param int $categoryId The ID of the category
     * @param string $dataKey The key of the data to retrieve from the category
     * @param int|null $storeId The ID of the store (optional). If not provided, the default store will be used
     * @return string|null The value of the data as a string, or null if the data is not found or cannot be encoded as a string
     */
    private function getDataFromEntity(int $categoryId, string $dataKey, ?int $storeId = null): ?string
    {
        try {
            $category = $this->categoryRepository->get($categoryId, $storeId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }

        if (!$category instanceof \Magento\Catalog\Model\Category) {
            return null;
        }

        $data = $category->getData($dataKey);

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
        return $this->type === 'seo_name' ? 'category_seo_name' : $this->type;
    }

    /**
     * @return string
     */
    protected function getName(): string
    {
        return $this->seoGenerationStatuses->getLabelByValue($this->entityName . '_' . $this->type) ?: 'Category: ' . $this->type;
    }
}
