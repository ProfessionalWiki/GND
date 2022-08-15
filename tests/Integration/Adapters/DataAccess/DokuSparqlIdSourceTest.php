<?php

declare( strict_types = 1 );

namespace DNB\GND\Tests\Integration\Adapters\DataAccess;

use DNB\GND\Adapters\DataAccess\DokuSparqlIdSource;
use DNB\GND\Adapters\DataAccess\MediaWikiFileFetcher;
use FileFetcher\StubFileFetcher;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DNB\GND\Adapters\DataAccess\DokuSparqlIdSource
 * @covers \DNB\GND\Adapters\DataAccess\MediaWikiFileFetcher
 */
class DokuSparqlIdSourceTest extends TestCase {

	public function testGetIds(): void {
		$idSource = new DokuSparqlIdSource(
			new StubFileFetcher( file_get_contents( __DIR__ . '/SparqlResult.xml' ) )
		);

		$ids = $idSource->getVocabularyIds();

		$this->assertSame( 'P101', $ids[0] );
		$this->assertSame( 'P103', $ids[1] );
		$this->assertSame( 'P104', $ids[2] );
		$this->assertSame( 'Q99', $ids[268] );
		$this->assertSame( 269, count( $ids ) );
	}

	public function testIntegrationWithLiveSystem(): void {
		$idSource = new DokuSparqlIdSource( new MediaWikiFileFetcher() );

		$ids = $idSource->getVocabularyIds();

		$this->assertSame( 'P100', $ids[0] );
	}

}
