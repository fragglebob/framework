<?php

use Mockery as m;

class AuthEloquentUserProviderTest extends PHPUnit\Framework\TestCase {

	protected function tearDown(): void
	{
		m::close();
	}


	public function testRetrieveByIDReturnsUser()
	{
		$provider = $this->getProviderMock();
		$mock = m::mock('stdClass');
		$mock->shouldReceive('newQuery')->once()->andReturn($mock);
		$mock->shouldReceive('find')->once()->with(1)->andReturn('bar');
		$provider->expects($this->once())->method('createModel')->will($this->returnValue($mock));
		$user = $provider->retrieveByID(1);

		$this->assertEquals('bar', $user);
	}


	public function testRetrieveByCredentialsReturnsUser()
	{
		$provider = $this->getProviderMock();
		$mock = m::mock('stdClass');
		$mock->shouldReceive('newQuery')->once()->andReturn($mock);
		$mock->shouldReceive('where')->once()->with('username', 'dayle');
		$mock->shouldReceive('first')->once()->andReturn('bar');
		$provider->expects($this->once())->method('createModel')->will($this->returnValue($mock));
		$user = $provider->retrieveByCredentials(array('username' => 'dayle', 'password' => 'foo'));

		$this->assertEquals('bar', $user);
	}


	public function testCredentialValidation()
	{
		$conn = m::mock('Illuminate\Database\Connection');
		$hasher = m::mock('Illuminate\Hashing\HasherInterface');
		$hasher->shouldReceive('check')->once()->with('plain', 'hash')->andReturn(true);
		$provider = new Illuminate\Auth\EloquentUserProvider($hasher, 'foo');
		$user = m::mock('Illuminate\Auth\UserInterface');
		$user->shouldReceive('getAuthPassword')->once()->andReturn('hash');
		$result = $provider->validateCredentials($user, array('password' => 'plain'));

		$this->assertTrue($result);
	}


    public function testRetrieveByTokenReturnsUser()
    {
        $mockUser = m::mock(stdClass::class);
        $mockUser->shouldReceive('getRememberToken')->once()->andReturn('a');

        $provider = $this->getProviderMock();
        $mock = m::mock(stdClass::class);
        $mock->shouldReceive('newQuery')->once()->andReturn($mock);
        $mock->shouldReceive('getAuthIdentifierName')->once()->andReturn('id');
        $mock->shouldReceive('where')->once()->with('id', 1)->andReturn($mock);
        $mock->shouldReceive('first')->once()->andReturn($mockUser);
        $provider->expects($this->once())->method('createModel')->willReturn($mock);
        $user = $provider->retrieveByToken(1, 'a');

        $this->assertEquals($mockUser, $user);
    }

    public function testRetrieveTokenWithBadIdentifierReturnsNull()
    {
        $provider = $this->getProviderMock();
        $mock = m::mock(stdClass::class);
        $mock->shouldReceive('newQuery')->once()->andReturn($mock);
        $mock->shouldReceive('getAuthIdentifierName')->once()->andReturn('id');
        $mock->shouldReceive('where')->once()->with('id', 1)->andReturn($mock);
        $mock->shouldReceive('first')->once()->andReturn(null);
        $provider->expects($this->once())->method('createModel')->willReturn($mock);
        $user = $provider->retrieveByToken(1, 'a');

        $this->assertNull($user);
    }

    public function testRetrieveByBadTokenReturnsNull()
    {
        $mockUser = m::mock(stdClass::class);
        $mockUser->shouldReceive('getRememberToken')->once()->andReturn(null);

        $provider = $this->getProviderMock();
        $mock = m::mock(stdClass::class);
        $mock->shouldReceive('newQuery')->once()->andReturn($mock);
        $mock->shouldReceive('getAuthIdentifierName')->once()->andReturn('id');
        $mock->shouldReceive('where')->once()->with('id', 1)->andReturn($mock);
        $mock->shouldReceive('first')->once()->andReturn($mockUser);
        $provider->expects($this->once())->method('createModel')->willReturn($mock);
        $user = $provider->retrieveByToken(1, 'a');

        $this->assertNull($user);
    }


	public function testModelsCanBeCreated()
	{
		$conn = m::mock('Illuminate\Database\Connection');
		$hasher = m::mock('Illuminate\Hashing\HasherInterface');
		$provider = new Illuminate\Auth\EloquentUserProvider($hasher, 'EloquentProviderUserStub');
		$model = $provider->createModel();

		$this->assertInstanceOf('EloquentProviderUserStub', $model);
	}


	protected function getProviderMock()
	{
		$hasher = m::mock('Illuminate\Hashing\HasherInterface');
		return $this->getMockBuilder('Illuminate\Auth\EloquentUserProvider')->setMethods(array('createModel'))->setConstructorArgs(array($hasher, 'foo'))->getMock();
	}

}

class EloquentProviderUserStub {}
