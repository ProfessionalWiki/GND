<?php

declare( strict_types = 1 );

namespace DNB\GND\Tests\Integration\Adapters\DataAccess;

use DNB\GND\Adapters\DataAccess\WikibaseRepoItemStore;
use DNB\GND\Domain\ItemStore;
use MediaWikiIntegrationTestCase;
use User;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\Repo\WikibaseRepo;

/**
 * @covers \DNB\GND\Adapters\DataAccess\WikibaseRepoItemStore
 */
class WikibaseRepoItemStoreTest extends MediaWikiIntegrationTestCase {

	public function testStoreItem() {
		$item = new Item();

		$this->newItemStore()->storeItem( $item );

		$this->assertNotNull( $item->getId() );

		$this->assertEquals(
			$item,
			$this->getItemFromPersistence( $item->getId() )
		);
	}

	private function newItemStore(): WikibaseRepoItemStore {
		return new WikibaseRepoItemStore(
			WikibaseRepo::getDefaultInstance()->newEditEntityFactory()->newEditEntity(
				User::newSystemUser( 'WikibaseRepoItemStoreTest', [ 'steal' => true ] )
			)
		);
	}

	private function getItemFromPersistence( ItemId $id ): ?Item {
		return WikibaseRepo::getDefaultInstance()->getItemLookup()->getItemForId( $id );
	}

}
