<?php

use Illuminate\Foundation\AliasLoader;

class FoundationAliasLoaderTest extends PHPUnit\Framework\TestCase {

	public function testLoaderCanBeCreatedAndRegisteredOnce()
	{
		$loader = $this->getMockBuilder('Illuminate\Foundation\AliasLoader')->setMethods(array('prependToLoaderStack'))->setConstructorArgs(array(array('foo' => 'bar')))->getMock();
		$loader->expects($this->once())->method('prependToLoaderStack');

		$this->assertEquals(array('foo' => 'bar'), $loader->getAliases());
		$this->assertFalse($loader->isRegistered());
		$loader->register();

		$this->assertTrue($loader->isRegistered());
	}


	public function testGetInstanceCreatesOneInstance()
	{
		$loader = AliasLoader::getInstance(array('foo' => 'bar'));
		$this->assertEquals($loader, AliasLoader::getInstance());
	}

}
