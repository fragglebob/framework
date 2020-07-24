<?php

use Mockery as m;
use Illuminate\View\Engines\PhpEngine;

class ViewPhpEngineTest extends PHPUnit\Framework\TestCase {

	protected function tearDown(): void
	{
		m::close();
	}


	public function testViewsMayBeProperlyRendered()
	{
		$engine = new PhpEngine;
		$this->assertEquals("Hello World\n", $engine->get(__DIR__.'/fixtures/basic.php'));
	}

}
