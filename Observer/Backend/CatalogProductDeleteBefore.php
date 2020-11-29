<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\MintSoft\Observer\Backend;

use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\Store;
use SoftCommerce\MintSoft\Api\ProductExportRepositoryInterface;
use SoftCommerce\MintSoft\Logger\Logger;

/**
 * Class CatalogProductDeleteBefore
 * @package SoftCommerce\MintSoft\Observer\Backend
 */
class CatalogProductDeleteBefore implements ObserverInterface
{
    /**
     * @var Logger
     */
    private $_logger;

    /**
     * @var ProductExportRepositoryInterface
     */
    private $_productExportRepository;

    /**
     * CatalogProductDeleteBefore constructor.
     * @param ProductExportRepositoryInterface $productExportRepository
     * @param Logger $logger
     */
    public function __construct(
        ProductExportRepositoryInterface $productExportRepository,
        Logger $logger
    ) {
        $this->_productExportRepository = $productExportRepository;
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
        ) {
            return;
        }

        try {
            $this->_productExportRepository->deleteById($product->getId());
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage(), ['ID' => $product->getId()]);
        }

        return;
    }
}
