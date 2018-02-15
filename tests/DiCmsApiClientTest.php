<?php
namespace ShopExpress\ApiClient\Test;

use ShopExpress\ApiClient;

class DiCmsApiClientTest extends \PHPUnit_Framework_TestCase
{
	public function testTrueIsFalse()
    {
    	$value = (function(){retun false;});
        $this->assertFalse($value);
    }

	
    public function provider()
    {
    	return [[true],[false]];
    }
}
