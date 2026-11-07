<?php

/**
 * Pest configuration, for the Unit suite.
 */

declare(strict_types=1);

use function Brain\Monkey\setUp;
use function Brain\Monkey\tearDown;

/*
 * Brain Monkey around every test: setUp() defines WordPress' hook functions and arms Patchwork;
 * tearDown() verifies every expectation and removes every mock. Brain Monkey's expectations are
 * Mockery expectations, which PHPUnit does not count, so they are added to the assertion count or
 * a test whose only check is `->once()` reports as risky.
 */
uses()
    ->beforeEach(function (): void {
        setUp();
    })
    ->afterEach(function (): void {
        $this->addToAssertionCount(Mockery::getContainer()->mockery_getExpectationCount());

        tearDown();
    })
    ->in('Unit');
