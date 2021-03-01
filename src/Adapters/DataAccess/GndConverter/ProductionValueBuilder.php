<?php

declare( strict_types = 1 );

namespace DNB\GND\Adapters\DataAccess\GndConverter;

use DataValues\DataValue;
use DataValues\StringValue;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\ItemId;

class ProductionValueBuilder implements ValueBuilder {

	// TODO: error cases
	public function stringToDataValue( string $value, string $typeId ): DataValue {
		if ( $typeId === 'wikibase-entityid' ) {
			return $this->stringToEntityIdValue( $value );
		}

		return new StringValue( $value );
	}

	private function stringToEntityIdValue( string $value ): EntityIdValue {
		return new EntityIdValue( new ItemId( $value ) );
	}

}
