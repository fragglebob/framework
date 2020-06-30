<?php

use Mockery as m;

class FoundationComposerTest extends PHPUnit\Framework\TestCase {

	public function tearDown()
	{
		m::close();
	}


	public function testDumpAutoloadRunsTheCorrectCommand()
	{
		$composer = $this->getMockBuilder('Illuminate\Foundation\Composer')->setMethods(array('getProcess'))->setConstructorArgs(array($files = m::mock('Illuminate\Filesystem\Filesystem'), __DIR__))->getMock();
		$files->shouldReceive('exists')->once()->with(__DIR__.'/composer.phar')->andReturn(true);
		$process = m::mock('stdClass');
		$composer->expects($this->once())->method('getProcess')->will($this->returnValue($process));
		$process->shouldReceive('setCommandLine')->once()->with('"'.PHP_BINARY.'" composer.phar dump-autoload');
		$process->shouldReceive('run')->once();

		$composer->dumpAutoloads();
	}


	public function testDumpAutoloadRunsTheCorrectCommandWhenComposerIsntPresent()
	{
		$composer = $this->getMockBuilder('Illuminate\Foundation\Composer')->setMethods(array('getProcess'))->setConstructorArgs(array($files = m::mock('Illuminate\Filesystem\Filesystem'), __DIR__))->getMock();
		$files->shouldReceive('exists')->once()->with(__DIR__.'/composer.phar')->andReturn(false);
		$process = m::mock('stdClass');
		$composer->expects($this->once())->method('getProcess')->will($this->returnValue($process));
		$process->shouldReceive('setCommandLine')->once()->with('composer dump-autoload');
		$process->shouldReceive('run')->once();

		$composer->dumpAutoloads();
	}

}
