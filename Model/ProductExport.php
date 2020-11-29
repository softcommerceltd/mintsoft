<?php
/**
 * Copyright © Soft Commerce Ltd, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SoftCommerce\MintSoft\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use SoftCommerce\MintSoft\Api\Data\ProductExportInterface;

/**
 * Class ProductExport
 * @package SoftCommerce\MintSoft\Model
 */
class ProductExport extends AbstractModel implements
    ProductExportInterface,
    IdentityInterface
{
    const CACHE_TAG = 'softcommerce_mintsoft_productexport';
    protected $_cacheTag = 'softcommerce_mintsoft_productexport';
    protected $_eventPrefix = 'softcommerce_mintsoft_productexport';

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\ProductExport::class);
    }

    /**
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return string|null
     */
    public function getSku()
    {
        return $this->getData(self::SKU);
    }

    /**
     * @param string $sku
     * @return $this
     */
    public function setSku(string $sku)
    {
        $this->setData(self::SKU, $sku);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus(string $status)
    {
        $this->setData(self::STATUS, $status);
        return $this;
    }

    /**
     * @return int|null
     */
    public function getExternalId()
    {
        return $this->getData(self::EXTERNAL_ID);
    }

    /**
     * @param int $externalId
     * @return $this
     */
    public function setExternalId(int $externalId)
    {
        $this->setData(self::EXTERNAL_ID, $externalId);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMessage()
    {
        return $this->getData(self::MESSAGE);
    }

    /**
     * @param $message
     * @return $this
     */
    public function setMessage($message)
    {
        $this->setData(self::MESSAGE, $message);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRequestEntry()
    {
        return $this->getData(self::REQUEST_ENTRY);
    }

    /**
     * @param $request
     * @return $this
     */
    public function setRequestEntry($request)
    {
        $this->setData(self::REQUEST_ENTRY, $request);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getResponseEntry()
    {
        return $this->getData(self::RESPONSE_ENTRY);
    }

    /**
     * @param $response
     * @return $this
     */
    public function setResponseEntry($response)
    {
        $this->setData(self::RESPONSE_ENTRY, $response);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @param $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->setData(self::CREATED_AT, $createdAt);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @param $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->setData(self::UPDATED_AT, $updatedAt);
        return $this;
    }
}
