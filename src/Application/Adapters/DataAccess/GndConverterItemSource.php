<?php

declare( strict_types = 1 );

namespace DNB\GND\Application\Adapters\DataAccess;

use DNB\GND\Application\Domain\ItemSource;

class GndConverterItemSource implements ItemSource {

	public function __construct( GndConverterItemBuilder $itemBuilder ) {
	}

}
