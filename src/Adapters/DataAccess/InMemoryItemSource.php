<?php

declare( strict_types = 1 );

namespace DNB\GND\Adapters\DataAccess;

use DNB\GND\Domain\ItemSource;
use Wikibase\DataModel\Entity\Item;

class InMemoryItemSource implements ItemSource {

	private array $items;

	public function __construct( Item ...$items ) {
		$this->items = $items;
	}

	public function next(): ?Item {
		return array_shift( $this->items );
	}

}
