<?php
/**
 * Copyright © Soft Commerce Ltd, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SoftCommerce\MintSoft\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use SoftCommerce\MintSoft\Api;
use SoftCommerce\MintSoft\Model\Source\Status;

/**
 * Class ProductExportRegisterManagement
 * @package SoftCommerce\MintSoft\Model
 */
class ProductExportRegisterManagement extends ProductExportAbstractManagement
    implements Api\ProductExportRegisterManagementInterface
{
    /**
     * @var string|null
     */
    private $_behaviour;

    /**
     * @return string
     */
    public function getBehaviour()
    {
        return $this->_behaviour ?: self::BEHAVIOUR_APPEND;
    }

    /**
     * @param string $behaviour
     * @return $this
     */
    public function setBehaviour($behaviour)
    {
        $this->_behaviour = $behaviour;
        return $this;
    }

    /**
     * @return $this|ProductExportRegisterManagement
     * @throws CouldNotSaveException
     */
    public function execute()
    {
        $request = $this->getRequest();
        if ($this->getBehaviour() === self::BEHAVIOUR_APPEND) {
            $existingIds = $this->_productExportRepository->getAllIds();
            $request = array_diff($request, $existingIds);
        }

        if (empty($request)) {
            $this->_log('Products are up-to-date');
            return $this;
        }

        $data = [];
        foreach ($request as $id) {
            $data[] = [
                Api\Data\ProductExportInterface::ENTITY_ID => $id,
                Api\Data\ProductExportInterface::STATUS => Status::PENDING,
                Api\Data\ProductExportInterface::MESSAGE => 'Registered. Waiting for export.',
                Api\Data\ProductExportInterface::CREATED_AT => $this->_dateTime->gmtDate(),
                Api\Data\ProductExportInterface::UPDATED_AT => $this->_dateTime->gmtDate()
            ];
        }

        if (empty($data)) {
            return $this;
        }

        $this->_productExportRepository->saveMultiple($data);
        $this->_log(sprintf('A total of %1 records have been added to export.', count($request)));

        return $this;
    }
}
