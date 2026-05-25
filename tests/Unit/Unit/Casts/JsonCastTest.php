<?php

declare(strict_types=1);

namespace AndyDefer\LaravelEloquentCasts\Tests\Unit\Casts;

use AndyDefer\LaravelEloquentCasts\Casts\JsonCast;
use AndyDefer\LaravelEloquentCasts\Tests\Unit\UnitTestCase;
use Illuminate\Database\Eloquent\Model;
use JsonException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

#[AllowMockObjectsWithoutExpectations]
final class JsonCastTest extends UnitTestCase
{
    private JsonCast $cast;

    private Model $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cast = new JsonCast;
        $this->model = $this->createMock(Model::class);
    }

    public function test_get_returns_null_when_value_is_null(): void
    {
        $result = $this->cast->get($this->model, 'metadata', null, []);

        $this->assertNull($result);
    }

    public function test_get_returns_array_when_value_is_already_array(): void
    {
        $expectedArray = ['key' => 'value', 'nested' => ['data' => 42]];

        $result = $this->cast->get($this->model, 'metadata', $expectedArray, []);

        $this->assertSame($expectedArray, $result);
    }

    public function test_get_decodes_valid_json_string_to_array(): void
    {
        $jsonString = '{"user":"John","preferences":{"theme":"dark","notifications":true}}';
        $expectedArray = ['user' => 'John', 'preferences' => ['theme' => 'dark', 'notifications' => true]];

        $result = $this->cast->get($this->model, 'metadata', $jsonString, []);

        $this->assertIsArray($result);
        $this->assertSame($expectedArray, $result);
    }

    public function test_get_decodes_empty_json_object_to_empty_array(): void
    {
        $jsonString = '{}';

        $result = $this->cast->get($this->model, 'metadata', $jsonString, []);

        $this->assertIsArray($result);
        $this->assertSame([], $result);
    }

    public function test_get_returns_null_when_json_string_is_invalid(): void
    {
        $invalidJson = '{invalid json with no quotes}';

        $result = $this->cast->get($this->model, 'metadata', $invalidJson, []);

        $this->assertNull($result);
    }

    public function test_get_returns_empty_array_when_json_decodes_to_non_array(): void
    {
        $jsonString = '"just a string, not an array"';

        $result = $this->cast->get($this->model, 'metadata', $jsonString, []);

        $this->assertIsArray($result);
        $this->assertSame([], $result);
    }

    public function test_set_returns_null_when_value_is_null(): void
    {
        $result = $this->cast->set($this->model, 'metadata', null, []);

        $this->assertNull($result);
    }

    public function test_set_returns_string_when_value_is_valid_json_string(): void
    {
        $validJsonString = '{"preserve":"me","keep":"unchanged"}';

        $result = $this->cast->set($this->model, 'metadata', $validJsonString, []);

        $this->assertSame($validJsonString, $result);
    }

    public function test_set_converts_array_to_json_string(): void
    {
        $inputArray = ['user' => 'Jane', 'roles' => ['admin', 'editor']];
        $expectedJson = json_encode($inputArray);

        $result = $this->cast->set($this->model, 'metadata', $inputArray, []);

        $this->assertIsString($result);
        $this->assertSame($expectedJson, $result);
    }

    public function test_set_throws_json_exception_for_non_encodable_values(): void
    {
        $nonEncodableResource = fopen('php://memory', 'r');

        $this->expectException(JsonException::class);
        $this->cast->set($this->model, 'metadata', $nonEncodableResource, []);

        fclose($nonEncodableResource);
    }
}
