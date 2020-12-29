<?php

declare( strict_types = 1 );

namespace DNB\GND\Adapters\DataAccess;

use DNB\GND\Domain\ItemSource;

class GndConverterItemSource implements ItemSource {

	public function __construct( GndConverterItemBuilder $itemBuilder ) {
	}

}
