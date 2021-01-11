<?php

declare( strict_types = 1 );

namespace DNB\GND\Adapters\DataAccess;

use DNB\GND\Domain\ItemStore;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;

class InMemoryItemStore implements ItemStore {

	private array $items = [];
	private array $idsToThrowOn = [];
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
		else if ( in_array( $item->getId()->getSerialization(), $this->idsToThrowOn ) ) {
			throw new \RuntimeException( $item->getId()->getSerialization() );
		}

		$this->items[$item->getId()->getSerialization()] = $item;
	}

	/**
	 * @return Item[]
	 */
	public function getItems(): array {
		return array_values( $this->items );
	}

	public function throwOnId( string $itemId ): void {
		$this->idsToThrowOn[] = $itemId;
	}

}
