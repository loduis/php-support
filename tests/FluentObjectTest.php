<?php

namespace Php\Tests;

use Error;
use LogicException;
use PHPUnit\Framework\TestCase;
use Php\FluentObject;
use OutOfBoundsException;
use ReflectionProperty;
use stdClass;

/**
 * @property TestObject2 $nestedObject
 */
class TestObject extends FluentObject
{
    public string $publicProp;

    protected ?string $protectedProp;

    private string $privateProp;

    protected ?TestObject2 $nestedObject;

    /**
     * @readonly
     */
    protected ?bool $readonly;

    protected function setProtectedProp($value)
    {
        return strtoupper($value);
    }

    protected function getProtectedProp($value)
    {
        return "Protected: " . $value;
    }

    protected function getReadonly()
    {
        return true;
    }
}

class TestObject2 extends FluentObject
{
    public string $nestedProp;
    protected float $nestedProtectedProp;

    protected function setNestedProtectedProp($value)
    {
        return round($value, 2);
    }

    protected function getNestedProtectedProp($value)
    {
        return "Nested Protected: " . $value;
    }
}

class FluentObjectTest extends TestCase
{
    private TestObject $object;

    protected function setUp(): void
    {
        $this->object = new TestObject();
    }

    public function testPublicPropertyAccess()
    {
        $this->object->publicProp = "test";
        $this->assertEquals("test", $this->object->publicProp);
    }

    public function testReadonly()
    {
        $test = new TestObject([
            'readonly' => false
        ]);

        $this->assertTrue($test->readonly);
        $this->expectException(LogicException::class);
        $test->readonly = true;
    }

    public function testProtectedPropertyAccess()
    {
        $this->object->protectedProp = "test";
        $this->assertEquals("Protected: TEST", $this->object->protectedProp);
    }

    public function testPrivatePropertyAccess()
    {
        $this->expectException(OutOfBoundsException::class);
        $this->object->privateProp = "test";
    }

    public function testArrayAccessSet()
    {
        $this->object['publicProp'] = "test";
        $this->assertEquals("test", $this->object['publicProp']);
    }

    public function testArrayAccessGet()
    {
        $this->object['protectedProp'] = "test";
        $this->assertEquals("Protected: TEST", $this->object['protectedProp']);
    }

    public function testArrayAccessExists()
    {
        $this->object['publicProp'] = "test";
        $this->assertTrue(isset($this->object['publicProp']));
        $this->assertFalse(isset($this->object['nonExistentProp']));
    }

    public function testArrayAccessUnset()
    {
        $this->object['publicProp'] = "test";
        unset($this->object['publicProp']);
        $this->assertFalse(isset($this->object['publicProp']));
    }

    public function testCustomSetter()
    {
        $this->object->protectedProp = "test";
        $reflection = new ReflectionProperty(TestObject::class, 'protectedProp');
        $reflection->setAccessible(true);
        $this->assertEquals("TEST", $reflection->getValue($this->object));
    }

    public function testCustomGetter()
    {
        $this->object->protectedProp = "test";
        $this->assertEquals("Protected: TEST", $this->object->protectedProp);
    }

    public function testToArray()
    {
        $this->object->publicProp = "public";
        $this->object->protectedProp = "protected";
        $expected = [
            'publicProp' => 'public',
            'protectedProp' => 'Protected: PROTECTED'
        ];
        $this->assertEquals($expected, $this->object->toArray());
    }

    public function testSyncProperties()
    {
        $data = [
            'publicProp' => 'public',
            'protectedProp' => 'protected'
        ];
        $this->object->syncProperties($data);
        $this->assertEquals('public', $this->object->publicProp);
        $this->assertEquals('Protected: PROTECTED', $this->object->protectedProp);
    }

    public function testNestedObjectProperty()
    {
        $this->object->nestedObject = new TestObject2();
        $this->object->nestedObject->nestedProp = "nested value";
        $this->object->nestedObject->nestedProtectedProp = 10.123;

        $this->assertInstanceOf(TestObject2::class, $this->object->nestedObject);
        $this->assertEquals("nested value", $this->object->nestedObject->nestedProp);
        $this->assertEquals("Nested Protected: 10.12", $this->object->nestedObject->nestedProtectedProp);
    }

    public function testNestedObjectToArray()
    {
        $this->object->publicProp = "public";
        $this->object->protectedProp = "protected";
        $this->object->nestedObject = new TestObject2();
        $this->object->nestedObject->nestedProp = "nested value";
        $this->object->nestedObject->nestedProtectedProp = 10.123;

        $expected = [
            'publicProp' => 'public',
            'protectedProp' => 'Protected: PROTECTED',
            'nestedObject' => [
                'nestedProp' => 'nested value',
                'nestedProtectedProp' => 'Nested Protected: 10.12'
            ]
        ];

        $this->assertEquals($expected, $this->object->toArray());
    }

    public function testNestedObjectSyncProperties()
    {
        $data = [
            'publicProp' => 'public',
            'protectedProp' => 'protected',
            'nestedObject' => [
                'nestedProp' => 'nested value',
                'nestedProtectedProp' => 10.123
            ]
        ];

        $this->object->syncProperties($data);

        $this->assertEquals('public', $this->object->publicProp);
        $this->assertEquals('Protected: PROTECTED', $this->object->protectedProp);
        $this->assertInstanceOf(TestObject2::class, $this->object->nestedObject);
        $this->assertEquals('nested value', $this->object->nestedObject->nestedProp);
        $this->assertEquals('Nested Protected: 10.12', $this->object->nestedObject->nestedProtectedProp);
    }

    public function testNestedStdClass()
    {
        $this->object->publicProp = "public";
        $this->object->protectedProp = "protected";
        $nestedObject = new stdClass();
        $nestedObject->nestedProp = "nested value";
        $nestedObject->nestedProtectedProp = 10.123;
        $this->object->nestedObject = $nestedObject;
        $this->assertEquals('Nested Protected: 10.12', $this->object->nestedObject->nestedProtectedProp);
    }
}