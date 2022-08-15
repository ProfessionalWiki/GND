<?php

declare( strict_types = 1 );

namespace DNB\GND\Adapters\DataAccess\GndConverter;

use DNB\WikibaseConverter\GndItem;
use DNB\WikibaseConverter\GndQualifier;
use DNB\WikibaseConverter\GndStatement;
use RuntimeException;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Services\Lookup\PropertyDataTypeLookup;
use Wikibase\DataModel\Services\Lookup\PropertyDataTypeLookupException;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Snak\SnakList;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\DataModel\Statement\StatementList;
use Wikibase\DataModel\Term\Fingerprint;

/**
 * Builds a Wikibase Item (using the Wikibase DataModel classes) from
 * an item representation coming from the GND Wikibase Converter library.
 */
class ItemBuilder {

	private ValueBuilder $valueBuilder;
	private PropertyDataTypeLookup $propertyTypeLookup;

	public function __construct( ValueBuilder $valueBuilder, PropertyDataTypeLookup $propertyTypeLookup ) {
		$this->valueBuilder = $valueBuilder;
		$this->propertyTypeLookup = $propertyTypeLookup;
	}

	public function build( GndItem $gndItem ): Item {
		$id = $gndItem->getNumericId();

		if ( $id === null ) {
			throw new RuntimeException( 'No item id found' );
		}

		return new Item(
			ItemId::newFromNumber( $id ),
			$this->fingerprintFromGndItem( $gndItem ),
			null,
			$this->statementsFromGndItem( $gndItem )
		);
	}

	private function statementsFromGndItem( GndItem $gndItem ): StatementList {
		$statements = new StatementList();

		foreach ( $gndItem->getPropertyIds() as $id ) {
			foreach ( $gndItem->getStatementsForProperty( $id ) as $gndStatement ) {
				try {
					$statement = $this->newStatement( $id, $gndStatement );
				}
				catch ( PropertyDataTypeLookupException $exception ) {
					continue;
				}

				$statements->addStatement( $statement );
			}
		}

		return $statements;
	}

	private function newStatement( string $id, GndStatement $gndStatement ): Statement {
		return new Statement(
			$this->newWikibaseQualifier( $id, $gndStatement->getValue() ),
			new SnakList(
				array_map(
					fn( GndQualifier $qualifier ) => $this->newWikibaseQualifier(
						$qualifier->getPropertyId(),
						$qualifier->getValue()
					),
					$gndStatement->getQualifiers()
				)
			)
		);
	}

	private function newWikibaseQualifier( string $propertyId, string $value ): PropertyValueSnak {
		return new PropertyValueSnak(
			new PropertyId( $propertyId ),
			$this->valueBuilder->stringToDataValue(
				$value,
				$this->propertyTypeLookup->getDataTypeIdForProperty( new PropertyId( $propertyId ) )
			)
		);
	}

	private function fingerprintFromGndItem( GndItem $gndItem ): Fingerprint {
		$fingerprint = new Fingerprint();

		$label = $gndItem->getGermanLabel();

		if ( is_string( $label ) ) {
			$fingerprint->setLabel( 'de', $label );
		}

		$fingerprint->setAliasGroup( 'de', $gndItem->getGermanAliases() );

		return $fingerprint;
	}

}
