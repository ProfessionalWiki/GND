<?php

declare( strict_types = 1 );

namespace DNB\GND\Adapters\DataAccess\GndConverter;

use DNB\WikibaseConverter\GndItem;
use RuntimeException;
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

	private const GND_ID = 'P150';

	private ValueBuilder $valueBuilder;

	public function __construct( ValueBuilder $valueBuilder ) {
		$this->valueBuilder = $valueBuilder;
	}

	public function build( GndItem $gndItem ): Item {
		return new Item(
			$this->itemIdFromGndItem( $gndItem ),
			null,
			null,
			$this->statementsFromGndItem( $gndItem )
		);
	}

	private function itemIdFromGndItem( GndItem $gndItem ): ItemId {
		foreach ( $gndItem->getStatementsForProperty( self::GND_ID ) as $gndStatement ) {
			// TODO: change when we know what to do item-ID wise
			return ItemId::newFromNumber( (int)preg_replace('/[^0-9]/', '', $gndStatement->getValue() ) );
		}

		throw new RuntimeException( 'No item id found' );
	}

	private function statementsFromGndItem( GndItem $gndItem ): StatementList {
		$statements = new StatementList();

		foreach ( $gndItem->getPropertyIds() as $id ) {
			foreach ( $gndItem->getStatementsForProperty( $id ) as $gndStatement ) {
				$statements->addNewStatement(
					new PropertyValueSnak(
						new PropertyId( $id ),
						// TODO: look up property type based on ID (PropertyDataTypeLookup)
						$this->valueBuilder->stringToDataValue( $gndStatement->getValue(), 'string' )
					)
				);
			}
		}

		return $statements;
	}

}
