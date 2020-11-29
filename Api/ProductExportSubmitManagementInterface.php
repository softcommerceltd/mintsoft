<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\MintSoft\Api;

use Magento\Framework\Exception\LocalizedException;
use SoftCommerce\MintSoft\Model\ProductExportSubmitManagement;

/**
 * Interface ProductExportSubmitManagementInterface
 * @package SoftCommerce\MintSoft\Api
 */
interface ProductExportSubmitManagementInterface
{
    /**
     * @param null $key
     * @return array|mixed
     */
    public function getErrors($key = null);

    /**
     * @param int|string|array $data
     * @param int|string|null $key
     * @return $this
     */
    public function setErrors($data, $key = null);

    /**
     * @param null $key
     * @return array|mixed
     */
    public function getResponse($key = null);

    /**
     * @param string|array $data
     * @param null|string $key
     * @return $this
     */
    public function setResponse($data, $key = null);

    /**
     * @param array|string $data
     * @param null $key
     * @return $this
     */
    public function addResponse($data, $key = null);

    /**
     * @param int|string|null $key
     * @return array|string|mixed
     */
    public function getRequest($key = null);

    /**
     * @param $value
     * @param null $key
     * @return $this
     */
    public function setRequest($value, $key = null);

    /**
     * @param array|string $data
     * @param null $key
     * @return $this
     */
    public function addRequest($data, $key = null);

    /**
     * @return string|null
     * @throws LocalizedException
     * @throws \Exception
     */
    public function getLastCollectedAt();

    /**
     * @param bool $keys
     * @return array
     */
    public function getSearchCriteria($keys = false);

    /**
     * @param array $productEntityId
     * @return $this
     */
    public function setSearchCriteria(array $productEntityId);

    /**
     * @return $this
     */
    public function execute();
}
