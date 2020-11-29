<?php
/**
 * Copyright © Soft Commerce Ltd, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SoftCommerce\MintSoft\Api\Data;

/**
 * Interface ProductExportInterface
 * @package SoftCommerce\MintSoft\Api\Data
 */
interface ProductExportInterface
{
    const DB_TABLE_NAME = 'softcommerce_mintsoft_product_export';

    const ENTITY_ID = 'entity_id';
    const SKU = 'sku';
    const STATUS = 'status';
    const EXTERNAL_ID = 'external_id';
    const MESSAGE = 'message';
    const REQUEST_ENTRY = 'request_entry';
    const RESPONSE_ENTRY = 'response_entry';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * @return string|null
     */
    public function getSku();

    /**
     * @param string $sku
     * @return $this
     */
    public function setSku(string $sku);

    /**
     * @return string|null
     */
    public function getStatus();

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus(string $status);

    /**
     * @return int|null
     */
    public function getExternalId();

    /**
     * @param int $externalId
     * @return $this
     */
    public function setExternalId(int $externalId);

    /**
     * @return string|null
     */
    public function getMessage();

    /**
     * @param $message
     * @return $this
     */
    public function setMessage($message);

    /**
     * @return string|null
     */
    public function getRequestEntry();

    /**
     * @param $request
     * @return $this
     */
    public function setRequestEntry($request);

    /**
     * @return string|null
     */
    public function getResponseEntry();

    /**
     * @param $response
     * @return $this
     */
    public function setResponseEntry($response);

    /**
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * @param $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * @param $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);
}
