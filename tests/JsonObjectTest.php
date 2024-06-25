<?php

namespace Php\Tests;

use PHPUnit\Framework\TestCase;
use Php\JsonObject;


class TestObject3 extends JsonObject
{
    public string $name;
}

class JsonObjectTest extends TestCase
{
    private TestObject3 $object;

    protected function setUp(): void
    {
        $this->object = new TestObject3();
    }

    public function testToString()
    {
        $this->object->name = 'Lucas';
        $this->assertEquals('{"name":"Lucas"}', (string) $this->object);
    }

    public function testJsonSerializable()
    {
        $this->object->name = 'Lucas';
        $this->assertEquals('{"name":"Lucas"}', json_encode($this->object));
    }

    public function testToJson()
    {
        $this->object->name = 'Lucas';
        $this->assertEquals('{"name":"Lucas"}', $this->object->toJson());
    }
}