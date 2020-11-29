<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\MintSoft\Observer\Backend;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\Store;
use SoftCommerce\MintSoft\Api\ProductExportRegisterManagementInterface;
use SoftCommerce\MintSoft\Logger\Logger;

/**
 * Class CatalogProductSaveAfter
 * @package SoftCommerce\MintSoft\Observer\Backend
 */
class CatalogProductSaveAfter implements ObserverInterface
{
    const ATTRIBUTE_SET_FASHION_TYPE = 4;

    /**
     * @var ProductExportRegisterManagementInterface
     */
    private $_productExportRegisterManagement;

    /**
     * @var Logger
     */
    private $_logger;

    /**
     * CatalogProductSaveAfter constructor.
     * @param ProductExportRegisterManagementInterface $productExportRegisterManagement
     * @param Logger $logger
     */
    public function __construct(
        ProductExportRegisterManagementInterface $productExportRegisterManagement,
        Logger $logger
    ) {
        $this->_productExportRegisterManagement = $productExportRegisterManagement;
        $this->_logger = $logger;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer): void
    {
        $product = $observer->getEvent()->getProduct();
        if (!$product instanceof Product
            || $product->getStoreId() != Store::DEFAULT_STORE_ID
            || $product->getTypeId() == 'giftvoucher'
            || $product->getAttributeSetId() != self::ATTRIBUTE_SET_FASHION_TYPE
        ) {
            return;
        }

        if ($product->getTypeId() == Configurable::TYPE_CODE) {
            $targetIds = $product->getTypeInstance()->getChildrenIds($product->getId());
            $targetIds = current($targetIds);
        } elseif ($product->getTypeId() == Product\Type::TYPE_SIMPLE) {
            $targetIds[] = $product->getId();
        } else {
            $targetIds = [];
        }

        try {
            $this->_productExportRegisterManagement
                ->setRequest($targetIds)
                ->setBehaviour(ProductExportRegisterManagementInterface::BEHAVIOUR_REPLACE)
                ->execute();
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage(), ['Target IDs' => $targetIds]);
        }

        return;
    }
}
