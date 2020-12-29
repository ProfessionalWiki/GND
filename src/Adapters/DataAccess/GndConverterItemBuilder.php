<?php

declare( strict_types = 1 );

namespace DNB\GND\Adapters\DataAccess;

use DNB\WikibaseConverter\WikibaseRecord;
use Wikibase\DataModel\Entity\Item;

/**
 * Builds a Wikibase Item (using the Wikibase DataModel classes) from
 * an item representation coming from the GND Wikibase Converter library.
 */
class GndConverterItemBuilder {

	public function build( WikibaseRecord $record ): Item {

	}

}
