<?php

use Mockery as m;
use Illuminate\Support\Facades\Response;

class SupportFacadeResponseTest extends PHPUnit\Framework\TestCase {

	protected function tearDown(): void
	{
		m::close();
	}


	public function testArrayableSendAsJson()
	{
		$data = m::mock('Illuminate\Support\Contracts\ArrayableInterface');
		$data->shouldReceive('toArray')->andReturn(array('foo' => 'bar'));

		$response = Response::json($data);
		$this->assertEquals('{"foo":"bar"}', $response->getContent());
	}

}
