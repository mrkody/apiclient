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
    public function testSendInvalidObjectUrlForGet($instance)
    {
    	$response = $instance->get("");
	}

    /**
     * @depends testSendValidInitRequest
     * @expectedException \InvalidArgumentException
     */
    public function testSendInvalidObjectUrlForUpdate($instance)
    {
    	$response = $instance->update("", []);
    }

    /**
     * @depends testSendValidInitRequest
     * @expectedException \InvalidArgumentException
     */
    public function testSendInvalidObjectUrlForCreate($instance)
    {
    	$response = $instance->create("", []);
	}

	/**
     * @depends testSendValidInitRequest
     * @expectedException \InvalidArgumentException
     */
    public function testSendInvalidObjectUrlForDelete($instance)
    {
    	$response = $instance->delete("", []);
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

    /**
     * @depends testValidInit
     */
    public function testGetApiVersion($instance)
    {
    	$this->assertTrue(!empty($instance->getApiVersion()));
    }


    /**
     * @depends testValidInit
     */
    public function testCreateUserRequest($instance)
    {
    	$someUser = [
            'parent_oid' => 4,
            //'master_oid' => 3,
            'name' => 'Иван',
            'login' => 'ivan',
            'password' => '12345',
    	];

        $response = $instance->create('users', $someUser);

        print_r($response);

        $this->assertInstanceOf(
            'ShopExpress\ApiClient\Response\ApiResponse', 
            $response,
            'User was not created!'
        );
        $this->assertTrue($response->oid > 0, 'User was not created!');

        return $response;
    }

    /**
     * @depends testValidInit
     * @depends testCreateUserRequest
     */
    public function testGetUserRequest($instance, $response)
    {
        $oid = $response->oid;

        $response = $instance->get("users/{$oid}", []);
        $this->assertInstanceOf(
            'ShopExpress\ApiClient\Response\ApiResponse', 
            $response,
            'User was not received!'
        );

        print_r($response);

        try {
            $this->assertEquals($response->oid, $oid, 'User was not received!');
        } catch (\InvalidArgumentException $e) {
            $this->fail('User was not received!');
        }

        return $response;
    }

    /**
     * @depends testValidInit
     * @depends testGetUserRequest
     */
    public function testUpdateUserRequest($instance, $response)
    {
        $newParendOid = 2;

        $response = $instance->update("users/{$response->oid}", ['parent_oid' => $newParendOid]);
        $this->assertInstanceOf(
            'ShopExpress\ApiClient\Response\ApiResponse', 
            $response,
            'User was not updated!'
        );
        print_r($response);

        try {
            $this->assertEquals($response->oid, $oid, 'User was not received after updating!');
        } catch (\InvalidArgumentException $e) {
            $this->fail('User was not received after updating!');
        }

        $response = $instance->get("users/{$response->oid}", []);
        print_r($response);
        $this->assertEquals($response->parent_oid, $newParendOid, 'User was not updated!');

    }

    /**
     * @depends testValidInit
     * @depends testCreateUserRequest
     */
    public function testDeleteUserRequest($instance, $response)
    {
        $response = $instance->delete("users/{$response->oid}", []);

        print_r($response);
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
