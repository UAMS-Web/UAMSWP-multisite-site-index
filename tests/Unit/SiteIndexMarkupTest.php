<?php

/**
 * uams_multisite_site_index_get_markup(), with WordPress mocked.
 *
 * @see uamswp-multisite-site-index.php
 */

declare(strict_types=1);

use Brain\Monkey\Functions;

use function Brain\Monkey\Functions\stubEscapeFunctions;
use function Brain\Monkey\Functions\when;

beforeEach(function (): void {
    stubEscapeFunctions();
    when('absint')->alias(fn ($value): int => abs((int) $value));

    require_once __DIR__.'/../Support/load-plugin.php';
});

it('returns an empty string when the network is not a multisite', function (): void {
    Functions\expect('is_multisite')->once()->andReturn(false);
    Functions\expect('get_sites')->never();

    expect(uams_multisite_site_index_get_markup())->toBe('');
});

it('returns an empty string when no sites match', function (): void {
    when('is_multisite')->justReturn(true);
    when('get_sites')->justReturn([]);

    expect(uams_multisite_site_index_get_markup())->toBe('');
});

it('lists each site as a linked title with its tagline', function (): void {
    when('is_multisite')->justReturn(true);
    when('get_sites')->justReturn([2]);
    when('switch_to_blog')->justReturn(true);
    when('restore_current_blog')->justReturn(true);
    when('home_url')->justReturn('https://example.org/site-two/');
    when('get_bloginfo')->alias(fn (string $show): string => $show === 'name' ? 'Site Two' : 'The second site');

    expect(uams_multisite_site_index_get_markup())->toBe(
        '<ul class="site-index id"><li class="site">'
        .'<a href="https://example.org/site-two/" target="_top"><strong class="site-index-site-title">Site Two</strong></a>'
        .'<br><em class="site-index-site-tagline">The second site</em></li></ul>'
    );
});

// scratch: tests-only change for the mutate job
