<?php

declare( strict_types = 1 );

namespace DNB\GND\UseCases\ShowFullDoku;

use DNB\GND\Domain\Doku\GndField;
use DNB\GND\Domain\PropertyCollectionLookup;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Services\Lookup\ItemLookup;
use Wikibase\DataModel\Snak\PropertyValueSnak;

class ShowFullDoku {

	private const ELEMENT_OF_PROPERTY = 'P2';
	private const GND_FIELD_ITEM = 'Q2';

	private FullDokuPresenter $presenter;
	private PropertyCollectionLookup $propertyLookup;
	private ItemLookup $itemLookup;

	public function __construct( FullDokuPresenter $presenter, PropertyCollectionLookup $propertyLookup, ItemLookup $itemLookup ) {
		$this->presenter = $presenter;
		$this->propertyLookup = $propertyLookup;
		$this->itemLookup = $itemLookup;
	}

	public function showFullDoku( string $languageCode ): void {
		/**
		 * @var GndField[]
		 */
		$gndFields = [];

		$properties = $this->propertyLookup->getProperties();

		foreach ( $properties->asArray() as $property ) {
			$field = $this->propertyToGndField( $property, $languageCode );

			if ( $field !== null ) {
				$gndFields[] = $field;
			}
		}

		$this->presenter->present( $gndFields );
	}

	private function propertyToGndField( Property $property, string $languageCode ): ?GndField {
		if ( !$this->isElementOfGndField( $property ) ) {
			return null;
		}

		$field = new GndField();

		$field->id = $property->getId()->serialize();
		$field->label = $property->getLabels()->getByLanguage( $languageCode )->getText();
		$field->description = $property->getDescriptions()->getByLanguage( $languageCode )->getText();
		$field->aliases = $property->getAliasGroups()->toTextArray()[$languageCode] ?? [];

		return $field;
	}

	private function isElementOfGndField( Property $property ): bool {
		$elementOfStatements = $property->getStatements()->getByPropertyId( new PropertyId( self::ELEMENT_OF_PROPERTY ) )->toArray();

		if ( !array_key_exists( 0, $elementOfStatements ) ) {
			return false;
		}

		return $elementOfStatements[0]->getMainSnak()->equals(
			new PropertyValueSnak(
				new PropertyId( self::ELEMENT_OF_PROPERTY ),
				new EntityIdValue( new ItemId( self::GND_FIELD_ITEM ) )
			)
		);
	}

}
