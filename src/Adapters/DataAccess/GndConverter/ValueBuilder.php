<?php

declare( strict_types = 1 );

namespace DNB\GND\Adapters\DataAccess\GndConverter;

use DataValues\DataValue;

interface ValueBuilder {

	public function stringToDataValue( string $value, string $typeId ): DataValue;

}
