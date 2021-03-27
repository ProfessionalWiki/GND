<?php

declare( strict_types = 1 );

namespace DNB\GND\Adapters\DataAccess;

use DNB\GND\Adapters\DataAccess\GndConverter\ItemBuilder;
use DNB\GND\Domain\ItemSource;
use DNB\WikibaseConverter\GndItem;
use DNB\WikibaseConverter\InvalidPica;
use DNB\WikibaseConverter\PicaConverter;
use Iterator;
use Wikibase\DataModel\Entity\Item;

class GndConverterItemSource implements ItemSource {

	private Iterator $jsonStringIterator;
	private ItemBuilder $itemBuilder;
	private ?PicaConverter $picaConverter = null;

	public function __construct( Iterator $jsonStringIterator, ItemBuilder $itemBuilder ) {
		$this->jsonStringIterator = $jsonStringIterator;
		$this->itemBuilder = $itemBuilder;
	}

	public function next(): ?Item {
		while ( true ) {
			$line = $this->jsonStringIterator->current();

			if ( $line === null ) {
				return null;
			}

			$this->jsonStringIterator->next();

			try {
				$gndItem = $this->picaJsonToGndItem( $line );
			} catch ( InvalidPica $ex ) {
				continue;
			}

			return $this->itemBuilder->build( $gndItem );
		}
	}

	/**
	 * @throws InvalidPica
	 */
	private function picaJsonToGndItem( string $json ): GndItem {
		if ( $this->picaConverter === null ) {
			$this->picaConverter = PicaConverter::newWithDefaultMapping();
		}

		return $this->picaConverter->picaJsonToGndItem( $json );
	}

}
