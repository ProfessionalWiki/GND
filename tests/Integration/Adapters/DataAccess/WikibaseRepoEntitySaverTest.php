<?php

declare( strict_types = 1 );

namespace DNB\GND\Tests\Integration\Adapters\DataAccess;

use DNB\GND\Adapters\DataAccess\WikibaseRepoEntitySaver;
use MediaWikiIntegrationTestCase;
use User;
use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\Repo\WikibaseRepo;

/**
 * @covers \DNB\GND\Adapters\DataAccess\WikibaseRepoEntitySaver
 */
class WikibaseRepoEntitySaverTest extends MediaWikiIntegrationTestCase {

	public function testCanStoreNewItemWithId() {
		$item = new Item( new ItemId( 'Q1042' ) );

		$this->newItemStore()->storeEntity( $item );

		$this->assertEquals(
			$item,
			$this->getItemFromPersistence( $item->getId() )
		);
	}

	private function newItemStore(): WikibaseRepoEntitySaver {
		return new WikibaseRepoEntitySaver(
			WikibaseRepo::getDefaultInstance()->getEntityStore(),
			User::newSystemUser( 'WikibaseRepoItemStoreTest', [ 'steal' => true ] )
		);
	}

	private function getItemFromPersistence( ItemId $id ): ?EntityDocument {
		return WikibaseRepo::getDefaultInstance()->getEntityLookup()->getEntity( $id );
	}

	public function testMultipleStorageCallsForOneItem() {
		$item = new Item( new ItemId( 'Q1043' ) );

		$this->newItemStore()->storeEntity( $item );
		$this->newItemStore()->storeEntity( $item );

		$this->assertEquals(
			$item,
			$this->getItemFromPersistence( $item->getId() )
		);
	}

	public function testExceptionWhenItemHasNoId() {
		$this->expectException( \RuntimeException::class );
		$this->newItemStore()->storeEntity( new Item() );
	}

}
