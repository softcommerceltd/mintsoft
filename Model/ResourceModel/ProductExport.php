<?php
/**
 * Copyright © Soft Commerce Ltd, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SoftCommerce\MintSoft\Model\ResourceModel;

use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use SoftCommerce\Core\Model\Source\Status;
use SoftCommerce\MintSoft\Api\Data\ProductExportInterface;

/**
 * Class ProductExport
 * @package SoftCommerce\MintSoft\Model\ResourceModel
 */
class ProductExport extends AbstractDb
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init(ProductExportInterface::DB_TABLE_NAME, ProductExportInterface::ENTITY_ID);
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getLastUpdatedAt()
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getMainTable(), [ProductExportInterface::UPDATED_AT])
            ->order(ProductExportInterface::UPDATED_AT . ' ' . Select::SQL_DESC);

        return $adapter->fetchOne($select);
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getPendingRecords()
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getMainTable(), [ProductExportInterface::ENTITY_ID, ProductExportInterface::EXTERNAL_ID])
            ->where(ProductExportInterface::STATUS . ' = ?', Status::PENDING);

        return $adapter->fetchPairs($select);
    }

    /**
     * @param array $data
     * @param array $fields
     * @return int
     * @throws LocalizedException
     */
    public function insertOnDuplicate(array $data, array $fields = [])
    {
        return $this->getConnection()
            ->insertOnDuplicate($this->getMainTable(), $data, $fields);
    }
}
