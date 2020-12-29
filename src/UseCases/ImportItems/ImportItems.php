<?php

declare( strict_types = 1 );

namespace DNB\GND\UseCases\ImportItems;

use DNB\GND\Domain\ItemSource;
use DNB\GND\Domain\ItemStore;
use DNB\GND\UseCases\ImportItemsPresenter;

class ImportItems {

	public function __construct( ItemSource $itemSource, ItemStore $store, ImportItemsPresenter $presenter ) {

	}

	public function import( ImportItemsRequest $requestModel ) {

	}

}
