<?php

declare( strict_types = 1 );

namespace DNB\GND\Application\Adapters\DataAccess;

use DataValues\StringValue;
use DNB\WikibaseConverter\WikibaseRecord;
use Wikibase\DataModel\Entity\Item;
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

		foreach ( $record->getPropertyIds() as $id ) {
			foreach ( $record->getValuesForProperty( $id ) as $value ) {
				$statements->addNewStatement(
					new PropertyValueSnak(
						new PropertyId( $id ),
						new StringValue( $value ) // TODO: handle types
					)
				);
			}
		}

		return new Item( null, null, null, $statements );
	}

}
