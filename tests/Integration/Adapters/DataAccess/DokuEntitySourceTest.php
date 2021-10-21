<?php

declare( strict_types = 1 );

namespace DNB\GND\Tests\Integration\Adapters\DataAccess;

use DNB\GND\Adapters\DataAccess\DokuEntitySource;
use DNB\GND\Adapters\DataAccess\MediaWikiFileFetcher;
use PHPUnit\Framework\TestCase;
use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\DataModel\Statement\StatementListProvider;
use Wikibase\Repo\WikibaseRepo;

/**
 * @covers \DNB\GND\Adapters\DataAccess\DokuEntitySource
 * @covers \DNB\GND\Adapters\DataAccess\MediaWikiFileFetcher
 */
class DokuEntitySourceTest extends TestCase {

	public function testCanGetEntitiesFromDokuWiki(): void {
		$entitySource = new DokuEntitySource(
			[ 'P61', 'Q1' ],
			new MediaWikiFileFetcher(),
			WikibaseRepo::getDefaultInstance()->getBaseDataModelDeserializerFactory()->newEntityDeserializer()
		);

		$firstEntity = $entitySource->next();
		$secondEntity = $entitySource->next();

		$this->assertSame( 'P61', $firstEntity->getId()->getSerialization() );
		$this->assertSame( 'Q1', $secondEntity->getId()->getSerialization() );
		$this->assertNull( $entitySource->next() );

		$this->assertNoStatements( $firstEntity );
		$this->assertNoStatements( $secondEntity );
	}

	private function assertNoStatements( EntityDocument $entity ): void {
		if ( $entity instanceof StatementListProvider ) {
			$this->assertTrue( $entity->getStatements()->isEmpty() );
		}
		else {
			$this->fail();
		}
	}

}
