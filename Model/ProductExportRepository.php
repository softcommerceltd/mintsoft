<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\MintSoft\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;

use SoftCommerce\MintSoft\Api;
use SoftCommerce\MintSoft\Model\ResourceModel;

/**
 * Class ProductExportRepository
 * @package SoftCommerce\MintSoft\Model
 */
class ProductExportRepository implements Api\ProductExportRepositoryInterface
{
    /**
     * @var ResourceModel\ProductExport
     */
    private $_resource;

    /**
     * @var ProductExportFactory
     */
    private $_entityFactory;

    /**
     * @var ResourceModel\ProductExport\CollectionFactory
     */
    private $_collectionFactory;

    /**
     * @var Api\Data\ProductExportSearchResultsInterface
     */
    private $_searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $_collectionProcessor;

    /**
     * ProductExportRepository constructor.
     * @param ResourceModel\ProductExport $resource
     * @param ProductExportFactory $entityFactory
     * @param ResourceModel\ProductExport\CollectionFactory $collectionFactory
     * @param Api\Data\ProductExportSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface|null $collectionProcessor
     */
    public function __construct(
        ResourceModel\ProductExport $resource,
        ProductExportFactory $entityFactory,
        ResourceModel\ProductExport\CollectionFactory $collectionFactory,
        Api\Data\ProductExportSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->_resource = $resource;
        $this->_entityFactory = $entityFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_searchResultsFactory = $searchResultsFactory;
        $this->_collectionProcessor = $collectionProcessor
            ?: ObjectManager::getInstance()->get(CollectionProcessorInterface::class);
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return Api\Data\ProductExportSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var ResourceModel\ProductExport\Collection $collection */
        $collection = $this->_collectionFactory->create();
        $this->_collectionProcessor->process($searchCriteria, $collection);

        /** @var Api\Data\ProductExportSearchResultsInterface $searchResults */
        $searchResult = $this->_searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getPendingRecords()
    {
        return $this->_resource->getPendingRecords();
    }

    /**
     * @return array
     */
    public function getAllIds()
    {
        /** @var ResourceModel\ProductExport\Collection $collection */
        $collection = $this->_collectionFactory->create();
        return $collection->getAllIds();
    }

    /**
     * @param $entityId
     * @param null $field
     * @return Api\Data\ProductExportInterface|ProductExport
     * @throws NoSuchEntityException
     */
    public function get($entityId, $field = null)
    {
        /** @var Api\Data\ProductExportInterface|ProductExport $entity */
        $entity = $this->_entityFactory->create();
        $this->_resource->load($entity, $entityId, $field);
        if (!$entity->getId()) {
            throw new NoSuchEntityException(__('The entity with ID "%1" doesn\'t exist.', $entityId));
        }

        return $entity;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getLastUpdatedAt()
    {
        return $this->_resource->getLastUpdatedAt();
    }

    /**
     * @param Api\Data\ProductExportInterface $entity
     * @return Api\Data\ProductExportInterface
     * @throws CouldNotSaveException
     */
    public function save(Api\Data\ProductExportInterface $entity)
    {
        try {
            $this->_resource->save($entity);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $entity;
    }

    /**
     * @param array $entries
     * @return int
     * @throws CouldNotSaveException
     */
    public function saveMultiple(array $entries)
    {
        try {
            $result = $this->_resource->insertOnDuplicate($entries);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $result;
    }

    /**
     * @param Api\Data\ProductExportInterface $entity
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Api\Data\ProductExportInterface $entity)
    {
        try {
            $this->_resource->delete($entity);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @param int $entityId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($entityId)
    {
        return $this->delete($this->get($entityId));
    }
}
