<?php

declare( strict_types = 1 );

namespace DNB\GND\Adapters\DataAccess;

use DNB\GND\Domain\ItemSource;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Services\EntityId\EntityIdPager;
use Wikibase\DataModel\Services\Lookup\ItemLookup;

class FullLocalItemSource implements ItemSource {

	private EntityIdPager $entityIdPager;
	private ItemLookup $itemLookup;

	public function __construct( EntityIdPager $entityIdPager, ItemLookup $itemLookup ) {
		$this->entityIdPager = $entityIdPager;
		$this->itemLookup = $itemLookup;
	}

	public function next(): ?Item {
		$ids = $this->entityIdPager->fetchIds( 1 );

		if ( $ids === [] ) {
			return null;
		}

		if ( !( $ids[0] instanceof ItemId ) ) {
			throw new \RuntimeException( 'Got ID of wrong entity type, thanks generic interface' );
		}

		return $this->itemLookup->getItemForId( $ids[0] );
	}

}
