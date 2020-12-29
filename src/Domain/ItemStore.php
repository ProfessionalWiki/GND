<?php

declare( strict_types = 1 );

namespace DNB\GND\Domain;

use Wikibase\DataModel\Entity\Item;

interface ItemStore {

	/**
	 * Creates or updates the provided Item.
	 * In case of creation, a newly generated ItemId gets assigned to the Item instance.
	 *
	 * TODO: failure cases
	 *
	 * @param Item $item
	 */
	public function storeItem( Item $item ): void;

}
