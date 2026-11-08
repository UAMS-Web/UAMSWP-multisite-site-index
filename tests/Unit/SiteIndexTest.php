<?php

/**
 * The plugin's main file, with WordPress mocked: the shortcode, the widget and the markup it lists.
 *
 * Mutation notes (read with the survivors of `composer mutate`):
 * - The file-scope `add_shortcode()` and `add_action()` calls run once per process, in the first
 *   test's `beforeEach`. The first test below asserts what the loader recorded, so it must stay
 *   first: a mutant on those two lines is only covered by that test.
 * - Five mutants are equivalent and annotated in the plugin file with `@pest-mutate-ignore`, each
 *   with its reason beside it:
 *   - `UnwrapTrim` on `absint(trim($id))`: absint() is abs(intval()), and intval() ignores
 *     surrounding whitespace, so no input can tell `absint(' 3 ')` from `absint('3')`. The unit
 *     tests stub absint() the same way.
 *   - `ConcatEqualToEqual` on the first `$innerhtml .= '<li class="site">'`: `$innerhtml` is empty
 *     there on every pass (declared empty, reset at the end of each pass), so `.=` and `=` agree.
 *   - `EqualToIdentical` on each of the three comparisons that choose the sort (`$orderby == 'name'`,
 *     `$order == 'ASC'`, `$order == 'DESC'`): `==` and `===` differ only for a non-string value, and
 *     the shortcode attributes and widget settings that reach them are always strings.
 *
 * @see uamswp-multisite-site-index.php
 */

declare(strict_types=1);

use Brain\Monkey\Functions;

use function Brain\Monkey\Filters\expectApplied;
use function Brain\Monkey\Functions\stubEscapeFunctions;
use function Brain\Monkey\Functions\stubTranslationFunctions;
use function Brain\Monkey\Functions\when;

/**
 * Stubs a multisite network of the given sites and expects one site query with the given arguments.
 *
 * @param  array<int, array{name: string, tagline: string, url: string}>  $sites  keyed by site id, in query order
 * @param  array<string, mixed>  $queryArgs  the arguments the plugin must pass to get_sites()
 */
function stub_network(array $sites, array $queryArgs): void
{
    $current = null;

    when('is_multisite')->justReturn(true);

    Functions\expect('get_sites')->once()->with($queryArgs)->andReturn(array_keys($sites));

    Functions\expect('switch_to_blog')
        ->times(count($sites))
        ->andReturnUsing(function (int $id) use (&$current): bool {
            $current = $id;

            return true;
        });

    Functions\expect('restore_current_blog')
        ->times(count($sites))
        ->andReturnUsing(function () use (&$current): bool {
            $current = null;

            return true;
        });

    // By-reference closures: an arrow function would copy $current when it is created, and see null.
    when('home_url')->alias(function () use (&$current, $sites): string {
        return $sites[(int) $current]['url'];
    });
    when('get_bloginfo')->alias(function (string $show) use (&$current, $sites): string {
        return $show === 'name' ? $sites[(int) $current]['name'] : $sites[(int) $current]['tagline'];
    });
}

/**
 * The query the plugin sends when no option is changed.
 *
 * @return array<string, mixed>
 */
function default_query(): array
{
    return [
        'site__not_in' => [0],
        'orderby' => 'id',
        'order' => 'ASC',
        'fields' => 'ids',
        'number' => 100,
        'deleted' => 0,
    ];
}

/**
 * The markup of one listed site, as the plugin builds it.
 */
function site_item(string $url, string $title, ?string $tagline): string
{
    return '<li class="site"><a href="'.$url.'" target="_top"><strong class="site-index-site-title">'.$title.'</strong></a>'
        .($tagline === null ? '' : '<br><em class="site-index-site-tagline">'.$tagline.'</em>')
        .'</li>';
}

/**
 * WordPress' selected() and checked(): the attribute when the two values match, echoed when asked to and returned either way.
 */
function html_attribute(string $name, mixed $value, mixed $current, bool $echo): string
{
    $html = (string) $value === (string) $current ? " {$name}='{$name}'" : '';

    if ($echo) {
        echo $html;
    }

    return $html;
}

/**
 * Renders the widget form for an instance, with the field helpers WordPress provides stubbed.
 *
 * @param  array<string, mixed>  $instance
 */
function render_form(array $instance, int $siteCount): string
{
    Functions\expect('get_sites')->once()->with(['count' => true])->andReturn($siteCount);

    when('selected')->alias(fn (mixed $value, mixed $current = true, bool $echo = true): string => html_attribute('selected', $value, $current, $echo));
    when('checked')->alias(fn (mixed $value, mixed $current = true, bool $echo = true): string => html_attribute('checked', $value, $current, $echo));

    $widget = new UAMS_Multisite_Site_Index_Widget;

    ob_start();
    $widget->form($instance);

    return (string) ob_get_clean();
}

beforeEach(function (): void {
    stubEscapeFunctions();
    stubTranslationFunctions();
    when('absint')->alias(fn ($value): int => abs((int) $value));

    require_once __DIR__.'/../Support/load-plugin.php';
});

// Must stay the first test: see the file docblock.
it('registers the shortcode and the widgets_init hook when the file loads', function (): void {
    expect($GLOBALS['uams_site_index_registrations'])->toBe([
        'shortcode' => ['site-index', 'uams_multisite_site_index_do_shortcode'],
        'widgets_init' => 10,
    ]);
});

describe('the shortcode', function (): void {
    it('renders nothing when the network is not a multisite', function (): void {
        Functions\expect('is_multisite')->once()->andReturn(false);

        expect(uams_multisite_site_index_do_shortcode([]))->toBeNull();
    });

    it('passes its defaults to the markup', function (): void {
        when('is_multisite')->justReturn(true);
        when('shortcode_atts')->alias(fn (array $pairs, mixed $atts): array => array_merge($pairs, (array) $atts));
        Functions\expect('uams_multisite_site_index_get_markup')
            ->once()
            ->with('', 0, 100, true, 'ASC', 'id')
            ->andReturn('<ul>defaults</ul>');

        expect(uams_multisite_site_index_do_shortcode([]))->toBe('<ul>defaults</ul>');
    });

    it('passes each attribute to the markup in order', function (): void {
        when('is_multisite')->justReturn(true);
        when('shortcode_atts')->alias(fn (array $pairs, mixed $atts): array => array_merge($pairs, (array) $atts));
        Functions\expect('uams_multisite_site_index_get_markup')
            ->once()
            ->with('2,3', 1, 5, false, 'DESC', 'name')
            ->andReturn('<ul>custom</ul>');

        expect(uams_multisite_site_index_do_shortcode([
            'excluded' => '2,3',
            'number' => 5,
            'recent' => 1,
            'description' => false,
            'order' => 'DESC',
            'orderby' => 'name',
        ]))->toBe('<ul>custom</ul>');
    });
});

describe('the widget registration', function (): void {
    it('registers nothing when the network is not a multisite', function (): void {
        Functions\expect('is_multisite')->once()->andReturn(false);
        Functions\expect('register_widget')->never();

        uams_multisite_site_index_widget_init();
    });

    it('registers the widget class on a multisite', function (): void {
        Functions\expect('is_multisite')->once()->andReturn(true);
        Functions\expect('register_widget')->once()->with('UAMS_Multisite_Site_Index_Widget');

        uams_multisite_site_index_widget_init();
    });
});

describe('the widget', function (): void {
    it('registers under its id, name and options', function (): void {
        $widget = new UAMS_Multisite_Site_Index_Widget;

        expect($widget->id_base)->toBe('UAMS Multisite_Site_Index_Widget')
            ->and($widget->name)->toBe('Site Index')
            ->and($widget->widget_options)->toBe([
                'customize_selective_refresh' => true,
                'description' => 'Displays an index of sites on this multisite network',
            ]);
    });

    it('outputs the markup with the defaults when the instance is empty', function (): void {
        expectApplied('widget_title')->once()->with('')->andReturn('');
        Functions\expect('uams_multisite_site_index_get_markup')
            ->once()
            ->with('', 0, 100, true, 'ASC', 'id')
            ->andReturn('<ul>index</ul>');

        ob_start();
        (new UAMS_Multisite_Site_Index_Widget)->widget(
            ['before_widget' => '<aside>', 'after_widget' => '</aside>', 'before_title' => '<h2>', 'after_title' => '</h2>'],
            []
        );

        expect(ob_get_clean())->toBe('<aside><ul>index</ul></aside>');
    });

    it('outputs the filtered title and passes every instance setting to the markup', function (): void {
        expectApplied('widget_title')->once()->with('Sites')->andReturn('Filtered sites');
        Functions\expect('uams_multisite_site_index_get_markup')
            ->once()
            ->with('4', 0, 7, false, 'DESC', 'name')
            ->andReturn('<ul>index</ul>');

        ob_start();
        (new UAMS_Multisite_Site_Index_Widget)->widget(
            ['before_widget' => '<aside>', 'after_widget' => '</aside>', 'before_title' => '<h2>', 'after_title' => '</h2>'],
            ['title' => 'Sites', 'excluded' => '4', 'number' => 7, 'order' => 'DESC', 'orderby' => 'name', 'description' => false]
        );

        expect(ob_get_clean())->toBe('<aside><h2>Filtered sites</h2><ul>index</ul></aside>');
    });

    it('saves sanitized settings and keeps the ones it does not own', function (): void {
        $saved = (new UAMS_Multisite_Site_Index_Widget)->update(
            [
                'title' => '<b>Sites</b>',
                'number' => '12abc',
                'excluded' => '<i>1,2</i>',
                'order' => '<u>DESC</u>',
                'orderby' => '<u>name</u>',
                'description' => '1',
            ],
            ['kept' => 'yes', 'title' => 'old']
        );

        expect($saved)->toBe([
            'kept' => 'yes',
            'title' => 'Sites',
            'number' => 12,
            'excluded' => '1,2',
            'order' => 'DESC',
            'orderby' => 'name',
            'description' => true,
        ]);
    });

    it('saves an empty description checkbox as false', function (): void {
        $saved = (new UAMS_Multisite_Site_Index_Widget)->update(
            ['title' => '', 'number' => '', 'excluded' => '', 'order' => '', 'orderby' => '', 'description' => ''],
            []
        );

        expect($saved['description'])->toBeFalse();
    });

    it('renders the form with the saved settings', function (): void {
        $html = render_form(
            ['title' => 'Sites', 'number' => 25, 'excluded' => '4,5', 'order' => 'DESC', 'orderby' => 'name', 'description' => false],
            150
        );

        expect($html)
            ->toContain('id="widget-site-index-title" name="widget-site-index[title]" type="text" value="Sites"')
            ->toContain('id="widget-site-index-number" name="widget-site-index[number]" type="number" value="25"')
            ->toContain('id="widget-site-index-excluded" name="widget-site-index[excluded]" type="text" value="4,5"')
            ->toContain('<option value="DESC" selected=\'selected\'>')
            ->toContain('<option value="ASC">')
            ->toContain('<option value="name" selected=\'selected\'>')
            ->toContain('<option value="id">')
            ->toContain('<option value="last_updated">')
            ->toContain('ASC (A - Z)')
            ->toContain('DESC (Z - A)')
            ->toContain('Site ID')
            ->toContain('Site Title')
            ->toContain('Last Updated')
            ->toContain('Site IDs, separated by commas.')
            ->toContain('Remove 1 to include the primary site.')
            ->toContain('Show site description');

        expect($html)->not->toContain("checked='checked'");
    });

    it('renders the form defaults when nothing is saved', function (): void {
        $html = render_form([], 150);

        expect($html)
            ->toContain('type="text" value="" class="widefat"')
            ->toContain('type="number" value="100"')
            ->toContain('id="widget-site-index-excluded" name="widget-site-index[excluded]" type="text" value="1"')
            ->toContain('<option value="ASC" selected=\'selected\'>')
            ->toContain('<option value="id" selected=\'selected\'>')
            ->toContain("checked='checked'");
    });

    it('shows the number field only for a network of more than 100 sites', function (): void {
        expect(render_form([], 100))->not->toContain('widget-site-index[number]');
    });

    it('selects the last updated sort when that is saved', function (): void {
        $html = render_form(['title' => '', 'number' => 100, 'excluded' => '', 'order' => 'ASC', 'orderby' => 'last_updated', 'description' => true], 10);

        expect($html)
            ->toContain("<option value=\"last_updated\" selected='selected'>")
            ->toContain('<option value="id">')
            ->toContain('<option value="name">');
    });

    it('shows the number field for 101 sites', function (): void {
        expect(render_form([], 101))->toContain('widget-site-index[number]');
    });
});

describe('the markup', function (): void {
    it('returns an empty string when the network is not a multisite', function (): void {
        Functions\expect('is_multisite')->once()->andReturn(false);
        Functions\expect('get_sites')->never();

        expect(uams_multisite_site_index_get_markup())->toBe('');
    });

    it('returns an empty string when no sites match', function (): void {
        when('is_multisite')->justReturn(true);
        Functions\expect('get_sites')->once()->with(default_query())->andReturn([]);

        expect(uams_multisite_site_index_get_markup())->toBe('');
    });

    it('queries with the excluded ids, count, order and field it was given', function (): void {
        when('is_multisite')->justReturn(true);
        Functions\expect('get_sites')
            ->once()
            ->with([
                'site__not_in' => [1, 3, 0],
                'orderby' => 'name',
                'order' => 'DESC',
                'fields' => 'ids',
                'number' => 5,
                'deleted' => 0,
            ])
            ->andReturn([]);

        expect(uams_multisite_site_index_get_markup('1, 3,x', 0, 5, true, 'DESC', 'name'))->toBe('');
    });

    it('lists each site as an escaped linked title with its tagline', function (): void {
        stub_network(
            [
                2 => ['name' => 'Site <Two> & more', 'tagline' => 'The second site', 'url' => 'https://example.org/two/'],
                3 => ['name' => 'Site Three', 'tagline' => 'The third site', 'url' => 'https://example.org/three/'],
            ],
            default_query()
        );

        expect(uams_multisite_site_index_get_markup())->toBe(
            '<ul class="site-index id">'
            .site_item('https://example.org/two/', 'Site &lt;Two&gt; &amp; more', 'The second site')
            .site_item('https://example.org/three/', 'Site Three', 'The third site')
            .'</ul>'
        );
    });

    it('leaves the tagline out when the description is off', function (int|string|bool $description): void {
        stub_network(
            [2 => ['name' => 'Site Two', 'tagline' => 'The second site', 'url' => 'https://example.org/two/']],
            default_query()
        );

        expect(uams_multisite_site_index_get_markup('', 0, 100, $description))->toBe(
            '<ul class="site-index id">'.site_item('https://example.org/two/', 'Site Two', null).'</ul>'
        );
    })->with([[false], [0], ['0'], ['']]);

    it('orders the listed sites by title or leaves them in query order', function (string $orderby, string $order, array $expected): void {
        stub_network(
            [
                1 => ['name' => 'Charlie', 'tagline' => '', 'url' => 'https://example.org/c/'],
                2 => ['name' => 'alpha', 'tagline' => '', 'url' => 'https://example.org/a/'],
                3 => ['name' => 'Bravo', 'tagline' => '', 'url' => 'https://example.org/b/'],
            ],
            ['orderby' => $orderby, 'order' => $order] + default_query()
        );

        $titles = [];
        preg_match_all('#site-index-site-title">([^<]*)<#', uams_multisite_site_index_get_markup('', 0, 100, true, $order, $orderby), $titles);

        expect($titles[1])->toBe($expected);
    })->with([
        'title ascending' => ['name', 'ASC', ['Bravo', 'Charlie', 'alpha']],
        'title descending' => ['name', 'DESC', ['alpha', 'Charlie', 'Bravo']],
        'id ascending keeps query order' => ['id', 'ASC', ['Charlie', 'alpha', 'Bravo']],
        'id descending keeps query order' => ['id', 'DESC', ['Charlie', 'alpha', 'Bravo']],
        'last updated keeps query order' => ['last_updated', 'ASC', ['Charlie', 'alpha', 'Bravo']],
        'title with another order keeps query order' => ['name', 'RAND', ['Charlie', 'alpha', 'Bravo']],
    ]);

    it('names the sort field in the list class', function (): void {
        stub_network(
            [2 => ['name' => 'Site Two', 'tagline' => '', 'url' => 'https://example.org/two/']],
            ['orderby' => 'last_updated'] + default_query()
        );

        expect(uams_multisite_site_index_get_markup('', 0, 100, true, 'ASC', 'last_updated'))->toStartWith('<ul class="site-index last_updated">');
    });
});
