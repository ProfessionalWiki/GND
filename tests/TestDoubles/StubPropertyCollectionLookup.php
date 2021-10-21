<?php

declare( strict_types = 1 );

namespace DNB\GND\Tests\TestDoubles;

use DNB\GND\Domain\PropertyCollection;
use DNB\GND\Domain\PropertyCollectionLookup;

class StubPropertyCollectionLookup implements PropertyCollectionLookup {

	private PropertyCollection $returnValue;

	public function __construct( PropertyCollection $returnValue ) {
		$this->returnValue = $returnValue;
	}

	public function getProperties(): PropertyCollection {
		return $this->returnValue;
	}

}
