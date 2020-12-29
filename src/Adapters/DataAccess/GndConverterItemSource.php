<?php

declare( strict_types = 1 );

namespace DNB\GND\Adapters\DataAccess;

use DNB\GND\Domain\ItemSource;
use DNB\WikibaseConverter\Converter;
use DNB\WikibaseConverter\PicaRecord;
use DNB\WikibaseConverter\WikibaseRecord;
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
			return $this->itemBuilder->build( $this->jsonStringToWikibaseRecord( $jsonString ) );
		}

		return null;
	}

	private function jsonStringToWikibaseRecord( string $json ): WikibaseRecord {
		$converter = Converter::fromArrayMapping( [] ); // TODO

		return $converter->picaToWikibase(
			new PicaRecord( json_decode( $json, true ) )
		);
	}

}
