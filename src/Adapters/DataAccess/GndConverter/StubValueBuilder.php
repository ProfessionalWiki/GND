<?php

declare( strict_types = 1 );

namespace DNB\GND\Adapters\DataAccess\GndConverter;

use DataValues\DataValue;
use DataValues\StringValue;

class StubValueBuilder implements ValueBuilder {

	public function stringToDataValue( string $value, string $typeId ): DataValue {
		return new StringValue( $value );
	}

}
