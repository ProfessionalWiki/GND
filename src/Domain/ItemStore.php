<?php

declare( strict_types = 1 );

namespace DNB\GND\Domain;

use Wikibase\DataModel\Entity\Item;

interface ItemStore {

	public function storeItem( Item $item );

}
