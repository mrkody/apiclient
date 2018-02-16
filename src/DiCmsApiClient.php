<?php
namespace ShopExpress\ApiClient;

use ShopExpress\ApiClient\Response\ApiResponse;
use ShopExpress\ApiClient\Exception\CurlException;
use ShopExpress\ApiClient\Exception\CsCartApiException;

class DiCmsApiClient
{
    const VERSION = '0.1 beta';
    
    const ERROR_API_CALLING = 'You have to specify a method (eg. POST, PUT, ...) and a correct object url to call the API';
    const ERROR_CURL_ERROR = 'HTTP error while calling the API. Error code and message: %s - %s';
    const ERROR_CSCART_API_MESSAGE = 'Message from API: %s';
    const ERROR_ARGUMENT_VALUE = 'Parameter `%s` can\'t be empty';

    public static $CURL_OPTS = [
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_USERAGENT => 'dicms-api-php-beta-0.1',
    ];
    protected $apiKey;
    protected $userLogin;
    protected $apiUrl;

    public function __construct($apiKey, $userLogin, $apiUrl)
    {
        $this->setApiKey($apiKey);
        $this->setUserLogin($userLogin);
        $this->setApiUrl($apiUrl);
    }

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

    public function getApiKey()
    {
        return $this->apiKey;
    }

    public function getUserLogin()
    {
        return $this->userLogin;
    }

    public function getApiUrl()
    {
        return $this->apiUrl;
    }

    protected function makeRequest($objectUrl, $method, $data = '', $params = [])
    {
        $ch = curl_init();

        $opts = self::$CURL_OPTS;
        //print_r($params);
        $opts[CURLOPT_URL] = $this->initUrl($objectUrl, $params);
        $opts[CURLOPT_USERPWD] = $this->getAuthString();
        //print_r($opts);
        $this->setHeader($opts, 'Content-Type: application/json');

        if ($method == 'POST' || $method == 'PUT') {
            $postdata = $this->generatePostData($data);
        } else {
            unset($data);
        }
        
        switch ($method) {
            case 'GET':
                break;
            case 'POST':
                $opts[CURLOPT_CUSTOMREQUEST] = 'POST';
                $opts[CURLOPT_RETURNTRANSFER] = true;
                $opts[CURLOPT_POSTFIELDS] = $postdata;
                $this->setHeader($opts, 'Content-Length: ' . strlen($postdata));
                break;
            case 'PUT':
                $opts[CURLOPT_CUSTOMREQUEST] = 'PUT';
                $opts[CURLOPT_RETURNTRANSFER] = true;
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
    
    protected function initUrl($objectUrl, array $params = [])
    {
        $params = !empty($params)? '?' . http_build_query($params) : '';
        return $this->apiUrl . $objectUrl . $params;
    }

    protected function getAuthString()
    {
        return $this->userLogin . ":" . $this->apiKey;
    }

    protected function setHeader(&$opts, $headerString)
    {
        $opts[CURLOPT_HTTPHEADER][] = $headerString;
    }

    protected function generatePostData($data)
    {
        return json_encode($data);
    }
    
    protected function parseResult($resultBody, $statusCode)
    {
        $response = new ApiResponse($statusCode, $resultBody);

        if (!empty($response['message'])) {
            throw new CsCartApiException(
                sprintf(self::ERROR_CSCART_API_MESSAGE, $response['message'])
            );
        } else {
            return $response;
        }
    }

    public function api($method, $objectUrl, $data = '', $params = [])
    {
        if (!empty($method) && !empty($objectUrl)) {
            return $this->makeRequest($objectUrl, $method, $data, $params);
        } else {
            throw new \InvalidArgumentException(self::ERROR_API_CALLING);
        }
    }
    
    public function get($objectUrl, $params = [])
    {
        return $this->api('GET', $objectUrl, '', $params);
    }

    public function update($objectUrl, $data)
    {
        return $this->api('PUT', $objectUrl, $data);
    }

    public function create($objectUrl, $data)
    {
        return $this->api('POST', $objectUrl, $data);
    }
    
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
        return str_replace("DiCMS: version ", "", strip_tags(file_get_contents($this->apiUrl.'?version')));
    }
}
