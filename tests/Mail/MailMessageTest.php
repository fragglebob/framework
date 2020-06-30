<?php

use Mockery as m;

class MailMessageTest extends PHPUnit\Framework\TestCase {

	public function tearDown()
	{
		m::close();
	}


	public function testBasicAttachment()
	{
		$swift = m::mock('StdClass');
		$message = $this->getMockBuilder('Illuminate\Mail\Message')->setMethods(array('createAttachmentFromPath'))->setConstructorArgs(array($swift))->getMock();
		$attachment = m::mock('StdClass');
		$message->expects($this->once())->method('createAttachmentFromPath')->with($this->equalTo('foo.jpg'))->will($this->returnValue($attachment));
		$swift->shouldReceive('attach')->once()->with($attachment);
		$attachment->shouldReceive('setContentType')->once()->with('image/jpeg');
		$attachment->shouldReceive('setFilename')->once()->with('bar.jpg');
		$message->attach('foo.jpg', array('mime' => 'image/jpeg', 'as' => 'bar.jpg'));
	}


	public function testDataAttachment()
	{
		$swift = m::mock('StdClass');
		$message = $this->getMockBuilder('Illuminate\Mail\Message')->setMethods(array('createAttachmentFromData'))->setConstructorArgs(array($swift))->getMock();
		$attachment = m::mock('StdClass');
		$message->expects($this->once())->method('createAttachmentFromData')->with($this->equalTo('foo'), $this->equalTo('name'))->will($this->returnValue($attachment));
		$swift->shouldReceive('attach')->once()->with($attachment);
		$attachment->shouldReceive('setContentType')->once()->with('image/jpeg');
		$message->attachData('foo', 'name', array('mime' => 'image/jpeg'));
	}

}
