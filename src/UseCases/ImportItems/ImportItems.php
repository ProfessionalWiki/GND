<?php

declare( strict_types = 1 );

namespace DNB\GND\UseCases\ImportItems;

use DNB\GND\Domain\ItemSource;
use DNB\GND\Domain\ItemStore;

class ImportItems {

	private ItemSource $itemSource;
	private ItemStore $store;
	private ImportItemsPresenter $presenter;

	public function __construct( ItemSource $itemSource, ItemStore $store, ImportItemsPresenter $presenter ) {
		$this->itemSource = $itemSource;
		$this->store = $store;
		$this->presenter = $presenter;
	}

	public function import(): void {
		$this->presenter->presentImportStarted();

		while ( true ) {
			$item = $this->itemSource->nextItem();

			if ( $item === null ) {
				break;
			}

			$this->presenter->presentStartStoring( $item );

			try {
				$this->store->storeItem( $item );
			} catch ( \Exception $exception ) {
			}

			$this->presenter->presentDoneStoring( $item );
		}

		$this->presenter->presentImportFinished();
	}

}
