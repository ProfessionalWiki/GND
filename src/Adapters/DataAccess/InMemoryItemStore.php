<?php

declare( strict_types = 1 );

namespace DNB\GND\Adapters\DataAccess;

use DNB\GND\Domain\ItemStore;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;

class InMemoryItemStore implements ItemStore {

	private array $items = [];
	private int $nextId = 1000;

	public function __construct( Item ...$items ) {
		foreach ( $items as $item ) {
			$this->storeItem( $item );
		}
	}

	public function storeItem( Item $item ): void {
		if ( $item->getId() === null ) {
			$item->setId( ItemId::newFromNumber( $this->nextId++ ) );
		}

		$this->items[$item->getId()->getSerialization()] = $item;
	}

	/**
	 * @return Item[]
	 */
	public function getItems(): array {
		return array_values( $this->items );
	}

}
