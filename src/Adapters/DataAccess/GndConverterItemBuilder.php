<?php

declare( strict_types = 1 );

namespace DNB\GND\Adapters\DataAccess;

use DataValues\StringValue;
use DNB\WikibaseConverter\WikibaseRecord;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Statement\StatementList;

/**
 * Builds a Wikibase Item (using the Wikibase DataModel classes) from
 * an item representation coming from the GND Wikibase Converter library.
 */
class GndConverterItemBuilder {

	public function __construct() {
		// TODO: inject value builder
	}

	public function build( WikibaseRecord $record ): Item {
		$statements = new StatementList();
		$itemId = null;

		foreach ( $record->getPropertyIds() as $id ) {
			foreach ( $record->getValuesForProperty( $id ) as $value ) {
				// TODO: change when we know what to do item-ID wise
				if ( $id === 'P2' ) {
					$itemId = ItemId::newFromNumber( (int)preg_replace('/[^0-9]/', '', $value ) );
				}

				$statements->addNewStatement(
					new PropertyValueSnak(
						new PropertyId( $id ),
						new StringValue( $value ) // TODO: handle types
					)
				);
			}
		}

		return new Item( $itemId, null, null, $statements );
	}

}
