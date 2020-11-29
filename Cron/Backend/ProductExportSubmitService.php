<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\MintSoft\Cron\Backend;

use SoftCommerce\MintSoft\Api\ProductExportSubmitManagementInterface;
use SoftCommerce\MintSoft\Logger\Logger;

/**
 * Class ProductExportSubmitService
 * @package SoftCommerce\MintSoft\Cron\Backend
 */
class ProductExportSubmitService
{
    /**
     * @var ProductExportSubmitManagementInterface
     */
    private $_productExportSubmitManagement;

    /**
     * @var Logger
     */
    private $_logger;

    /**
     * ProductExportSubmitService constructor.
     * @param ProductExportSubmitManagementInterface $productExportSubmitManagement
     * @param Logger $logger
     */
    public function __construct(
        ProductExportSubmitManagementInterface $productExportSubmitManagement,
        Logger $logger
    ) {
        $this->_productExportSubmitManagement = $productExportSubmitManagement;
        $this->_logger = $logger;
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        try {
            $this->_productExportSubmitManagement->execute();
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
            return;
        }

        return;
    }
}
