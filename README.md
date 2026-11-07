# UAMSWP Multisite Site Index

Display an index of all sites on a multisite network with a widget or the `[site-index]` shortcode.

## Running the tests

The suite is Pest 5 with Brain Monkey, so it needs no WordPress install and no database. It develops on PHP 8.4.27 (`config.platform.php` in `composer.json`); the dependencies are development-only and are not part of the deployed plugin.

```bash
composer install
composer test
```
