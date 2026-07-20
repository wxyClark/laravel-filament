<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\ApiTesting;

use App\Domains\ApiTesting\Services\TestExecutorService;
use ReflectionMethod;
use Tests\TestCase;

class TestExecutorAssertionTest extends TestCase
{
    protected function evaluate(string $operator, mixed $actual, mixed $expected): bool
    {
        $method = new ReflectionMethod(TestExecutorService::class, 'evaluateAssertion');
        $method->setAccessible(true);

        return $method->invoke(new TestExecutorService, $operator, $actual, $expected);
    }

    public function test_equals_operator(): void
    {
        expect($this->evaluate('equals', 200, 200))->toBeTrue()
            ->and($this->evaluate('equals', 200, 404))->toBeFalse();
    }

    public function test_not_equals_operator(): void
    {
        expect($this->evaluate('not_equals', 200, 404))->toBeTrue()
            ->and($this->evaluate('not_equals', 200, 200))->toBeFalse();
    }

    public function test_gt_and_gte_operators(): void
    {
        expect($this->evaluate('gt', 10, 5))->toBeTrue()
            ->and($this->evaluate('gt', 5, 5))->toBeFalse()
            ->and($this->evaluate('gte', 5, 5))->toBeTrue();
    }

    public function test_lt_and_lte_operators(): void
    {
        expect($this->evaluate('lt', 5, 10))->toBeTrue()
            ->and($this->evaluate('lt', 10, 10))->toBeFalse()
            ->and($this->evaluate('lte', 10, 10))->toBeTrue();
    }

    public function test_contains_operator(): void
    {
        expect($this->evaluate('contains', 'hello world', 'world'))->toBeTrue()
            ->and($this->evaluate('contains', 'hello', 'xyz'))->toBeFalse();
    }

    public function test_exists_and_not_exists_operators(): void
    {
        expect($this->evaluate('exists', 'value', null))->toBeTrue()
            ->and($this->evaluate('exists', null, null))->toBeFalse()
            ->and($this->evaluate('not_exists', null, null))->toBeTrue()
            ->and($this->evaluate('not_exists', 'value', null))->toBeFalse();
    }

    public function test_in_and_not_in_operators(): void
    {
        expect($this->evaluate('in', 'a', ['a', 'b', 'c']))->toBeTrue()
            ->and($this->evaluate('in', 'z', ['a', 'b', 'c']))->toBeFalse()
            ->and($this->evaluate('not_in', 'z', ['a', 'b', 'c']))->toBeTrue()
            ->and($this->evaluate('not_in', 'a', ['a', 'b', 'c']))->toBeFalse();
    }

    public function test_type_operator(): void
    {
        expect($this->evaluate('type', 123, 'integer'))->toBeTrue()
            ->and($this->evaluate('type', '123', 'integer'))->toBeFalse()
            ->and($this->evaluate('type', 'abc', 'string'))->toBeTrue();
    }

    public function test_unknown_operator_fails(): void
    {
        expect($this->evaluate('unknown_op', 'x', 'y'))->toBeFalse();
    }
}
