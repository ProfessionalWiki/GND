<?php

declare( strict_types = 1 );

namespace DNB\GND\Adapters\DataAccess\GndConverter;

use DataValues\DataValue;
use DataValues\StringValue;
use DataValues\TimeValue;
use InvalidArgumentException;
use ValueParsers\YearMonthDayTimeParser;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\Repo\WikibaseRepo;

class ProductionValueBuilder implements ValueBuilder {

	public function stringToDataValue( string $value, string $propertyTypeId ): DataValue {
		$valueTypeId = $this->propertyTypeToValueTypeId( $propertyTypeId );

		switch ( $valueTypeId ) {
			case 'string':
				return new StringValue( $value );
			case 'wikibase-entityid':
				return $this->stringToEntityIdValue( $value );
			case 'time':
				return $this->stringToTimeValue( $value );
		}

		throw new InvalidArgumentException( 'Value type not supported' );
	}

	private function stringToEntityIdValue( string $value ): EntityIdValue {
		// TODO: error cases
		return new EntityIdValue( new ItemId( $value ) );
	}

	private function propertyTypeToValueTypeId( string $propertyTypeId ): string {
		$map = WikibaseRepo::getDefaultInstance()->getDataTypeDefinitions()->getValueTypes();

		if ( !array_key_exists( $propertyTypeId, $map ) ) {
			throw new InvalidArgumentException( 'Property type not supported' );
		}

		return $map[$propertyTypeId];
	}

	private function stringToTimeValue( string $value ): TimeValue {
		return ( new YearMonthDayTimeParser() )->parse( $value );
	}

}
