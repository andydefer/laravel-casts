<?php

declare(strict_types=1);

namespace AndyDefer\LaravelEloquentCasts\Tests\Unit\Casts;

use AndyDefer\LaravelEloquentCasts\Casts\MoneyCast;
use AndyDefer\LaravelEloquentCasts\Tests\Unit\UnitTestCase;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

#[AllowMockObjectsWithoutExpectations]
final class MoneyCastTest extends UnitTestCase
{
    private MoneyCast $cast;

    private Model $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cast = new MoneyCast;
        $this->model = $this->createMock(Model::class);
    }

    public function test_get_converts_cents_to_euros_with_two_decimals(): void
    {
        $result = $this->cast->get($this->model, 'amount', 1234, []);

        $this->assertIsFloat($result);
        $this->assertSame(12.34, $result);
    }

    public function test_get_rounds_cents_correctly(): void
    {
        $result = $this->cast->get($this->model, 'amount', 123, []);
        $this->assertSame(1.23, $result);

        $result = $this->cast->get($this->model, 'amount', 5, []);
        $this->assertSame(0.05, $result);

        $result = $this->cast->get($this->model, 'amount', 0, []);
        $this->assertSame(0.00, $result);
    }

    public function test_get_handles_large_amounts(): void
    {
        $result = $this->cast->get($this->model, 'amount', 123456789, []);

        $this->assertSame(1234567.89, $result);
    }

    public function test_get_handles_negative_amounts(): void
    {
        $result = $this->cast->get($this->model, 'amount', -500, []);

        $this->assertSame(-5.00, $result);
    }

    public function test_get_returns_null_when_value_is_null(): void
    {
        $result = $this->cast->get($this->model, 'amount', null, []);

        $this->assertNull($result);
    }

    public function test_set_converts_euros_to_cents(): void
    {
        $result = $this->cast->set($this->model, 'amount', 12.34, []);

        $this->assertIsInt($result);
        $this->assertSame(1234, $result);
    }

    public function test_set_rounds_cents_correctly(): void
    {
        $result = $this->cast->set($this->model, 'amount', 1.234, []);
        $this->assertSame(123, $result);

        $result = $this->cast->set($this->model, 'amount', 0.055, []);
        $this->assertSame(6, $result);

        $result = $this->cast->set($this->model, 'amount', 1.999, []);
        $this->assertSame(200, $result);
    }

    public function test_set_handles_large_amounts(): void
    {
        $result = $this->cast->set($this->model, 'amount', 1234567.89, []);

        $this->assertSame(123456789, $result);
    }

    public function test_set_handles_negative_amounts(): void
    {
        $result = $this->cast->set($this->model, 'amount', -5.00, []);

        $this->assertSame(-500, $result);
    }

    public function test_set_handles_integer_input(): void
    {
        $result = $this->cast->set($this->model, 'amount', 10, []);

        $this->assertSame(1000, $result);
    }

    public function test_set_handles_zero(): void
    {
        $result = $this->cast->set($this->model, 'amount', 0, []);
        $result2 = $this->cast->set($this->model, 'amount', 0.00, []);

        $this->assertSame(0, $result);
        $this->assertSame(0, $result2);
    }

    public function test_set_returns_null_when_value_is_null(): void
    {
        $result = $this->cast->set($this->model, 'amount', null, []);

        $this->assertNull($result);
    }

    public function test_null_values_are_preserved_through_round_trip(): void
    {
        $dbValue = $this->cast->set($this->model, 'amount', null, []);
        $appValue = $this->cast->get($this->model, 'amount', $dbValue, []);

        $this->assertNull($dbValue);
        $this->assertNull($appValue);
    }

    public function test_complete_workflow_with_nullable_values(): void
    {
        $dbValue = $this->cast->set($this->model, 'amount', null, []);
        $appValue = $this->cast->get($this->model, 'amount', $dbValue, []);

        $this->assertNull($dbValue);
        $this->assertNull($appValue);

        $dbValue = $this->cast->set($this->model, 'amount', 12.34, []);
        $this->assertSame(1234, $dbValue);

        $dbValue = $this->cast->set($this->model, 'amount', null, []);
        $this->assertNull($dbValue);
    }
}
