<?php

declare( strict_types = 1 );

namespace DNB\GND\Tests\Integration\Adapters\DataAccess;

use DNB\GND\Adapters\DataAccess\DokuSparqlIdSource;
use FileFetcher\StubFileFetcher;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DNB\GND\Adapters\DataAccess\DokuSparqlIdSource
 */
class DokuSparqlIdSourceTest extends TestCase {

	public function testFoo(): void {
		// https://query.wikidata.org/sparql?query=SELECT%20%3Fitem%20WHERE%20%7B%20%3Fitem%20wdt%3AP31%20wd%3AQ146%20%7D

		$idSource = new DokuSparqlIdSource(
			new StubFileFetcher( file_get_contents( __DIR__ . '/SparqlResult.xml' ) )
		);

		$this->assertSame(
			[ 'Q378619', 'Q498787', 'Q677525' ],
			$idSource->getVocabularyIds()
		);
	}

}
