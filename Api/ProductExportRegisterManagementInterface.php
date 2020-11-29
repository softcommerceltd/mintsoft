<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\MintSoft\Api;

/**
 * Interface ProductExportRegisterManagementInterface
 * @package SoftCommerce\MintSoft\Api
 */
interface ProductExportRegisterManagementInterface
{
    const BEHAVIOUR_APPEND = 'append';
    const BEHAVIOUR_REPLACE = 'replace';

    /**
     * @return string
     */
    public function getBehaviour();

    /**
     * @param string $behaviour
     * @return $this
     */
    public function setBehaviour($behaviour);

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
     * @return $this
     */
    public function execute();
}
