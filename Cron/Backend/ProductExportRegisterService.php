<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\MintSoft\Cron\Backend;

use Magento\Catalog\Model\ResourceModel;
use SoftCommerce\Catalog\Module\ProductMetadataInterface;
use SoftCommerce\MintSoft\Api\ProductExportRegisterManagementInterface;
use SoftCommerce\MintSoft\Logger\Logger;

/**
 * Class CatalogProductSaveAfter
 * @package SoftCommerce\MintSoft\Observer\Backend
 */
class ProductExportRegisterService
{
    private $_productResource;

    /**
     * @var ProductExportRegisterManagementInterface
     */
    private $_productExportRegisterManagement;

    /**
     * @var Logger
     */
    private $_logger;

    /**
     * ProductExportRegisterService constructor.
     * @param ResourceModel\Product $productResource
     * @param ProductExportRegisterManagementInterface $productExportRegisterManagement
     * @param Logger $logger
     */
    public function __construct(
        ResourceModel\Product $productResource,
        ProductExportRegisterManagementInterface $productExportRegisterManagement,
        Logger $logger
    ) {
        $this->_productResource = $productResource;
        $this->_productExportRegisterManagement = $productExportRegisterManagement;
        $this->_logger = $logger;
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        if (!$request = $this->_getRequest()) {
            return;
        }

        try {
            $this->_productExportRegisterManagement
                ->setRequest($request)
                ->execute();
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage(), ['Target IDs' => $request]);
            return;
        }

        return;
    }

    /**
     * @return array
     */
    private function _getRequest()
    {
        $adapter = $this->_productResource->getConnection();
        $select = $adapter->select()
            ->from($adapter->getTableName('catalog_product_entity'), ['entity_id'])
            ->where('type_id = ?', 'simple')
            ->where('attribute_set_id = ?', ProductMetadataInterface::PRODUCT_ATTRIBUTE_SET_FASHION);
        return $adapter->fetchCol($select);
    }
}
