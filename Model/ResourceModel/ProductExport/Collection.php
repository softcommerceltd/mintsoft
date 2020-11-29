<?php
/**
 * Copyright © Soft Commerce Ltd, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SoftCommerce\MintSoft\Model\ResourceModel\ProductExport;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use SoftCommerce\MintSoft\Api\Data\ProductExportInterface;
use SoftCommerce\MintSoft\Model\ProductExport;
use SoftCommerce\MintSoft\Model\ResourceModel;

/**
 * Class Collection
 * @package SoftCommerce\MintSoft\Model\ResourceModel\ProductExport
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = ProductExportInterface::ENTITY_ID;

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init(ProductExport::class, ResourceModel\ProductExport::class);
    }
}
