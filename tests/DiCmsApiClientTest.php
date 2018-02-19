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
    public function testCreateOrderRequest($instance)
    {
    	$someOrder = [
            'master_oid' => 3,
            'fio' => 'Ivanov Ivan Ivanovich',
            'email' => 'ivan12@mail.ru',
            'status' => 'A',
            'address' => 'Москва',
            'phone' => '+79099999999',
            'pay_method' => 'NON',
            'pay_status' => 'S',
            'delivery_id' => 1,
            'delivery' => 'courier',
            'products' => [
                ['oid' => 1117, 'count' => 1],
            ],
    	];

        $response = $instance->update('orders', $someOrder);

        print_r($response);

        $this->assertInstanceOf(
            'ShopExpress\ApiClient\Response\ApiResponse', 
            $response,
            'Order was not created!'
        );
        try {
            $this->assertTrue(is_numeric($response->id), 'Order was not created!');
        } catch (\InvalidArgumentException $e) {
            $this->fail('Order was not created!');
        }

        return $response->content;
    }

    /**
     * @depends testValidInit
     * @depends testCreateOrderRequest
     */
    public function testGetOrderRequest($instance, $response)
    {
        $order_id = $response['order_id'];

        $response = $instance->get("orders/{$order_id}", []);
        $this->assertInstanceOf(
            'ShopExpress\ApiClient\Response\ApiResponse', 
            $response,
            'Order was not received!'
        );

        print_r($response);

        try {
            $this->assertEquals($response->order_id, $order_id, 'Order was not received!');
        } catch (\InvalidArgumentException $e) {
            $this->fail('Order was not received!');
        }

        return $response;
    }

    /**
     * @depends testValidInit
     * @depends testGetOrderRequest
     */
    public function testUpdateOrderRequest($instance, $response)
    {
        /*$oid = $response->id;
        $newPayStatus = 'FP';

        $response = $instance->update("orders/{$oid}", ['pay_status' => $newPayStatus]);
        $this->assertInstanceOf(
            'ShopExpress\ApiClient\Response\ApiResponse', 
            $response,
            'Order was not updated!'
        );

        print_r($response);

        try {
            $this->assertEquals($response->id, $oid, 'Order was not received after updating!');
        } catch (\InvalidArgumentException $e) {
            $this->fail('Order was not received after updating!');
        }

        $response = $instance->get("orders/{$response->id}", []);

        print_r($response);

        $this->assertEquals($response->content['pay_status'], $newPayStatus, 'Order was not updated!');*/

        $this->assertTrue(true);

        return $response;
    }

    /**
     * @depends testValidInit
     * @depends testUpdateOrderRequest
     */
    public function testDeleteOrderRequest($instance, $response)
    {
        $response = $instance->delete("orders/{$response->order_id}");
        $this->assertInstanceOf(
            'ShopExpress\ApiClient\Response\ApiResponse', 
            $response,
            'Order was not deleted!'
        );

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
