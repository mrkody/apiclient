<?php
namespace ShopExpress\ApiClient;

use ShopExpress\ApiClient\Response\ApiResponse;
use ShopExpress\ApiClient\Exception\CurlException;
use ShopExpress\ApiClient\Exception\ApiException;

class ApiClient
{
    const VERSION = '0.2 beta';
    
    const ERROR_API_CALLING = 'You have to specify a method (eg. POST, PUT, ...) and a correct object url to call the API';
    const ERROR_CURL_ERROR = 'HTTP error while calling the API. Error code and message: %s - %s';
    const ERROR_API_MESSAGE = 'Message from API: %s';
    const ERROR_ARGUMENT_VALUE = 'Parameter `%s` can\'t be empty';

    public static $CURL_OPTS = [
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_USERAGENT => 'dicms-api-php-beta-0.2',
    ];
    protected $apiKey;
    protected $userLogin;
    protected $apiUrl;

    /**
     * Client constructor.
     *
     * @param string $apiKey The api key
     * @param string $userLogin The user login
     * @param string $apiUrl The api url
     */
    public function __construct($apiKey, $userLogin, $apiUrl)
    {
        $this->setApiKey($apiKey);
        $this->setUserLogin($userLogin);
        $this->setApiUrl($apiUrl);
    }

    /**
     * Sets the api key.
     *
     * @param string $apiKey The api key
     *
     * @throws \InvalidArgumentException 
     *
     * @return self 
     */
    public function setApiKey($apiKey)
    {
        if (empty($apiKey)) {
            throw new \InvalidArgumentException(
                sprintf(self::ERROR_ARGUMENT_VALUE, 'apiKey')
            );
        } 
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * Sets the user login.
     *
     * @param string $userLogin The user login
     *
     * @throws \InvalidArgumentException 
     *
     * @return self 
     */
    public function setUserLogin($userLogin)
    {
        if (empty($userLogin)) {
            throw new \InvalidArgumentException(
                sprintf(self::ERROR_ARGUMENT_VALUE, 'userLogin')
            );
        } 
        $this->userLogin = $userLogin;

        return $this;
    }

    /**
     * Sets the api url.
     *
     * @param string $apiUrl The api url
     *
     * @throws \InvalidArgumentException 
     *
     * @return self 
     */
    public function setApiUrl($apiUrl)
    {
        if (empty($apiUrl)) {
            throw new \InvalidArgumentException(
                sprintf(self::ERROR_ARGUMENT_VALUE, 'apiUrl')
            );
        } 
        if (!filter_var($apiUrl, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED | FILTER_FLAG_HOST_REQUIRED)) {
            throw new \InvalidArgumentException('Parameter `apiUrl` must be valid URL');
        }
        if ('/' !== $apiUrl[strlen($apiUrl) - 1]) {
            $apiUrl .= '/';
        }
        $this->apiUrl = $apiUrl;

        return $this;
    }

    /**
     * Gets the api key.
     *
     * @return string The api key.
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Gets the user login.
     *
     * @return string The user login.
     */
    public function getUserLogin()
    {
        return $this->userLogin;
    }

    /**
     * Gets the api url.
     *
     * @return string The api url.
     */
    public function getApiUrl()
    {
        return $this->apiUrl;
    }

    /**
     * Makes a request.
     *
     * @param string $objectUrl The object url
     * @param string $method The method
     * @param string $data The data
     * @param array $params The parameters
     *
     * @throws \ShopExpress\ApiClient\Exception\CurlException 
     *
     * @return \ShopExpress\ApiClient\Response\ApiResponse
     */
    protected function makeRequest($objectUrl, $method, $data = '', $params = [])
    {
        $ch = curl_init();

        $opts = self::$CURL_OPTS;
        $opts[CURLOPT_URL] = $this->initUrl($objectUrl, $params);
        $opts[CURLOPT_USERPWD] = $this->getAuthString();
        $this->setHeader($opts, 'Content-Type: application/json');

        switch ($method) {
            case 'GET':
                break;
            case 'POST':
                $opts[CURLOPT_CUSTOMREQUEST] = 'POST';
                $opts[CURLOPT_RETURNTRANSFER] = true;
                $postdata = $this->generatePostData($data);
                $opts[CURLOPT_POSTFIELDS] = $postdata;
                $this->setHeader($opts, 'Content-Length: ' . strlen($postdata));
                break;
            case 'PUT':
                $opts[CURLOPT_CUSTOMREQUEST] = 'PUT';
                $opts[CURLOPT_RETURNTRANSFER] = true;
                $postdata = $this->generatePostData($data);
                $opts[CURLOPT_POSTFIELDS] = $postdata;
                $this->setHeader($opts, 'Content-Length: ' . strlen($postdata));
                break;
            case 'DELETE':
                $opts[CURLOPT_CUSTOMREQUEST] = 'DELETE';
                break;
        }

        curl_setopt_array($ch, $opts);
        $resultBody = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errno = curl_errno($ch);
        $error = curl_error($ch);

        curl_close($ch);

        if ($resultBody === false) {
            throw new CurlException(
                sprintf(self::ERROR_CURL_ERROR, $errno, $error)
            );
        }

        return $this->parseResult($resultBody, $statusCode);
    }
    
    /**
     * @param string $objectUrl The object url
     * @param array $params The parameters
     *
     * @return string The init url
     */
    protected function initUrl($objectUrl, array $params = [])
    {
        $params = !empty($params)? '?' . http_build_query($params) : '';
        return $this->apiUrl . $objectUrl . $params;
    }

    /**
     * Gets the auth string.
     *
     * @return string The auth string.
     */
    protected function getAuthString()
    {
        return $this->userLogin . ":" . $this->apiKey;
    }

    /**
     * Sets the http-request header.
     *
     * @param &array $opts The options
     * @param string $headerString The header string
     */
    protected function setHeader(&$opts, $headerString)
    {
        $opts[CURLOPT_HTTPHEADER][] = $headerString;
    }

    /**
     * Convert post array to json.
     *
     * @param array $data The data
     *
     * @return string Json-string
     */
    protected function generatePostData($data)
    {
        return json_encode($data);
    }
    
    /**
     * Parse result.
     *
     * @param string $resultBody The result body
     * @param int $statusCode The status code
     *
     * @throws \ShopExpress\ApiClient\Exception\ApiException
     *
     * @return ApiResponse
     */
    protected function parseResult($resultBody, $statusCode)
    {
        $response = new ApiResponse($statusCode, $resultBody);

        if (!empty($response['message'])) {
            throw new ApiException(
                sprintf(self::ERROR_API_MESSAGE, $response['message'])
            );
        } else {
            return $response;
        }
    }

    /**
     * Universal api request method.
     *
     * @param string $method The method
     * @param string $objectUrl The object url
     * @param string $data The data
     * @param array $params The parameters
     *
     * @throws \InvalidArgumentException 
     *
     * @return \ShopExpress\ApiClient\Response\ApiResponse
     */
    public function api($method, $objectUrl, $data = '', $params = [])
    {
        if (!empty($method) && !empty($objectUrl)) {
            return $this->makeRequest($objectUrl, $method, $data, $params);
        } else {
            throw new \InvalidArgumentException(self::ERROR_API_CALLING);
        }
    }
    
    /**
     * Get api method.
     *
     * @param string $objectUrl The object url
     * @param array $params The parameters
     *
     * @return \ShopExpress\ApiClient\Response\ApiResponse
     */
    public function get($objectUrl, $params = [])
    {
        return $this->api('GET', $objectUrl, '', $params);
    }

    /**
     * Update api method.
     *
     * @param string $objectUrl The object url
     * @param array $data The data
     *
     * @return \ShopExpress\ApiClient\Response\ApiResponse
     */
    public function update($objectUrl, $data)
    {
        return $this->api('PUT', $objectUrl, $data);
    }

    /**
     * Create api method.
     *
     * @param string $objectUrl The object url
     * @param array $data The data
     *
     * @return \ShopExpress\ApiClient\Response\ApiResponse
     */
    public function create($objectUrl, $data)
    {
        return $this->api('POST', $objectUrl, $data);
    }
    
    /**
     * Delete api method.
     *
     * @param string $objectUrl The object url
     *
     * @return \ShopExpress\ApiClient\Response\ApiResponse
     */
    public function delete($objectUrl)
    {
        return $this->api('DELETE', $objectUrl);
    }
    
    /**
     * Gets the api version.
     *
     * @return string The api version.
     */
    public function getApiVersion()
    {
        return self::VERSION;
    }
    
    /**
     * Gets the cart version.
     *
     * @return string The cart version.
     */
    public function getCartVersion()
    {
        //return str_replace("DiCMS: version ", "", strip_tags(file_get_contents($this->apiUrl.'?version')));
    }
}
