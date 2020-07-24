<?php

use Mockery as m;
use Illuminate\Cache\DatabaseStore;

class CacheDatabaseStoreTest extends PHPUnit\Framework\TestCase {

	protected function tearDown(): void
	{
		m::close();
	}


	public function testNullIsReturnedWhenItemNotFound()
	{
		$store = $this->getStore();
		$table = m::mock('StdClass');
		$store->getConnection()->shouldReceive('table')->once()->with('table')->andReturn($table);
		$table->shouldReceive('where')->once()->with('key', '=', 'prefixfoo')->andReturn($table);
		$table->shouldReceive('first')->once()->andReturn(null);

		$this->assertNull($store->get('foo'));
	}


	public function testNullIsReturnedAndItemDeletedWhenItemIsExpired()
	{
		$store = $this->getMockBuilder('Illuminate\Cache\DatabaseStore')->setMethods(array('forget'))->setConstructorArgs($this->getMocks())->getMock();
		$table = m::mock('StdClass');
		$store->getConnection()->shouldReceive('table')->once()->with('table')->andReturn($table);
		$table->shouldReceive('where')->once()->with('key', '=', 'prefixfoo')->andReturn($table);
		$table->shouldReceive('first')->once()->andReturn((object) array('expiration' => 1));
		$store->expects($this->once())->method('forget')->with($this->equalTo('foo'))->will($this->returnValue(null));

		$this->assertNull($store->get('foo'));
	}


	public function testDecryptedValueIsReturnedWhenItemIsValid()
	{
		$store = $this->getStore();
		$table = m::mock('StdClass');
		$store->getConnection()->shouldReceive('table')->once()->with('table')->andReturn($table);
		$table->shouldReceive('where')->once()->with('key', '=', 'prefixfoo')->andReturn($table);
		$table->shouldReceive('first')->once()->andReturn((object) array('value' => 'bar', 'expiration' => 999999999999999));
		$store->getEncrypter()->shouldReceive('decrypt')->once()->with('bar')->andReturn('bar');

		$this->assertEquals('bar', $store->get('foo'));
	}


	public function testEncryptedValueIsInsertedWhenNoExceptionsAreThrown()
	{
		$store = $this->getMockBuilder('Illuminate\Cache\DatabaseStore')->setMethods(array('getTime'))->setConstructorArgs($this->getMocks())->getMock();
		$table = m::mock('StdClass');
		$store->getConnection()->shouldReceive('table')->once()->with('table')->andReturn($table);
		$store->getEncrypter()->shouldReceive('encrypt')->once()->with('bar')->andReturn('bar');
		$store->expects($this->once())->method('getTime')->will($this->returnValue(1));
		$table->shouldReceive('insert')->once()->with(array('key' => 'prefixfoo', 'value' => 'bar', 'expiration' => 61));

		$store->put('foo', 'bar', 1);
	}


	public function testEncryptedValueIsUpdatedWhenInsertThrowsException()
	{
		$store = $this->getMockBuilder('Illuminate\Cache\DatabaseStore')->setMethods(array('getTime'))->setConstructorArgs($this->getMocks())->getMock();
		$table = m::mock('StdClass');
		$store->getConnection()->shouldReceive('table')->with('table')->andReturn($table);
		$store->getEncrypter()->shouldReceive('encrypt')->once()->with('bar')->andReturn('bar');
		$store->expects($this->once())->method('getTime')->will($this->returnValue(1));
		$table->shouldReceive('insert')->once()->with(array('key' => 'prefixfoo', 'value' => 'bar', 'expiration' => 61))->andReturnUsing(function()
		{
			throw new Exception;
		});
		$table->shouldReceive('where')->once()->with('key', '=', 'prefixfoo')->andReturn($table);
		$table->shouldReceive('update')->once()->with(array('value' => 'bar', 'expiration' => 61));

		$store->put('foo', 'bar', 1);
	}


	public function testForeverCallsStoreItemWithReallyLongTime()
	{
		$store = $this->getMockBuilder('Illuminate\Cache\DatabaseStore')->setMethods(array('put'))->setConstructorArgs($this->getMocks())->getMock();
		$store->expects($this->once())->method('put')->with($this->equalTo('foo'), $this->equalTo('bar'), $this->equalTo(5256000));
		$store->forever('foo', 'bar');
	}


	public function testItemsMayBeRemovedFromCache()
	{
		$store = $this->getStore();
		$table = m::mock('StdClass');
		$store->getConnection()->shouldReceive('table')->once()->with('table')->andReturn($table);
		$table->shouldReceive('where')->once()->with('key', '=', 'prefixfoo')->andReturn($table);
		$table->shouldReceive('delete')->once();

		$store->forget('foo');
	}


	public function testItemsMayBeFlushedFromCache()
	{
		$store = $this->getStore();
		$table = m::mock('StdClass');
		$store->getConnection()->shouldReceive('table')->once()->with('table')->andReturn($table);
		$table->shouldReceive('delete')->once();

		$store->flush();
	}


	protected function getStore()
	{
		return new DatabaseStore(m::mock('Illuminate\Database\Connection'), m::mock('Illuminate\Encryption\Encrypter'), 'table', 'prefix');
	}


	protected function getMocks()
	{
		return array(m::mock('Illuminate\Database\Connection'), m::mock('Illuminate\Encryption\Encrypter'), 'table', 'prefix');
	}

}
