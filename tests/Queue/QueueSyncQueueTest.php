<?php

use Mockery as m;

class QueueSyncQueueTest extends PHPUnit\Framework\TestCase {

	public function tearDown()
	{
		m::close();
	}


	public function testPushShouldFireJobInstantly()
	{
		$sync = $this->getMockBuilder('Illuminate\Queue\SyncQueue')->setMethods(array('resolveJob'))->getMock();
		$job = m::mock('StdClass');
		$sync->expects($this->once())->method('resolveJob')->with($this->equalTo('Foo'), $this->equalTo('{"foo":"foobar"}'))->will($this->returnValue($job));
		$job->shouldReceive('fire')->once();

		$sync->push('Foo', array('foo' => 'foobar'));
	}

}
