# UAMSWP Multisite Site Index

Display an index of all sites on a multisite network with a widget or the `[site-index]` shortcode.

The [wiki](https://github.com/UAMS-Web/UAMSWP-multisite-site-index/wiki) holds the documentation: a walkthrough, the shortcode and widget reference, and troubleshooting.

## Requirements

- A WordPress multisite network, WordPress 6.8 or later.
- PHP 8.2 or later.

## Installation

1. Download the *Source code (zip)* of the latest release from [Releases](https://github.com/UAMS-Web/UAMSWP-multisite-site-index/releases).
2. In *My Sites*, *Network Admin*, *Plugins*, choose *Add New Plugin*, *Upload Plugin*, and upload the zip.
3. Network-activate the plugin.

Then add `[site-index]` to a page, as the wiki's [walkthrough](https://github.com/UAMS-Web/UAMSWP-multisite-site-index/wiki/Showing-a-site-index-on-a-page) shows.

## Running the tests

The suite is Pest 5 with Brain Monkey, so it needs no WordPress install and no database. It develops on PHP 8.4.27 (`config.platform.php` in `composer.json`); the dependencies are development-only and are not part of the deployed plugin.

```bash
composer install
composer test
```

## Credits

Based on Multisite Site Index by Nick Halsey.

Modified by UAMS Web Services.

Developers:

- Todd McKee
- Josh Daugherty
