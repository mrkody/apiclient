<?php
namespace ShopExpress\ApiClient\Test;

use ShopExpress\ApiClient\DiCmsApiClient;

class DiCmsApiClientTest extends \PHPUnit_Framework_TestCase
{
	protected static $config;

	public static function setUpBeforeClass()
	{
		static::$config = [
			'apiKey' => '1234',
	        'userLogin' => 'admin',
	        'apiUrl' => 'http://epetrov.kupikupi.org/adm/api/',
		];
	}

	public function simpleQuery($instance)
	{
		return $instance->get("groups", ['start' => 0, 'limit' => 1]);
	}
	
	/**
	 * @dataProvider invalidInitConfigProvider
	 * @expectedException \InvalidArgumentException
	 */
	public function testInvalidInit($apiKey, $userLogin, $apiUrl)
    {
    	$instance = new DiCmsApiClient($apiKey, $userLogin, $apiUrl);
    }

    public function testValidInit()
    {
    	$instance = new DiCmsApiClient(static::$config['apiKey'], static::$config['userLogin'], static::$config['apiUrl']);

    	return $instance;
    }

    /**
     * @depends testValidInit
     */
    public function testSendValidInitRequest($instance)
    {
    	$response = $this->simpleQuery($instance);
        $this->assertInstanceOf('ShopExpress\ApiClient\Response\ApiResponse', $response);
        $this->assertEquals(200, $response->getStatusCode());

        return $instance;
    }

    /**
     * @expectedException ShopExpress\ApiClient\Exception\InvalidJsonException
     */
    public function testSendInvalidInitRequest()
    {
    	$instance = new DiCmsApiClient(static::$config['apiKey'], static::$config['userLogin'], "http://example.ru");
    	$response = $this->simpleQuery($instance);
    }

    /**
     * @expectedException ShopExpress\ApiClient\Exception\InvalidJsonException
     */
    public function testSendInvalidInitRequest1()
    {
    	$instance = new DiCmsApiClient("wrong", static::$config['userLogin'], static::$config['apiUrl']);
    	$response = $this->simpleQuery($instance);
    }

    /**
     * @expectedException ShopExpress\ApiClient\Exception\InvalidJsonException
     */
    public function testSendInvalidInitRequest2()
    {
    	$instance = new DiCmsApiClient(static::$config['apiKey'], "wrong", static::$config['apiUrl']);
    	$response = $this->simpleQuery($instance);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSendInvalidInitRequest3()
    {
    	$instance = new DiCmsApiClient(static::$config['apiKey'], static::$config['userLogin'], "example.ru");
    	$response = $this->simpleQuery($instance);
    }

    /**
     * @depends testSendValidInitRequest
     * @expectedException \InvalidArgumentException
     */
    public function testSendInvalidObjectUrl($instance)
    {
    	$response = $instance->get("");
    }

    /**
     * @depends testValidInit
     */
    public function testGetApiKey($instance)
    {
    	$this->assertEquals(static::$config['apiKey'], $instance->getApiKey());
    }

    /**
     * @depends testValidInit
     */
    public function testGetUserLogin($instance)
    {
    	$this->assertEquals(static::$config['userLogin'], $instance->getUserLogin());
    }

    /**
     * @depends testValidInit
     */
    public function testGetApiUrl($instance)
    {
    	$this->assertEquals(static::$config['apiUrl'], $instance->getApiUrl());
    }

    public function invalidInitConfigProvider()
    {
    	return [
    		["", "s", "s"],
    		["s", "", "s"],
    		["s", "s", ""],
    		["", "", "s"],
    		["s", "", ""],
    		["", "s", ""],
    		["", "", "example.ru"],
    	];
    }

    public static function tearDownAfterClass()
    {
    	static::$config = null;
    }
}
