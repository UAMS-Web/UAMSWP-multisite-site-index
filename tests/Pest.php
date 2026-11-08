<?php

/**
 * Pest configuration, for the Unit suite.
 */

declare(strict_types=1);

use Pest\TestSuite;
use PHPUnit\Framework\TestCase;

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
        // `$this` is untyped inside a `uses()` hook; the running test is the suite's current test case.
        $test = TestSuite::getInstance()->test;

        if ($test instanceof TestCase) {
            $test->addToAssertionCount(max(0, Mockery::getContainer()->mockery_getExpectationCount()));
        }

        tearDown();
    })
    ->in('Unit');
