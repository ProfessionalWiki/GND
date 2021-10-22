<?php

declare( strict_types = 1 );

namespace DNB\GND\Tests\Integration\Adapters\DataAccess;

use DNB\GND\GndServicesFactory;
use MediaWikiIntegrationTestCase;
use User;
use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Term\Fingerprint;
use Wikibase\DataModel\Term\Term;
use Wikibase\DataModel\Term\TermList;

/**
 * @group Database
 *
 * @covers \DNB\GND\Adapters\DataAccess\FullLocalItemSource
 * @covers \DNB\GND\GndServicesFactory
 */
class FullLocalItemSourceTest extends MediaWikiIntegrationTestCase {

	public function testRetrievesAllItemsSavedInWikibaseRepo(): void {
		$item1 = new Item(
			new ItemId( 'Q133713371' ),
			new Fingerprint( new TermList( [ new Term( 'en', 'TestItem1' ) ] ) ),
		);
		$item2 = new Item(
			new ItemId( 'Q133713372' ),
			new Fingerprint( new TermList( [ new Term( 'en', 'TestItem2' ) ] ) ),
		);

		$this->saveEntity( $item1 );
		$this->saveEntity( $item2 );

		$itemSource = GndServicesFactory::getInstance()->newFullLocalItemSource();

		$this->assertEquals( $item1, $itemSource->next() );
		$this->assertEquals( $item2, $itemSource->next() );
		$this->assertNull( $itemSource->next() );
	}

	private function saveEntity( EntityDocument $entity ): void {
		GndServicesFactory::getInstance()->newEntitySaver(
			User::newSystemUser( 'FullLocalItemSourceTest', [ 'steal' => true ] )
		)->storeEntity( $entity );
	}

}
