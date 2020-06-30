<?php

use Mockery as m;
use Illuminate\Queue\Worker;

class QueueWorkerTest extends PHPUnit\Framework\TestCase {

	public function tearDown()
	{
		m::close();
	}


	public function testJobIsPoppedOffQueueAndProcessed()
	{
		$worker = $this->getMockBuilder('Illuminate\Queue\Worker')->setMethods(array('process'))->setConstructorArgs(array($manager = m::mock('Illuminate\Queue\QueueManager')))->getMock();
		$manager->shouldReceive('connection')->once()->with('connection')->andReturn($connection = m::mock('StdClass'));
		$manager->shouldReceive('getName')->andReturn('connection');
		$job = m::mock('Illuminate\Queue\Jobs\Job');
		$connection->shouldReceive('pop')->once()->with('queue')->andReturn($job);
		$worker->expects($this->once())->method('process')->with($this->equalTo('connection'), $this->equalTo($job), $this->equalTo(0), $this->equalTo(0));

		$worker->pop('connection', 'queue');
	}


	public function testJobIsPoppedOffFirstQueueInListAndProcessed()
	{
		$worker = $this->getMockBuilder('Illuminate\Queue\Worker')->setMethods(array('process'))->setConstructorArgs(array($manager = m::mock('Illuminate\Queue\QueueManager')))->getMock();
		$manager->shouldReceive('connection')->once()->with('connection')->andReturn($connection = m::mock('StdClass'));
		$manager->shouldReceive('getName')->andReturn('connection');
		$job = m::mock('Illuminate\Queue\Jobs\Job');
		$connection->shouldReceive('pop')->once()->with('queue1')->andReturn(null);
		$connection->shouldReceive('pop')->once()->with('queue2')->andReturn($job);
		$worker->expects($this->once())->method('process')->with($this->equalTo('connection'), $this->equalTo($job), $this->equalTo(0), $this->equalTo(0));

		$worker->pop('connection', 'queue1,queue2');
	}


	public function testWorkerSleepsIfNoJobIsPresentAndSleepIsEnabled()
	{
		$worker = $this->getMockBuilder('Illuminate\Queue\Worker')->setMethods(array('process', 'sleep'))->setConstructorArgs(array($manager = m::mock('Illuminate\Queue\QueueManager')))->getMock();
		$manager->shouldReceive('connection')->once()->with('connection')->andReturn($connection = m::mock('StdClass'));
		$connection->shouldReceive('pop')->once()->with('queue')->andReturn(null);
		$worker->expects($this->never())->method('process');
		$worker->expects($this->once())->method('sleep')->with($this->equalTo(3));

		$worker->pop('connection', 'queue', 0, 3);
	}


	public function testWorkerLogsJobToFailedQueueIfMaxTriesHasBeenExceeded()
	{
		$worker = new Illuminate\Queue\Worker(m::mock('Illuminate\Queue\QueueManager'), $failer = m::mock('Illuminate\Queue\Failed\FailedJobProviderInterface'));
		$job = m::mock('Illuminate\Queue\Jobs\Job');
		$job->shouldReceive('attempts')->once()->andReturn(10);
		$job->shouldReceive('getQueue')->once()->andReturn('queue');
		$job->shouldReceive('getRawBody')->once()->andReturn('body');
		$job->shouldReceive('delete')->once();
		$failer->shouldReceive('log')->once()->with('connection', 'queue', 'body');

		$worker->process('connection', $job, 3, 0);
	}


	public function testProcessFiresJobAndAutoDeletesIfTrue()
	{
		$worker = new Illuminate\Queue\Worker(m::mock('Illuminate\Queue\QueueManager'), null, new Illuminate\Events\Dispatcher());
		$job = m::mock('Illuminate\Queue\Jobs\Job');
		$job->shouldReceive('fire')->once();
		$job->shouldReceive('getName')->andReturn('foo');
		$job->shouldReceive('autoDelete')->once()->andReturn(true);
		$job->shouldReceive('delete')->once();

		$worker->process('connection', $job, 0, 0);
	}


	public function testProcessFiresJobAndDoesntCallDeleteIfJobDoesntAutoDelete()
	{
		$worker = new Illuminate\Queue\Worker(m::mock('Illuminate\Queue\QueueManager'), null, new Illuminate\Events\Dispatcher());
		$job = m::mock('Illuminate\Queue\Jobs\Job');
		$job->shouldReceive('fire')->once();
		$job->shouldReceive('autoDelete')->once()->andReturn(false);
		$job->shouldReceive('delete')->never();
		$job->shouldReceive('getName')->andReturn('foo');

		$worker->process('connection', $job, 0, 0);
	}

	public function testProcessFiresStartingAndFinishedEvent()
	{
		$called = 0;

		$worker = new Illuminate\Queue\Worker(m::mock('Illuminate\Queue\QueueManager'), null, $event = new Illuminate\Events\Dispatcher());
		$job = m::mock('Illuminate\Queue\Jobs\Job');

		$job->shouldReceive('getName')->andReturn('foo');
		$job->shouldReceive('fire')->once();
		$job->shouldReceive('autoDelete')->once()->andReturn(false);
		$job->shouldReceive('delete')->never();

		$event->listen('illuminate.queue.starting', function($name) use (&$called) {
			$this->assertEquals('foo', $name);
			$called++;
		});

		$event->listen('illuminate.queue.finished', function($name) use (&$called) {
			$this->assertEquals('foo', $name);
			$called++;
		});

		$worker->process('connection', $job, 0, 0);

		$this->assertEquals($called, 2);
	}



	/**
	 * @expectedException RuntimeException
	 */
	public function testJobIsReleasedWhenExceptionIsThrown()
	{
		$worker = new Illuminate\Queue\Worker(m::mock('Illuminate\Queue\QueueManager'), null, new Illuminate\Events\Dispatcher());
		$job = m::mock('Illuminate\Queue\Jobs\Job');
		$job->shouldReceive('fire')->once()->andReturnUsing(function() { throw new RuntimeException; });
		$job->shouldReceive('isDeleted')->once()->andReturn(false);
		$job->shouldReceive('release')->once()->with(5);
		$job->shouldReceive('getName')->andReturn('foo');

		$worker->process('connection', $job, 0, 5);
	}

	/**
	 * @expectedException RuntimeException
	 */
	public function testProcessFiresStartingAndFinishedEventWhenExceptionIsThrown()
	{
		$called = 0;

		$worker = new Illuminate\Queue\Worker(m::mock('Illuminate\Queue\QueueManager'), null, $event = new Illuminate\Events\Dispatcher());
		$job = m::mock('Illuminate\Queue\Jobs\Job');
		$job->shouldReceive('fire')->once()->andReturnUsing(function() { throw new RuntimeException; });
		$job->shouldReceive('isDeleted')->once()->andReturn(false);
		$job->shouldReceive('release')->once()->with(5);
		$job->shouldReceive('getName')->andReturn('foo');

		$event->listen('illuminate.queue.starting', function($name) use (&$called) {#
			$this->assertEquals('foo', $name);
			$called++;
		});

		$event->listen('illuminate.queue.finished', function($name, $error = null) use (&$called) {
			$this->assertEquals('foo', $name);
			$this->assertNotNull($error);
			$this->assertInstanceOf(RuntimeException::class, $error);
			$called++;
		});

		$worker->process('connection', $job, 0, 5);

		$this->assertEquals($called, 2);
	}

	/**
	 * @expectedException RuntimeException
	 */
	public function testJobIsNotReleasedWhenExceptionIsThrownButJobIsDeleted()
	{
		$worker = new Illuminate\Queue\Worker(m::mock('Illuminate\Queue\QueueManager'), null, new Illuminate\Events\Dispatcher());
		$job = m::mock('Illuminate\Queue\Jobs\Job');
		$job->shouldReceive('fire')->once()->andReturnUsing(function() { throw new RuntimeException; });
		$job->shouldReceive('isDeleted')->once()->andReturn(true);
		$job->shouldReceive('release')->never();
		$job->shouldReceive('getName')->andReturn('foo');

		$worker->process('connection', $job, 0, 5);
	}

}
