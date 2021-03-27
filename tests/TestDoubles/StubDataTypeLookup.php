<?php

declare( strict_types = 1 );

namespace DNB\GND\Tests\TestDoubles;

use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Services\Lookup\PropertyDataTypeLookup;

class StubDataTypeLookup implements PropertyDataTypeLookup {

	private string $stubReturnValue;

	public function __construct( string $stubReturnValue ) {
		$this->stubReturnValue = $stubReturnValue;
	}

	public function getDataTypeIdForProperty( PropertyId $propertyId ): string {
		return $this->stubReturnValue;
	}

}
