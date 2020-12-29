<?php

declare( strict_types = 1 );

namespace DNB\GND\Application\UseCases\ImportItems;

use DNB\GND\Application\Domain\ItemSource;
use DNB\GND\Application\Domain\ItemStore;
use DNB\GND\Application\UseCases\ImportItemsPresenter;

class ImportItems {

	public function __construct( ItemSource $itemSource, ItemStore $store, ImportItemsPresenter $presenter ) {

	}

	public function import( ImportItemsRequest $requestModel ) {

	}

}
