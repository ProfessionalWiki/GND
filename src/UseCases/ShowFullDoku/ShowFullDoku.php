<?php

declare( strict_types = 1 );

namespace DNB\GND\ShowFullDoku;

use DNB\GND\Domain\PropertyCollectionLookup;
use Wikibase\DataModel\Services\Lookup\ItemLookup;

class ShowFullDoku {

	private FullDokuPresenter $presenter;
	private PropertyCollectionLookup $properties;
	private ItemLookup $itemLookup;

	public function __construct( FullDokuPresenter $presenter, PropertyCollectionLookup $properties, ItemLookup $itemLookup ) {
		$this->presenter = $presenter;
		$this->properties = $properties;
		$this->itemLookup = $itemLookup;
	}

	public function showFullDoku(): void {

	}

}
