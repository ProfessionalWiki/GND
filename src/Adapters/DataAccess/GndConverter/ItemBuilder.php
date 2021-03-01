<?php

declare( strict_types = 1 );

namespace DNB\GND\Adapters\DataAccess\GndConverter;

use DataValues\StringValue;
use DNB\WikibaseConverter\GndItem;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Statement\StatementList;

/**
 * Builds a Wikibase Item (using the Wikibase DataModel classes) from
 * an item representation coming from the GND Wikibase Converter library.
 */
class ItemBuilder {

	private ValueBuilder $valueBuilder;

	public function __construct( ValueBuilder $valueBuilder ) {
		$this->valueBuilder = $valueBuilder;
	}

	public function build( GndItem $record ): Item {
		$statements = new StatementList();
		$itemId = null;

		foreach ( $record->getPropertyIds() as $id ) {
			foreach ( $record->getStatementsForProperty( $id ) as $gndStatement ) {
				// TODO: change when we know what to do item-ID wise
				if ( $id === 'P2' ) {
					$itemId = ItemId::newFromNumber( (int)preg_replace('/[^0-9]/', '', $gndStatement->getValue() ) );
				}

				$statements->addNewStatement(
					new PropertyValueSnak(
						new PropertyId( $id ),
						$this->valueBuilder->stringToDataValue( $gndStatement->getValue(), 'todo' )
					)
				);
			}
		}

		return new Item( $itemId, null, null, $statements );
	}

}
