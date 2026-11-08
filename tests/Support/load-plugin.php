<?php

/**
 * Loads the plugin's main file once, with the WordPress pieces it needs at file scope stubbed.
 *
 * The main file calls add_shortcode() and add_action() while loading and declares a class that
 * extends WP_Widget. Call this from a test, after Brain Monkey's setUp() has run, so add_action()
 * exists. The two registrations happen once per process, so the loader records them in
 * `$GLOBALS['uams_site_index_registrations']` for a test to assert.
 */

declare(strict_types=1);

use function Brain\Monkey\Functions\when;

if (! class_exists('WP_Widget')) {
    /**
     * Stand-in for WordPress' base widget class: it records what the widget passes to the parent
     * constructor and derives field ids and names the way WordPress does.
     */
    class WP_Widget
    {
        /**
         * @param  array<string, mixed>  $widget_options
         */
        public function __construct(public string $id_base, public string $name, public array $widget_options = []) {}

        public function get_field_id(string $field_name): string
        {
            return 'widget-site-index-'.$field_name;
        }

        public function get_field_name(string $field_name): string
        {
            return 'widget-site-index['.$field_name.']';
        }
    }
}

if (! function_exists('uams_multisite_site_index_get_markup')) {
    $GLOBALS['uams_site_index_registrations'] = [];

    when('add_shortcode')->alias(function (string $tag, string $callback): bool {
        $GLOBALS['uams_site_index_registrations']['shortcode'] = [$tag, $callback];

        return true;
    });

    require_once __DIR__.'/../../uamswp-multisite-site-index.php';

    $GLOBALS['uams_site_index_registrations']['widgets_init'] = has_action(
        'widgets_init',
        'uams_multisite_site_index_widget_init'
    );
}
