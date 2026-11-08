<?php

/**
 * Pest configuration, for the Unit suite.
 */

declare(strict_types=1);

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

use function Brain\Monkey\setUp;
use function Brain\Monkey\tearDown;

/*
 * Brain Monkey around every test: setUp() defines WordPress' hook functions and arms Patchwork;
 * tearDown() verifies every expectation and removes every mock. Brain Monkey's expectations are
 * Mockery expectations, which PHPUnit does not count: MockeryPHPUnitIntegration adds them to the
 * assertion count, so a test whose only check is `->once()` does not report as risky.
 */
uses(MockeryPHPUnitIntegration::class)
    ->beforeEach(function (): void {
        setUp();
    })
    ->afterEach(function (): void {
        tearDown();
    })
    ->in('Unit');
