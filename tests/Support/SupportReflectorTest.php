<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Reflector;
use PHPUnit\Framework\TestCase;

class SupportReflectorTest extends TestCase
{

    public function testSelfClassName()
    {
        $method = (new ReflectionClass(Model::class))->getMethod('newPivot');

        $this->assertSame(Model::class, Reflector::getParameterClassName($method->getParameters()[0]));
    }

    public function testParentClassName()
    {
        $method = (new ReflectionClass(B::class))->getMethod('f');

        $this->assertSame(A::class, Reflector::getParameterClassName($method->getParameters()[0]));
    }

    /**
     * @requires PHP 8
     */
    public function testUnionTypeName()
    {
        $method = (new ReflectionClass(C::class))->getMethod('f');

        $this->assertNull(Reflector::getParameterClassName($method->getParameters()[0]));
    }

    public function testIsCallable()
    {
        $this->assertTrue(Reflector::isCallable(function () {
        }));
        $this->assertTrue(Reflector::isCallable([B::class, 'f']));
        $this->assertFalse(Reflector::isCallable([TestClassWithCall::class, 'f']));
        $this->assertTrue(Reflector::isCallable([new TestClassWithCall, 'f']));
        $this->assertTrue(Reflector::isCallable([TestClassWithCallStatic::class, 'f']));
        $this->assertFalse(Reflector::isCallable([new TestClassWithCallStatic, 'f']));
        $this->assertFalse(Reflector::isCallable([new TestClassWithCallStatic]));
        $this->assertFalse(Reflector::isCallable(['TotallyMissingClass', 'foo']));
        $this->assertTrue(Reflector::isCallable(['TotallyMissingClass', 'foo'], true));
    }
}

class A
{
}

class B extends A
{
    public function f(parent $x)
    {
    }
}

if (PHP_MAJOR_VERSION >= 8) {
    $result = eval('
class C
{
    public function f(A|Model $x)
    {
    }
}'
    );
}

class TestClassWithCall
{
    public function __call($method, $parameters)
    {
    }
}

class TestClassWithCallStatic
{
    public static function __callStatic($method, $parameters)
    {
    }
}
