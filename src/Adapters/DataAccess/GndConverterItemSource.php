<?php

declare( strict_types = 1 );

namespace DNB\GND\Adapters\DataAccess;

use DNB\GND\Domain\ItemSource;
use DNB\WikibaseConverter\PicaConverter;
use Traversable;
use Wikibase\DataModel\Entity\Item;

class GndConverterItemSource implements ItemSource {

	private Traversable $jsonStringIterator;
	private GndConverterItemBuilder $itemBuilder;

	public function __construct( Traversable $jsonStringIterator, GndConverterItemBuilder $itemBuilder ) {
		$this->jsonStringIterator = $jsonStringIterator;
		$this->itemBuilder = $itemBuilder;
	}

	public function nextItem(): ?Item {
		foreach ( $this->jsonStringIterator as $jsonString ) {
			// TODO
			return $this->itemBuilder->build( PicaConverter::newWithDefaultMapping()->picaJsonToWikibaseRecord( $jsonString ) );
		}

		return null;
	}

}
