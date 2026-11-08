<?php

/**
 * Loads the plugin's main file once, with the WordPress pieces it needs at file scope stubbed.
 *
 * The main file calls add_shortcode() and add_action() while loading and declares a class that
 * extends WP_Widget. Call this from a test, after Brain Monkey's setUp() has run, so add_action()
 * exists.
 */

declare(strict_types=1);

use function Brain\Monkey\Functions\when;

if (! class_exists('WP_Widget')) {
    // Stand-in for WordPress' base widget class: the Unit suite never instantiates the widget.
    class WP_Widget {}
}

if (! function_exists('uams_multisite_site_index_get_markup')) {
    when('add_shortcode')->justReturn(true);

    require_once __DIR__.'/../../uamswp-multisite-site-index.php';
}
