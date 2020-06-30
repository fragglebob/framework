<?php

use Mockery as m;

class CacheMemcachedConnectorTest extends PHPUnit\Framework\TestCase {

	public function tearDown()
	{
		m::close();
	}


	public function testServersAreAddedCorrectly()
	{
		$connector = $this->getMockBuilder('Illuminate\Cache\MemcachedConnector')->setMethods(array('getMemcached'))->getMock();
		$memcached = m::mock('stdClass');
		$memcached->shouldReceive('addServer')->once()->with('localhost', 11211, 100);
		$memcached->shouldReceive('getVersion')->once()->andReturn(true);
		$connector->expects($this->once())->method('getMemcached')->will($this->returnValue($memcached));
		$result = $connector->connect(array(array('host' => 'localhost', 'port' => 11211, 'weight' => 100)));

		$this->assertSame($result, $memcached);
	}


	/**
	 * @expectedException RuntimeException
	 */
	public function testExceptionThrownOnBadConnection()
	{
		$connector = $this->getMockBuilder('Illuminate\Cache\MemcachedConnector')->setMethods(array('getMemcached'))->getMock();
		$memcached = m::mock('stdClass');
		$memcached->shouldReceive('addServer')->once()->with('localhost', 11211, 100);
		$memcached->shouldReceive('getVersion')->once()->andReturn(false);
		$connector->expects($this->once())->method('getMemcached')->will($this->returnValue($memcached));
		$result = $connector->connect(array(array('host' => 'localhost', 'port' => 11211, 'weight' => 100)));
	}

}
