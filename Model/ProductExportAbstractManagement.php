<?php
/**
 * Copyright © Soft Commerce Ltd, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SoftCommerce\MintSoft\Model;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use SoftCommerce\MintSoft\Api;
use SoftCommerce\MintSoft\Helper\Data as Helper;
use SoftCommerce\MintSoft\Logger\Logger;

/**
 * Class ProductExportAbstractManagement
 * @package SoftCommerce\MintSoft\Model
 */
abstract class ProductExportAbstractManagement
{
    /**
     * @var DateTime
     */
    protected $_dateTime;

    /**
     * @var array
     */
   protected $_error;

    /**
     * @var array
     */
    protected $_request;

    /**
     * @var array
     */
    protected $_response;

    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * @var null|Logger
     */
    protected $_logger;

    /**
     * @var Api\ProductExportRepositoryInterface
     */
    protected $_productExportRepository;

    /**
     * @var FilterBuilder
     */
    protected $_filterBuilder;

    /**
     * @var FilterGroupBuilder
     */
    protected $_filterGroupBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;

    /**
     * @var Json
     */
    protected $_serializer;

    /**
     * ProductExportAbstractManagement constructor.
     * @param Api\ProductExportRepositoryInterface $productExportRepository
     * @param FilterBuilder $filterBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Helper $helper
     * @param DateTime $dateTime
     * @param Logger $logger
     * @param Json|null $serializer
     */
    public function __construct(
        Api\ProductExportRepositoryInterface $productExportRepository,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Helper $helper,
        DateTime $dateTime,
        Logger $logger,
        ?Json $serializer = null
    ) {
        $this->_productExportRepository = $productExportRepository;
        $this->_filterBuilder = $filterBuilder;
        $this->_filterGroupBuilder = $filterGroupBuilder;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_helper = $helper;
        $this->_dateTime = $dateTime;
        $this->_logger = $logger;
        $this->_serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * @param null $key
     * @return array|mixed
     */
    public function getErrors($key = null)
    {
        return null === $key
            ? ($this->_error ?: [])
            : ($this->_error[$key] ?? []);
    }

    /**
     * @param int|string|array $data
     * @param int|string|null $key
     * @return $this
     */
    public function setErrors($data, $key = null)
    {
        null !== $key
            ? $this->_error[$key][] = $data
            : $this->_error[] = $data;
        return $this;
    }

    /**
     * @param null $key
     * @return array|null
     */
    public function getResponse($key = null)
    {
        return null === $key
            ? ($this->_response ?: [])
            : ($this->_response[$key] ?? []);
    }

    /**
     * @param int|string|array $data
     * @param int|string|null $key
     * @return $this
     */
    public function setResponse($data, $key = null)
    {
        null !== $key
            ? $this->_response[$key] = $data
            : $this->_response = $data;
        return $this;
    }

    /**
     * @param array|string $data
     * @param null $key
     * @return $this
     */
    public function addResponse($data, $key = null)
    {
        null !== $key
            ? $this->_response[$key][] = $data
            : $this->_response[] = $data;
        return $this;
    }

    /**
     * @param int|string|null $key
     * @return array|string|mixed
     */
    public function getRequest($key = null)
    {
        return null === $key
            ? ($this->_request ?: [])
            : ($this->_request[$key] ?? []);

    }

    /**
     * @param $value
     * @param null $key
     * @return $this
     */
    public function setRequest($value, $key = null)
    {
        null !== $key
            ? $this->_request[$key] = $value
            : $this->_request = $value;
        return $this;
    }

    /**
     * @param array|string $data
     * @param null $key
     * @return $this
     */
    public function addRequest($data, $key = null)
    {
        null !== $key
            ? $this->_request[$key][] = $data
            : $this->_request[] = $data;
        return $this;
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
     * @param string $message
     * @param array $data
     * @param bool $force
     * @return $this
     */
    protected function _log(string $message, array $data = [], $force = false)
    {
        if (!$this->_helper->getIsActiveDebug() && false === $force) {
            return $this;
        }

        if ($this->_helper->getIsDebugPrintToArray()) {
            $this->_logger->debug(print_r([$message => $data], true), __METHOD__);
        } else {
            $this->_logger->debug($message, [__METHOD__ => $data]);
        }

        return $this;
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
