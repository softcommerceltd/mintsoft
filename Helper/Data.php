<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\MintSoft\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class Data
 * @package SoftCommerce\MintSoft\Helper
 */
class Data extends AbstractHelper
{
    const XML_PATH_PRODUCT_IS_ACTIVE            = 'softcommerce_mintsoft/product_export/is_active';
    const XML_PATH_API_NAME                     = 'softcommerce_mintsoft/product_export/api_name';
    const XML_PATH_API_URL                      = 'softcommerce_mintsoft/product_export/api_url';
    const XML_PATH_API_USERNAME                 = 'softcommerce_mintsoft/product_export/api_username';
    const XML_PATH_API_PASSWORD                 = 'softcommerce_mintsoft/product_export/api_password';
    const XML_PATH_API_ACCESS_TOKEN             = 'softcommerce_mintsoft/product_export/access_token';
    const XML_PATH_API_RETRY                    = 'softcommerce_mintsoft/product_export/api_retry';
    const XML_PATH_API_CONNECTION_TIMEOUT       = 'softcommerce_mintsoft/product_export/api_connection_timeout';
    const XML_PATH_API_TIMEOUT                  = 'softcommerce_mintsoft/product_export/api_timeout';
    const XML_PATH_DEV_IS_ACTIVE_DEBUG          = 'softcommerce_mintsoft/dev/is_active_debug';
    const XML_PATH_DEV_DEBUG_PRINT_TO_ARRAY     = 'softcommerce_mintsoft/dev/debug_print_to_array';

    /**
     * @var StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var EncryptorInterface
     */
    private $_encryptor;

    /**
     * @var DateTime
     */
    private $_dateTime;

    /**
     * @var TimezoneInterface
     */
    private $_timezone;

    /**
     * Data constructor.
     * @param Context $context
     * @param EncryptorInterface $encryptor
     * @param StoreManagerInterface $storeManager
     * @param DateTime $date
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        Context $context,
        EncryptorInterface $encryptor,
        StoreManagerInterface $storeManager,
        DateTime $date,
        TimezoneInterface $timezone
    ) {
        $this->_storeManager = $storeManager;
        $this->_encryptor = $encryptor;
        $this->_dateTime = $date;
        $this->_timezone = $timezone;
        parent::__construct($context);
    }

    /**
     * @return int
     * @throws NoSuchEntityException
     */
    protected function _getStore()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * @param $path
     * @param null $store
     * @return mixed
     * @throws NoSuchEntityException
     */
    protected function _getConfig($path, $store = null)
    {
        if (null === $store) {
            $store = $this->_getStore();
        }

        return $this->scopeConfig
            ->getValue($path, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function getIsActiveProductExport()
    {
        return (bool) $this->_getConfig(self::XML_PATH_PRODUCT_IS_ACTIVE);
    }

    /**
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getApiName()
    {
        return $this->_getConfig(self::XML_PATH_API_NAME);
    }

    /**
     * @param $route
     * @return string
     * @throws NoSuchEntityException
     */
    public function getApiUrl($route)
    {
        return $this->_getConfig(self::XML_PATH_API_URL).$route;
    }

    /**
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getApiUsername()
    {
        return $this->_getConfig(self::XML_PATH_API_USERNAME);
    }

    /**
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getApiPassword()
    {
        $pass = $this->_getConfig(self::XML_PATH_API_PASSWORD);
        return $this->_encryptor->decrypt($pass);
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getApiRetries()
    {
        return $this->_getConfig(self::XML_PATH_API_RETRY);
    }

    /**
     * @return int|null
     * @throws NoSuchEntityException
     */
    public function getApiConnectionTimeout()
    {
        return $this->_getConfig(self::XML_PATH_API_CONNECTION_TIMEOUT);
    }

    /**
     * @return int|null
     * @throws NoSuchEntityException
     */
    public function getApiTimeout()
    {
        return $this->_getConfig(self::XML_PATH_API_TIMEOUT);
    }

    /**
     * @param null $params
     * @return string
     * @throws NoSuchEntityException
     */
    public function getApiAuthUrl($params = null)
    {
        return null === $params
            ? $this->getApiUrl('/api/Auth')
            : $this->getApiUrl("/api/Auth?{$params}");
    }

    /**
     * @param null $productId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getApiProductUrl($productId = null)
    {
        if (null === $productId) {
            return $this->getApiUrl('/api/Product');
        }

        return $this->getApiUrl('/api/Product/'.$productId);
    }

    /**
     * @param string $scope
     * @return bool
     */
    public function isEnabled($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_PRODUCT_IS_ACTIVE, $scope);
    }

    /**
     * @return mixed
     */
    public function getIsActiveDebug()
    {
        return (bool) $this->scopeConfig->isSetFlag(self::XML_PATH_DEV_IS_ACTIVE_DEBUG);
    }

    /**
     * @return bool
     */
    public function getIsDebugPrintToArray()
    {
        return (bool) $this->scopeConfig->isSetFlag(self::XML_PATH_DEV_DEBUG_PRINT_TO_ARRAY);
    }

    /**
     * @param $input
     * @return string|null
     * @throws \Exception
     */
    public function getDateTimeLocale($input)
    {
        if (!$input) {
            return null;
        } elseif (is_numeric($input)) {
            $result = $this->_dateTime->gmtDate(null, $input);
        } else {
            $result = $input;
        }

        $dateTime = (new \DateTime($result))
            ->setTimezone(new \DateTimeZone($this->scopeConfig->getValue('general/locale/timezone')));
        $result = $dateTime->format(\DateTime::W3C);

        return $result;
    }
}
