<?php

declare( strict_types = 1 );

namespace DNB\GND\UseCases\ShowFullDoku;

use DataValues\BooleanValue;
use DataValues\DataValue;
use DataValues\StringValue;
use DNB\GND\Domain\Doku\GndField;
use DNB\GND\Domain\Doku\GndReference;
use DNB\GND\Domain\Doku\GndSubfield;
use DNB\GND\Domain\PropertyCollection;
use DNB\GND\Domain\PropertyCollectionLookup;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Reference;
use Wikibase\DataModel\Services\Lookup\ItemLookup;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Statement\Statement;

/**
 * TODO: $languageCode is passed around a lot: suggests extract class refactoring
 */
class ShowFullDoku {

	private const ELEMENT_OF_PROPERTY = 'P2';
	private const GND_FIELD_ITEM = 'Q2';

	private const CODINGS_PROPERTY = 'P4';
	private const CODING_TYPE_PROPERTY = 'P3';
	private const CODING_MAP = [
		'Q1317' => 'PICA+',
		'Q1316' => 'PICA3',
		'Q1320' => 'MARC 21',
	];

	private const SUBFIELDS_PROPERTY = 'P15';
	private const SUBFIELD_DESCRIPTION_PROPERTY = 'P7';
	private const SUBFIELD_REF_DESCRIPTION_PROP = 'P7';
	private const SUBFIELD_REF_URL_PROP = 'P371';
	private const SUBFIELD_ALLOWED_VALUES_PROP = 'P8';

	private const DEFINITION_PROPERTY = 'P1';
	private const REPEATABLE_PROPERTY = 'P12';

	private const VALIDATION_PROPERTY = 'P9';
	private const RULES_OF_USE_PROPERTY = 'P10';
	private const EXAMPLES_PROPERTY = 'P11';

	private FullDokuPresenter $presenter;
	private PropertyCollectionLookup $propertyLookup;
	private ItemLookup $itemLookup;
	private PropertyCollection $properties;

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

		foreach ( $this->getProperties()->asArray() as $property ) {
			$field = $this->propertyToGndField( $property, $languageCode );

			if ( $field !== null ) {
				$gndFields[] = $field;
			}
		}

		$this->presenter->present( $gndFields );
	}

	private function getProperties(): PropertyCollection {
		$this->properties ??= $this->propertyLookup->getProperties();
		return $this->properties;
	}

	private function propertyToGndField( Property $property, string $languageCode ): ?GndField {
		if ( !$this->isElementOfGndField( $property ) ) {
			return null;
		}

		$field = new GndField();

		$field->id = $property->getId()->serialize();

		$field->label = $property->getLabels()->toTextArray()[$languageCode] ?? '';
		$field->description = $property->getDescriptions()->toTextArray()[$languageCode] ?? '';
		$field->aliases = $property->getAliasGroups()->toTextArray()[$languageCode] ?? [];

		$field->definition = $this->getStringValuesFromProperty( $property, self::DEFINITION_PROPERTY )[0] ?? '';
		$field->codings = $this->getCodingsFromProperty( $property );
		$field->isRepeatable = $this->getIsRepeatableFromProperty( $property );
		$field->subfields = $this->getSubfieldsFromProperty( $property, $languageCode );
		$field->validation = $this->getStringValuesFromProperty( $property, self::VALIDATION_PROPERTY );
		$field->rulesOfUse = $this->getStringValuesFromProperty( $property, self::RULES_OF_USE_PROPERTY );
		$field->examples = $this->getExamplesFromProperty( $property, $languageCode );

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

	private function getCodingsFromProperty( Property $property ): array {
		$codings = [];

		$codingStatements = $property->getStatements()->getByPropertyId( new PropertyId( self::CODINGS_PROPERTY ) );

		foreach ( $codingStatements as $statement ) {
			$codingType = ( new NiceStatement( $statement ) )->getQualifierValue(self::CODING_TYPE_PROPERTY );

			if ( $codingType instanceof EntityIdValue ) {
				$codingTypeId = $codingType->getEntityId()->serialize();

				if ( array_key_exists( $codingTypeId, self::CODING_MAP ) ) {
					$codings[self::CODING_MAP[$codingTypeId]] = $statement->getMainSnak()->getDataValue()->getValue();
				}
			}
		}

		return $codings;
	}

	private function getIsRepeatableFromProperty( Property $property ): bool {
		foreach ( $this->getMainSnakDataValues( $property, self::REPEATABLE_PROPERTY ) as $dataValue ) {
			return $this->valueIsTrue( $dataValue );
		}

		return false;
	}

	private function valueIsTrue( DataValue $value ): bool {
		if ( $value instanceof BooleanValue ) {
			return $value->getValue();
		}
		if ( $value instanceof StringValue ) {
			return strtolower( $value->getValue() ) === 'ja';
		}

		throw new \InvalidArgumentException();
	}

	/**
	 * @return array<int, GndSubfield>
	 */
	private function getSubfieldsFromProperty( Property $property, string $languageCode ): array {
		return array_filter(
			array_map(
				fn( Statement $statement ) => $this->statementToSubfield( $statement, $languageCode ),
				$property->getStatements()->getByPropertyId( new PropertyId( self::SUBFIELDS_PROPERTY ) )->toArray()
			),
			fn ( ?GndSubfield $subfield ) => $subfield !== null
		);
	}

	private function statementToSubfield( Statement $statement, string $languageCode ): ?GndSubfield {
		$mainSnak = $statement->getMainSnak();

		if ( $mainSnak instanceof PropertyValueSnak ) {
			$mainValue = $mainSnak->getDataValue();

			if ( $mainValue instanceof EntityIdValue ) {
				return new GndSubfield(
					$mainValue->getEntityId()->getSerialization(),
					$this->getPropertyLabel( $mainValue->getEntityId(), $languageCode ),
					( new NiceStatement( $statement ) )->getQualifierStringValue( self::SUBFIELD_DESCRIPTION_PROPERTY ) ?? '',
					$this->getSubfieldCodings( $mainValue->getEntityId() ),
					$this->getAllowedValuesFromStatement( $statement, $languageCode ),
					$this->referencesFromStatement( $statement ),
					$this->getIsRepeatableFromStatement( $statement )
				);
			}
		}

		return null;
	}

	private function getIsRepeatableFromStatement( Statement $statement ): bool {
		$qualifiersById = ( new NiceSnaks( $statement->getQualifiers() ) )->getLastDataValueByPropertyId();

		if ( !array_key_exists( self::REPEATABLE_PROPERTY, $qualifiersById ) ) {
			return false;
		}

		return $this->valueIsTrue( $qualifiersById[self::REPEATABLE_PROPERTY] );
	}

	private function getAllowedValuesFromStatement( Statement $statement, string $languageCode ): array {
		$qualifierValues = ( new NiceSnaks( $statement->getQualifiers() ) )->getDataValuesForPropertyId(
			new PropertyId( self::SUBFIELD_ALLOWED_VALUES_PROP )
		);

		$allowedValues = [];

		foreach ( $qualifierValues as $dataValue ) {
			if ( $dataValue instanceof EntityIdValue ) {
				$allowedValues[$dataValue->getEntityId()->serialize()] = $this->getItemLabel(
					$dataValue->getEntityId()->serialize(),
					$languageCode
				) ?? $dataValue->getEntityId()->serialize();
			}
		}

		return $allowedValues;
	}

	private function getPropertyLabel( PropertyId $id, string $languageCode ): string {
		if ( $this->getProperties()->hasId( $id ) ) {
			return $this->getProperties()->getById( $id )->getLabels()->toTextArray()[$languageCode] ?? $id->serialize();
		}

		return $id->serialize();
	}

	/**
	 * @return array<string, string>
	 */
	private function getSubfieldCodings( PropertyId $propertyId ): array {
		if ( $this->getProperties()->hasId( $propertyId ) ) {
			return $this->getCodingsFromProperty( $this->getProperties()->getById( $propertyId ) );
		}

		return [];
	}

	private function referencesFromStatement( Statement $statement ): array {
		$references = [];

		/**
		 * @var Reference $reference
		 */
		foreach ( $statement->getReferences() as $reference ) {
			$reference = $this->wikibaseReferenceToGndReference( $reference );

			if ( $reference !== null ) {
				$references[] = $reference;
			}
		}

		return $references;
	}

	private function wikibaseReferenceToGndReference( Reference $reference ): ?GndReference {
		$valuesById = ( new NiceSnaks( $reference->getSnaks() ) )->getLastDataValueByPropertyId();

		if ( array_key_exists( self::SUBFIELD_REF_DESCRIPTION_PROP, $valuesById ) ) {
			$nameValue = $valuesById[self::SUBFIELD_REF_DESCRIPTION_PROP];

			if ( $nameValue instanceof StringValue ) {
				return new GndReference(
					$nameValue->getValue(),
					array_key_exists( self::SUBFIELD_REF_URL_PROP, $valuesById ) ? $valuesById[self::SUBFIELD_REF_URL_PROP]->getValue() : null
				);
			}
		}

		return null;
	}

	/**
	 * @return array<int, string>
	 */
	private function getStringValuesFromProperty( Property $property, string $propertyId ): array {
		$values = [];

		foreach ( $this->getMainSnakDataValues( $property, $propertyId ) as $dataValue ) {
			if ( $dataValue instanceof StringValue ) {
				$values[] = $dataValue->getValue();
			}
		}

		return $values;
	}

	/**
	 * @return array<string, string>
	 */
	private function getExamplesFromProperty( Property $property, string $languageCode ): array {
		$examples = [];

		foreach ( $this->getMainSnakDataValues( $property, self::EXAMPLES_PROPERTY ) as $dataValue ) {
			if ( $dataValue instanceof EntityIdValue ) {
				$itemId = $dataValue->getEntityId()->getSerialization();
				$itemLabel = $this->getItemLabel( $itemId, $languageCode );

				if ( $itemLabel !== null ) {
					$examples[$itemId] = $itemLabel;
				}
			}
		}

		return $examples;
	}

	private function getItemLabel( string $itemId, string $languageCode ): ?string {
		try {
			$item = $this->itemLookup->getItemForId( new ItemId( $itemId ) );
		}
		catch ( \Exception $exception ) {
			return null;
		}

		if ( $item === null ) {
			return null;
		}

		return $item->getFingerprint()->getLabels()->toTextArray()[$languageCode] ?? null;
	}

	/**
	 * @param Property $property
	 * @param string $statementPropertyId
	 *
	 * @return array<int, DataValue>
	 */
	private function getMainSnakDataValues( Property $property, string $statementPropertyId ): array {
		$snaks = $property->getStatements()->getByPropertyId( new PropertyId( $statementPropertyId ) )->getMainSnaks();
		return ( new NiceSnaks( $snaks ) )->getAllDataValues();
	}

}
