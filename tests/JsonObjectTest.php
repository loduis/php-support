<?php

namespace PHP\Tests;

use PHPUnit\Framework\TestCase;
use PHP\JsonObject;
use PHP\Http\Required;
use PHP\Attribute\Transform;
use PHP\Http\JsonTrait;

class TestHttp extends JsonObject
{
    use JsonTrait;

    #[Required('POST')]
    #[Transform('dash')]
    public ?string $lastName = null;

    #[Transform('snake')]
    public ?string $firstName = null;

    #[Transform('lower')]
    public ?string $middleNames = null;
}


class TestObject3 extends JsonObject
{
    public string $name;

    public ?string $tradename;
}

class JsonObjectTest extends TestCase
{
    private TestObject3 $object;

    protected function setUp(): void
    {
        $this->object = new TestObject3();
    }

    public function testHttp()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("'lastName' property is required for POST requests");
        $ob = new TestHttp();
        $ob->httpMethod('post');
        // $ob->lastName = 'Lucas';
        $ob->firstName = 'Martinez';
        $data = $ob->toArray();
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

    public function testToJson2()
    {
        $this->object->name = 'Lucas';
        $this->object->tradename = null;
        $this->assertEquals('{"name":"Lucas","tradename":null}', $this->object->toJson());
    }
}