<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\MintSoft\Http;

use GuzzleHttp;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Webapi\Rest\Request;
use Psr\Http\Message\StreamInterface;
use SoftCommerce\MintSoft\Helper;
use SoftCommerce\MintSoft\Logger\Logger;

/**
 * Class Client
 * @package SoftCommerce\MintSoft\Http
 */
class Client implements
    ClientInterface, MintSoftServerInterface
{
    /**
     * @var string
     */
    private $_accessToken;

    /**
     * @var WriterInterface
     */
    private $_configWriter;

    /**
     * @var ClientFactory
     */
    private $clientFactory;

    /**
     * @var CacheInterface
     */
    private $_cacheInterface;

    /**
     * @var null
     */
    private $_helper;

    /**
     * @var Logger
     */
    private $_logger;

    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @var Response
     */
    private $_response;

    /**
     * @var string|int|null
     */
    private $_responseStatusCode;

    /**
     * @var StreamInterface
     */
    private $_responseBody;

    /**
     * @var string|null
     */
    private $_responseContents;

    /**
     * @var array
     */
    private $_request;

    /**
     * @var string
     */
    private $_requestUri;

    /**
     * @var int
     */
    private $_requestNo;

    /**
     * @var ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * @var Json
     */
    private $_serializer;

    /**
     * Client constructor.
     * @param ClientFactory $clientFactory
     * @param ResponseFactory $responseFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param WriterInterface $configWriter
     * @param CacheInterface $cache
     * @param Helper\Data $helper
     * @param Logger $logger
     * @param Json|null $serializer
     */
    public function __construct(
        GuzzleHttp\ClientFactory $clientFactory,
        ResponseFactory $responseFactory,
        ScopeConfigInterface $scopeConfig,
        WriterInterface $configWriter,
        CacheInterface $cache,
        Helper\Data $helper,
        Logger $logger,
        ?Json $serializer = null
    ) {
        $this->clientFactory = $clientFactory;
        $this->responseFactory = $responseFactory;
        $this->_configWriter = $configWriter;
        $this->_cacheInterface = $cache;
        $this->_scopeConfig = $scopeConfig;
        $this->_helper = $helper;
        $this->_logger = $logger;
        $this->_serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        $this->_requestNo = 0;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->_response ?: $this->responseFactory->create();
    }

    /**
     * @param Response $response
     * @return $this
     */
    public function setResponse(Response $response)
    {
        $this->_response = $response;
        return $this;
    }

    /**
     * @return string|int|null
     */
    public function getResponseStatusCode()
    {
        return $this->_responseStatusCode;
    }

    /**
     * @param string|int $statusCode
     * @return $this
     */
    public function setResponseStatusCode($statusCode)
    {
        $this->_responseStatusCode = $statusCode;
        return $this;
    }

    /**
     * @return StreamInterface
     */
    public function getResponseBody()
    {
        return $this->_responseBody;
    }

    /**
     * @param StreamInterface $body
     * @return $this
     */
    public function setResponseBody(StreamInterface $body)
    {
        $this->_responseBody = $body;
        return $this;
    }

    /**
     * @param bool $decoded
     * @return array|bool|float|int|mixed|string|null
     */
    public function getResponseContents($decoded = false)
    {
        return false === $decoded
            ? $this->_responseContents
            : $this->_serializer->unserialize($this->_responseContents);
    }

    /**
     * @param string $contents
     * @return $this
     */
    public function setResponseContents(string $contents)
    {
        $this->_responseContents = $contents;
        return $this;
    }

    /**
     * @return array|string|mixed
     */
    public function getRequest()
    {
        return $this->_request ?: [];
    }

    /**
     * @param string|array|mixed $data
     * @return $this
     */
    public function setRequest($data)
    {
        $this->_request = $data;
        return $this;
    }

    /**
     * @return string
     */
    public function getRequestUri()
    {
        return $this->_requestUri;
    }

    /**
     * @param string $uri
     * @return $this
     */
    public function setRequestUri(string $uri)
    {
        $this->_requestUri = $uri;
        return $this;
    }

    /**
     * @param string $uri
     * @param array $params
     * @param string $method
     * @return $this
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(
        string $uri,
        array $params = [],
        string $method = Request::HTTP_METHOD_GET
    ) {
        $this->executeBefore()
            ->setRequestUri($uri);

        if ($method !== self::OAUTH) {
            $this->setRequestUri(
                $uri . '?' . http_build_query(['APIKey' => $this->_getAccessToken()])
            );
        }

        /** @var GuzzleHttp\Client $client */
        $client = $this->clientFactory->create();

        if (!empty($params)) {
            $this->setRequest(
                [
                    GuzzleHttp\RequestOptions::DECODE_CONTENT => true,
                    GuzzleHttp\RequestOptions::JSON => $params
                ]
            );
        }

        try {
            $response = $client->request($method, $this->getRequestUri(), $this->getRequest());
        } catch (GuzzleException $e) {
            $this->_log($e->getMessage());
            $response = $this->responseFactory->create([
                'status' => $e->getCode(),
                'reason' => $e->getMessage()
            ]);
        }

        if ($response->getStatusCode() === self::HTTP_CODE_UNAUTHORIZED) {
            if (!$this->_requestAccessToken() || $this->_requestNo > 1) {
                throw new LocalizedException(__('Unauthorised.'));
            }
            $this->execute($uri, $params, $method);
        }

        if (!$body = $response->getBody()) {
            throw new LocalizedException(__('Could not retrieve response body.'));
        }

        if (!$contents = $body->getContents()) {
            throw new LocalizedException(__('Could not retrieve response contents.'));
        }

        $this->setResponse($response)
            ->setResponseStatusCode($response->getStatusCode())
            ->setResponseBody($body)
            ->setResponseContents($contents);

        return $this;
    }

    /**
     * @return $this
     */
    public function executeBefore()
    {
        $this->_response =
        $this->_responseBody =
        $this->_responseContents =
        $this->_responseStatusCode =
        $this->_requestUri =
            null;

        $this->_requestNo += 1;
        $this->_request = [];

        return $this;
    }

    /**
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function _getAccessToken()
    {
        if (null === $this->_accessToken) {
            $this->_accessToken = $this->_scopeConfig->getValue(Helper\Data::XML_PATH_API_ACCESS_TOKEN);
        }

        if (!$this->_accessToken) {
            $this->_accessToken = $this->_requestAccessToken();
        }

        return (string) $this->_accessToken;
    }

    /**
     * @param string $accessToken
     * @return $this
     */
    private function _setAccessToken(string $accessToken)
    {
        $this->_configWriter->save(Helper\Data::XML_PATH_API_ACCESS_TOKEN, $accessToken);
        $this->_cacheInterface->clean(['config']);
        return $this;
    }

    /**
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function _requestAccessToken()
    {
        $uri = $this->_helper->getApiAuthUrl($this->_getAuthRequest());
        $client = $this->clientFactory->create();

        try {
            $response = $client->request(self::GET, $uri);
        } catch (GuzzleException $e) {
            /** @var Response $response */
            $response = $this->responseFactory->create([
                'status' => $e->getCode(),
                'reason' => $e->getMessage()
            ]);
        }

        $status = $response->getStatusCode();

        if ($status === self::HTTP_CODE_UNAUTHORIZED) {
            throw new LocalizedException(__('Unauthorised. Provided username or password is incorrect.'));
        }

        if (!$body = $response->getBody()) {
            throw new LocalizedException(__('Could not retrieve response body.'));
        }

        if (!$contents = $body->getContents()) {
            throw new LocalizedException(__('Could not retrieve response contents.'));
        }

        $accessToken = (string) $this->_serializer->unserialize($contents);
        $this->_setAccessToken($accessToken);

        return $accessToken;
    }

    /**
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function _getAuthRequest()
    {
        if (!$userName = $this->_helper->getApiUsername()) {
            throw new LocalizedException(__('Api username is not set.'));
        }

        if (!$password = $this->_helper->getApiPassword()) {
            throw new LocalizedException(__('Api password is not set.'));
        }

        $authData = [
            'UserName' => $userName,
            'Password' => $password
        ];

        return http_build_query($authData);
    }

    /**
     * @param $message
     * @param array $context
     * @param bool $force
     * @return $this
     */
    private function _log($message, array $context = [], $force = false)
    {
        if (false === $force || !$this->_helper->getIsActiveDebug()) {
            return $this;
        }

        if ($this->_helper->getIsDebugPrintToArray()) {
            $this->_logger->debug(print_r([$message => $context], true), []);
        } else {
            $this->_logger->debug($message, $context);
        }

        return $this;
    }
}
