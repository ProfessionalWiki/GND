<?php

declare( strict_types = 1 );

namespace DNB\GND\Tests\Integration\Adapters\DataAccess;

use DNB\GND\Adapters\DataAccess\WikibaseRepoItemStore;
use MediaWikiIntegrationTestCase;
use User;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\Repo\WikibaseRepo;

/**
 * @covers \DNB\GND\Adapters\DataAccess\WikibaseRepoItemStore
 */
class WikibaseRepoItemStoreTest extends MediaWikiIntegrationTestCase {

	public function testCanStoreNewItemWithId() {
		$item = new Item( new ItemId( 'Q1042' ) );

		$this->newItemStore()->storeItem( $item );

		$this->assertEquals(
			$item,
			$this->getItemFromPersistence( $item->getId() )
		);
	}

	private function newItemStore(): WikibaseRepoItemStore {
		return new WikibaseRepoItemStore(
			WikibaseRepo::getDefaultInstance()->getEntityStore(),
			User::newSystemUser( 'WikibaseRepoItemStoreTest', [ 'steal' => true ] )
		);
	}

	private function getItemFromPersistence( ItemId $id ): ?Item {
		return WikibaseRepo::getDefaultInstance()->getItemLookup()->getItemForId( $id );
	}

	public function testMultipleStorageCallsForOneItem() {
		$item = new Item( new ItemId( 'Q1043' ) );

		$this->newItemStore()->storeItem( $item );
		$this->newItemStore()->storeItem( $item );

		$this->assertEquals(
			$item,
			$this->getItemFromPersistence( $item->getId() )
		);
	}

	public function testExceptionWhenItemHasNoId() {
		$this->expectException( \RuntimeException::class );
		$this->newItemStore()->storeItem( new Item() );
	}

}
