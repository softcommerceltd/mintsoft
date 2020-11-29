<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\MintSoft\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Helper\ImageFactory as ProductHelperImageFactory;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\StringUtils;

use SoftCommerce\MintSoft\Http\ClientInterface;
use SoftCommerce\MintSoft\Api;
use SoftCommerce\MintSoft\Helper\Data as Helper;
use SoftCommerce\MintSoft\Logger\Logger;
use SoftCommerce\Core\Model\Source\Status;

/**
 * Class ProductExportSubmitManagement
 * @package SoftCommerce\MintSoft\Model
 */
class ProductExportSubmitManagement extends ProductExportAbstractManagement
    implements Api\ProductExportSubmitManagementInterface
{
    /**
     * @var ClientInterface
     */
    private $_client;

    /**
     * @var array
     */
    private $_clientResponse;

    /**
     * @var ProductRepositoryInterface
     */
    private $_productRepository;

    /**
     * @var ProductHelperImageFactory
     */
    private $_productHelperImageFactory;

    /**
     * @var array
     */
    private $_processedEntries;

    /**
     * @var Product
     */
    private $_product;

    /**
     * @var array
     */
    private $_searchCriteria;

    /**
     * @var StringUtils
     */
    private $_string;

    /**
     * ProductExportSubmitManagement constructor.
     * @param ClientInterface $client
     * @param ProductRepositoryInterface $productRepository
     * @param ProductHelperImageFactory $productHelperImageFactory
     * @param Api\ProductExportRepositoryInterface $productExportRepository
     * @param FilterBuilder $filterBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Helper $helper
     * @param DateTime $dateTime
     * @param Logger $logger
     * @param StringUtils $stringUtils
     * @param Json|null $serializer
     */
    public function __construct(
        ClientInterface $client,
        ProductRepositoryInterface $productRepository,
        ProductHelperImageFactory $productHelperImageFactory,
        Api\ProductExportRepositoryInterface $productExportRepository,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Helper $helper,
        DateTime $dateTime,
        Logger $logger,
        StringUtils $stringUtils,
        ?Json $serializer = null
    ) {
        $this->_client = $client;
        $this->_productRepository = $productRepository;
        $this->_productHelperImageFactory = $productHelperImageFactory;
        $this->_string = $stringUtils;
        parent::__construct($productExportRepository, $filterBuilder, $filterGroupBuilder, $searchCriteriaBuilder, $helper, $dateTime, $logger, $serializer);
    }

    /**
     * @return string|null
     * @throws LocalizedException
     * @throws \Exception
     */
    public function getLastCollectedAt()
    {
        return $this->_formatDateTime(
            $this->_productExportRepository->getLastUpdatedAt()
        );
    }

    /**
     * @param bool $keys
     * @return array
     */
    public function getSearchCriteria($keys = false)
    {
        return false === $keys
            ? $this->_searchCriteria
            : array_keys($this->_searchCriteria);
    }

    /**
     * @param array $productEntityId
     * @return $this
     */
    public function setSearchCriteria(array $productEntityId)
    {
        $this->_searchCriteria = $productEntityId;
        return $this;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $this->_executeBefore();

        $pageSize = 100;
        $currentPage = 1;
        $searchCriteria = $this->_searchCriteriaBuilder
            ->addFilter(Api\Data\ProductExportInterface::ENTITY_ID, $this->getSearchCriteria(true), 'in')
            ->create()
            ->setCurrentPage($currentPage)
            ->setPageSize($pageSize);

        $collection = $this->_productRepository->getList($searchCriteria);
        if (!$collection->getTotalCount()) {
            $this->addResponse('Products are up-to-date.', Status::COMPLETE);
            return $this;
        }

        $totalCount = (int) $collection->getTotalCount();
        while ($totalCount > 0) {
            try {
                $this->_processMultiple($collection->getItems());
            } catch (\Exception $e) {
                $this->addResponse([__METHOD__ => $e->getMessage()], Status::ERROR);
            }

            $searchCriteria = $collection->getSearchCriteria()
                ->setCurrentPage(++$currentPage)
                ->setPageSize($pageSize);
            $collection = $this->_productRepository->getList($searchCriteria);
            $totalCount = $totalCount - $pageSize;
        }

        $this->_executeAfter();

        return $this;
    }

    /**
     * @return $this
     */
    private function _executeBefore()
    {
        $this->_response = [];
        if (empty($this->getSearchCriteria())) {
            $this->setSearchCriteria(
                $this->_productExportRepository->getPendingRecords()
            );
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function _executeAfter()
    {
        if (!$this->_helper->getIsActiveDebug()) {
            return $this;
        }

        $context = [];

        if ($request = $this->getRequest()) {
            $context['request'][] = $request;
        }

        if ($response = $this->getResponse()) {
            $context['response'][] = $response;
        }

        if (empty($context)) {
            return $this;
        }

        if ($this->_helper->getIsDebugPrintToArray()) {
            $this->_logger->debug(print_r([__METHOD__ => $context], true), []);
        } else {
            $this->_logger->debug(__METHOD__, $context);
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function _processMultipleBefore()
    {
        $this->_processedEntries = [];
        return $this;
    }

    /**
     * @param array $products
     * @return $this
     * @throws LocalizedException
     */
    private function _processMultiple(array $products)
    {
        $this->_processMultipleBefore();

        /** @var Product $product */
        foreach ($products as $product) {
            try {
                $this->_processBefore($product)
                    ->_process()
                    ->_processAfter();
            } catch (\Exception $e) {
                $this->_setProcessedEntry(Status::ERROR, $e->getMessage())
                    ->setResponse($e->getMessage(), Status::ERROR);
            }
        }

        $this->_processMultipleAfter();

        return $this;
    }

    /**
     * @return $this
     * @throws CouldNotSaveException
     */
    private function _processMultipleAfter()
    {
        if (empty($this->_processedEntries)) {
            return $this;
        }

        $this->_productExportRepository->saveMultiple($this->_processedEntries);
        return $this;
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    private function _process()
    {
        $this->_generateRequest()
            ->_export();

        return $this;
    }

    /**
     * @param Product $product
     * @return $this
     */
    private function _processBefore(Product $product)
    {
        $this->_error =
        $this->_request =
        $this->_clientResponse =
            [];
        $this->_product = $product;
        return $this;
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    private function _processAfter()
    {
        if (isset($this->_clientResponse['Error'])) {
            throw new LocalizedException(
                __('Could not export product SKU: "%1".%2', $this->_getProduct()->getSku(), $this->_clientResponse['Message'] ?? null)
            );
        }

        $status = isset($this->_clientResponse['Success']) && true === $this->_clientResponse['Success']
            ? Status::COMPLETE
            : (isset($this->_clientResponse['Success']) && false === $this->_clientResponse['Success'] ? Status::NOTICE : Status::ERROR);
        $message = isset($this->_clientResponse['Message'])
            ? $this->_clientResponse['Message']
            : __('Product SKU: "%1" has been exported.', $this->_getProduct()->getSku());

        if (isset($this->_clientResponse['ProductId'])) {
            $externalId = $this->_clientResponse['ProductId'];
        } elseif (isset($this->_clientResponse['ID'])) {
            $externalId = $this->_clientResponse['ID'];
        } else {
            throw new LocalizedException(
                __('Could not retrieve response data for product SKU: "%1".', $this->_getProduct()->getSku())
            );
        }

        $this->_setExternalId($externalId)
            ->_setProcessedEntry($status, $message)
            ->addResponse(
                __('SKU "%1" has been exported %2. Response: %3',
                    $this->_getProduct()->getSku(),
                    $status === Status::ERROR
                        ? 'with errors'
                        : null,
                    $message
                ),
                $status
            );

        return $this;
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    private function _generateRequest()
    {
        $product = $this->_getProduct();

        $this->_request = [
            'SKU' => $product->getSku(),
            'Name' => $product->getName(),
            'Description' => $product->getShortDescription(),
            'CustomsDescription' => $this->_string->substr(strip_tags($product->getData('customs_description')), 0, 49),
            'CountryOfManufactureId' => $product->getData('country_of_manufacture'),
            'EAN' => $product->getData('eannumber'),
            'Weight' => $product->getWeight() / 1000,
            'Height' => $product->getData('height'),
            'Width' => $product->getData('fit'),
            'Price' => $product->getPrice(),
            'CommodityCode' => [
                'Code' => $product->getData('commodity_code')
            ],
            'ImageURL' => $product->getImage()
                ? (string) $this->_productHelperImageFactory
                    ->create()
                    ->init($product, 'product_page_image_large')
                    ->setImageFile($product->getImage())
                    ->getUrl()
                : ''
        ];

        if ($externalId = $this->_getExternalId()) {
            $this->_request['ID'] = $externalId;
        }

        return $this;
    }

    /**
     * @return $this
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function _export()
    {
        if (empty($this->getRequest())) {
            return $this;
        }

        $this->_client->execute(
            $this->_helper->getApiProductUrl(),
            $this->getRequest(),
            $this->_getExternalId() ? ClientInterface::POST : ClientInterface::PUT
        );

        $this->_clientResponse = $this->_client->getResponseContents(true);

        return $this;
    }

    /**
     * @return Product
     * @throws LocalizedException
     */
    private function _getProduct()
    {
        if (null === $this->_product) {
            throw new LocalizedException(__('Product is not set.'));
        }

        return $this->_product;
    }

    /**
     * @return int|null
     * @throws LocalizedException
     */
    private function _getExternalId()
    {
        return $this->_searchCriteria[$this->_getProduct()->getId()] ?? null;
    }

    /**
     * @param int $externalId
     * @return $this
     * @throws LocalizedException
     */
    private function _setExternalId($externalId)
    {
        if ($this->_getExternalId()) {
            return $this;
        }

        $this->_searchCriteria[$this->_getProduct()->getId()] = $externalId;
        return $this;
    }

    /**
     * @param $status
     * @param $message
     * @return $this
     * @throws LocalizedException
     */
    private function _setProcessedEntry($status, $message)
    {
        $this->_processedEntries[] = [
            Api\Data\ProductExportInterface::ENTITY_ID => $this->_getProduct()->getId(),
            Api\Data\ProductExportInterface::SKU => $this->_getProduct()->getSku(),
            Api\Data\ProductExportInterface::EXTERNAL_ID => $this->_getExternalId(),
            Api\Data\ProductExportInterface::STATUS => $status,
            Api\Data\ProductExportInterface::MESSAGE => $message,
            Api\Data\ProductExportInterface::REQUEST_ENTRY => $this->_serializer->serialize($this->getRequest()),
            Api\Data\ProductExportInterface::RESPONSE_ENTRY => $this->_serializer
                ->serialize(is_array($this->_clientResponse) ? $this->_clientResponse : [$this->_clientResponse]),
            Api\Data\ProductExportInterface::UPDATED_AT => $this->_dateTime->gmtDate()
        ];

        return $this;
    }

    /**
     * @param $needle
     * @param array $haystack
     * @param $columnName
     * @param null $columnId
     * @return false|int|string
     */
    public function getSearchArrayMatch(
        $needle,
        array $haystack,
        $columnName,
        $columnId = null
    ) {
        return array_search($needle, array_column($haystack, $columnName, $columnId));
    }


    /**
     * @param string|null $dateTime
     * @return string|null
     * @throws \Exception
     */
    private function _formatDateTime($dateTime)
    {
        $w3cResult = null;
        if (strtotime($dateTime) > 0) {
            $w3cResult = $this->_helper->getDateTimeLocale($dateTime);
        }

        return $w3cResult;
    }
}
