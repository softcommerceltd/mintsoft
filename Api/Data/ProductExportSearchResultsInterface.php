<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\MintSoft\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface ProductExportSearchResultsInterface
 * @package SoftCommerce\MintSoft\Api\Data
 */
interface ProductExportSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return ExtensibleDataInterface[]
     */
    public function getItems();

    /**
     * @param array $items
     * @return SearchResultsInterface
     */
    public function setItems(array $items);

    /**
     * @return ExtensibleDataInterface[]
     */
    public function getAllIds();

    /**
     * @param array $ids
     * @return SearchResultsInterface;
     */
    public function setAllIds(array $ids);
}
