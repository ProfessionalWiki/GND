<?php

declare( strict_types = 1 );

namespace DNB\GND\Tests\Integration\Adapters\DataAccess;

use DNB\GND\Adapters\DataAccess\DokuEntitySource;
use FileFetcher\SimpleFileFetcher;
use PHPUnit\Framework\TestCase;
use Wikibase\Repo\WikibaseRepo;

/**
 * @covers \DNB\GND\Adapters\DataAccess\DokuEntitySource
 */
class DokuEntitySourceTest extends TestCase {

	public function testCanGetEntitiesFromDokuWiki(): void {
		$entitySource = new DokuEntitySource(
			[ 'P61', 'Q150', 'P62' ],
			new SimpleFileFetcher(),
			WikibaseRepo::getDefaultInstance()->getBaseDataModelDeserializerFactory()->newEntityDeserializer()
		);

		$this->assertSame( 'P61', $entitySource->next()->getId()->getSerialization() );
		$this->assertSame( 'Q150', $entitySource->next()->getId()->getSerialization() );
		$this->assertSame( 'P62', $entitySource->next()->getId()->getSerialization() );
		$this->assertNull( $entitySource->next() );
	}

}
