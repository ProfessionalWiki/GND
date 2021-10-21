<?php

declare( strict_types = 1 );

namespace DNB\GND\Domain;

interface PropertyCollectionLookup {

	public function getProperties(): PropertyCollection;

}
