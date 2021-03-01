<?php

declare( strict_types = 1 );

namespace DNB\GND\Adapters\DataAccess;

use DNB\GND\Domain\ItemSource;
use DNB\WikibaseConverter\InvalidPica;
use DNB\WikibaseConverter\PicaConverter;
use Iterator;
use Wikibase\DataModel\Entity\Item;

class GndConverterItemSource implements ItemSource {

	private Iterator $jsonStringIterator;
	private GndConverterItemBuilder $itemBuilder;

	public function __construct( Iterator $jsonStringIterator, GndConverterItemBuilder $itemBuilder ) {
		$this->jsonStringIterator = $jsonStringIterator;
		$this->itemBuilder = $itemBuilder;
	}

	public function nextItem(): ?Item {
		while ( true ) {
			$line = $this->jsonStringIterator->current();

			if ( $line === null ) {
				return null;
			}

			$this->jsonStringIterator->next();

			try {
				// TODO: do not re-create
				$gndItem = PicaConverter::newWithDefaultMapping()->picaJsonToGndItem( $line );
			} catch ( InvalidPica $ex ) {
				continue;
			}

			//if ( $gndItem->getPropertyIds() !== [] ) {
			return $this->itemBuilder->build( $gndItem );
			//}
		}
	}

}
