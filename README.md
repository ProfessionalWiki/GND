# GND MediaWiki extension

[![GitHub Workflow Status](https://img.shields.io/github/workflow/status/ProfessionalWiki/GND/CI/master)](https://github.com/ProfessionalWiki/GND/actions?query=workflow%3ACI)
[![Latest Stable Version](https://poser.pugx.org/dnb/mediawiki-gnd/version.png)](https://packagist.org/packages/dnb/mediawiki-gnd)
[![Download count](https://poser.pugx.org/dnb/mediawiki-gnd/d/total.png)](https://packagist.org/packages/dnb/mediawiki-gnd)

[MediaWiki] extension that adds scripts to import GND PICA+ into [Wikibase].

The GND extension was created by [Professional.Wiki] for the [German National Library]. [Professional.Wiki] provides commercial [Wikibase hosting], [MediaWiki development] and support.

## Platform requirements

* [PHP] 7.4 or later, including PHP 8
* [MediaWiki] 1.35
* [Wikibase Repository] REL1_35

See the [release notes](#release-notes) for more information on the different versions of this extension.

## Installation

First install MediaWiki and Wikibase Repository.

The recommended way to install the GND extension is using [Composer] with
[MediaWiki's built-in support for Composer][Composer install].

On the commandline, go to your wikis root directory. Then run these two commands:

```shell script
COMPOSER=composer.local.json composer require --no-update dnb/mediawiki-gnd:*
composer update dnb/mediawiki-gnd --no-dev -o
```

**Enabling the extension**

Then enable the extension by adding the following to the bottom of your wikis `LocalSettings.php` file:

```php
wfLoadExtension( 'GND' );
```

You can verify the extension was enabled successfully by opening your wikis Special:Version page in your browser.

## Usage

GND import via `ImportGndDump.php`. Example:

    php extensions/GND/maintenance/ImportGndDump.php --path extensions/GND/data/GND.json --limit 10

Doku-wiki vocabulary sync via `SyncDokuVocabulary`. Example:

    php extensions/GND/maintenance/SyncDokuVocabulary.php

## Running the tests

* PHP tests: `php tests/phpunit/phpunit.php -c extensions/GND/`

## Release notes

### Version 0.1.0

Under development

* Initial release for MediaWiki/Wikibase 1.35

[Professional.Wiki]: https://professional.wiki
[Wikibase]: https://wikibase.consulting/what-is-wikibase/
[MediaWiki]: https://www.mediawiki.org
[PHP]: https://www.php.net
[Wikibase Repository]: https://www.mediawiki.org/wiki/Extension:Wikibase_Repository
[Composer]: https://getcomposer.org
[Composer install]: https://professional.wiki/en/articles/installing-mediawiki-extensions-with-composer
[MediaWiki development]: https://professional.wiki/en/mediawiki-development
[Wikibase hosting]: https://professional.wiki/en/hosting/wikibase
[German National Library]: https://www.dnb.de/EN/Home/home_node.html
