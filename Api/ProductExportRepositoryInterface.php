<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\MintSoft\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use SoftCommerce\MintSoft\Model\ProductExport;

/**
 * Interface ProductExportRepositoryInterface
 * @package SoftCommerce\MintSoft\Api
 */
interface ProductExportRepositoryInterface
{
    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return Data\ProductExportSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @return array
     */
    public function getPendingRecords();

    /**
     * @return array
     */
    public function getAllIds();

    /**
     * @param $entityId
     * @param null $field
     * @return Data\ProductExportInterface|ProductExport
     * @throws NoSuchEntityException
     */
    public function get($entityId, $field = null);

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getLastUpdatedAt();

    /**
     * @param Data\ProductExportInterface $entity
     * @return Data\ProductExportInterface
     * @throws CouldNotSaveException
     */
    public function save(Data\ProductExportInterface $entity);

    /**
     * @param array $entries
     * @return int
     * @throws CouldNotSaveException
     */
    public function saveMultiple(array $entries);

    /**
     * @param Data\ProductExportInterface $entity
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\ProductExportInterface $entity);

    /**
     * @param int $entityId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($entityId);
}
