<?php

class CacheMemcachedStoreTest extends PHPUnit\Framework\TestCase {

	public function testGetReturnsNullWhenNotFound()
	{
		$memcache = $this->getMockBuilder('StdClass')->setMethods(array('get', 'getResultCode'))->getMock();
		$memcache->expects($this->once())->method('get')->with($this->equalTo('foo:bar'))->will($this->returnValue(null));
		$memcache->expects($this->once())->method('getResultCode')->will($this->returnValue(1));
		$store = new Illuminate\Cache\MemcachedStore($memcache, 'foo');
		$this->assertNull($store->get('bar'));
	}


	public function testMemcacheValueIsReturned()
	{
		$memcache = $this->getMockBuilder('StdClass')->setMethods(array('get', 'getResultCode'))->getMock();
		$memcache->expects($this->once())->method('get')->will($this->returnValue('bar'));
		$memcache->expects($this->once())->method('getResultCode')->will($this->returnValue(0));
		$store = new Illuminate\Cache\MemcachedStore($memcache);
		$this->assertEquals('bar', $store->get('foo'));
	}


	public function testSetMethodProperlyCallsMemcache()
	{
		$memcache = $this->getMockBuilder('Memcached')->setMethods(array('set'))->getMock();
		$memcache->expects($this->once())->method('set')->with($this->equalTo('foo'), $this->equalTo('bar'), $this->equalTo(60));
		$store = new Illuminate\Cache\MemcachedStore($memcache);
		$store->put('foo', 'bar', 1);
	}


	public function testIncrementMethodProperlyCallsMemcache()
	{
		$memcache = $this->getMockBuilder('Memcached')->setMethods(array('increment'))->getMock();
		$memcache->expects($this->once())->method('increment')->with($this->equalTo('foo'), $this->equalTo(5));
		$store = new Illuminate\Cache\MemcachedStore($memcache);
		$store->increment('foo', 5);
	}


	public function testDecrementMethodProperlyCallsMemcache()
	{
		$memcache = $this->getMockBuilder('Memcached')->setMethods(array('decrement'))->getMock();
		$memcache->expects($this->once())->method('decrement')->with($this->equalTo('foo'), $this->equalTo(5));
		$store = new Illuminate\Cache\MemcachedStore($memcache);
		$store->decrement('foo', 5);
	}


	public function testStoreItemForeverProperlyCallsMemcached()
	{
		$memcache = $this->getMockBuilder('Memcached')->setMethods(array('set'))->getMock();
		$memcache->expects($this->once())->method('set')->with($this->equalTo('foo'), $this->equalTo('bar'), $this->equalTo(0));
		$store = new Illuminate\Cache\MemcachedStore($memcache);
		$store->forever('foo', 'bar');
	}


	public function testForgetMethodProperlyCallsMemcache()
	{
		$memcache = $this->getMockBuilder('Memcached')->setMethods(array('delete'))->getMock();
		$memcache->expects($this->once())->method('delete')->with($this->equalTo('foo'));
		$store = new Illuminate\Cache\MemcachedStore($memcache);
		$store->forget('foo');
	}

}
