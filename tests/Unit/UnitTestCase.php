<?php

declare(strict_types=1);

namespace AndyDefer\LaravelCasts\Tests\Unit;

use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Base TestCase for all Unit tests.
 *
 * Unit tests should be fast, isolated, and not depend on Laravel's container.
 * Use mocks for external dependencies.
 */
abstract class UnitTestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        \Mockery::close();
    }
}
