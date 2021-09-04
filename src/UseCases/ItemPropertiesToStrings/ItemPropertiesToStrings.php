<?php

declare( strict_types = 1 );

namespace DNB\GND\UseCases\ItemPropertiesToStrings;

use DataValues\StringValue;
use DNB\GND\Domain\EntitySaver;
use DNB\GND\Domain\ItemSource;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Services\Lookup\PropertyLookup;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Snak\Snak;
use Wikibase\DataModel\Statement\Statement;

class ItemPropertiesToStrings {

	private const ITEM_PROPERTY_TYPE = 'wikibase-item';
	private const STRING_PROPERTY_TYPE = 'string';

	private PropertyLookup $propertyLookup;
	private EntitySaver $entitySaver;
	private ItemSource $itemSource;

	/**
	 * @var array<int, string>
	 */
	private array $propertyIdsAsStrings;

	public function __construct(
		PropertyLookup $propertyLookup, EntitySaver $entitySaver, ItemSource $itemSource
	) {
		$this->propertyLookup = $propertyLookup;
		$this->entitySaver = $entitySaver;
		$this->itemSource = $itemSource;
	}

	public function migrate( PropertyId ...$propertyIds ): void {
		$this->propertyIdsAsStrings = $this->idObjectsToStrings( $propertyIds );

		foreach ( $propertyIds as $propertyId ) {
			$this->changePropertyType( $propertyId );
		}

		$this->migrateValues();
	}

	private function idObjectsToStrings( array $propertyIds ): array {
		return array_map(
			fn ( PropertyId $id ) => (string)$id,
			$propertyIds
		);
	}

	private function changePropertyType( PropertyId $propertyId ): void {
		$property = $this->propertyLookup->getPropertyForId( $propertyId );

		if ( $property->getDataTypeId() !== self::ITEM_PROPERTY_TYPE ) {
			throw new \RuntimeException( $propertyId->serialize() . ' has incorrect data type "' . $property->getDataTypeId() . '"' );
		}

		$property->setDataTypeId( self::STRING_PROPERTY_TYPE );

		$this->entitySaver->storeEntity( $property );
	}

	private function migrateValues(): void {
		while ( $item = $this->itemSource->next() ) {
			$this->migrateItem( $item );
			$this->entitySaver->storeEntity( $item );
		}
	}

	private function migrateItem( Item $item ): void {
		foreach ( $item->getStatements() as $statement ) {
			$this->migrateMainSnak( $statement );
		}
	}

	private function migrateMainSnak( Statement $statement ): void {
		$migratedSnak = $this->getMigratedSnakOrNull( $statement->getMainSnak() );

		if ( $migratedSnak !== null ) {
			$statement->setMainSnak( $migratedSnak );
		}
	}

	private function getMigratedSnakOrNull( Snak $snak ): ?PropertyValueSnak {
		if ( !$this->shouldMigrateValuesOfProperty( $snak->getPropertyId() ) ) {
			return null;
		}

		if ( $snak instanceof PropertyValueSnak ) {
			$dataValue = $snak->getDataValue();

			if ( $dataValue instanceof EntityIdValue ) {
				 return new PropertyValueSnak(
					 $snak->getPropertyId(),
					new StringValue( (string)$dataValue->getEntityId() )
				);
			}
		}

		return null;
	}

	private function shouldMigrateValuesOfProperty( PropertyId $id ): bool {
		return in_array( (string)$id, $this->propertyIdsAsStrings );
	}

}
