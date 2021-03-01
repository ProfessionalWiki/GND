<?php

declare( strict_types = 1 );

namespace DNB\GND\UseCases\ImportItems;

use DNB\GND\Domain\ItemSource;
use DNB\GND\Domain\ItemStore;
use Exception;

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
		$stats = new ImportStats();
		$stats->recordStart();

//		$factory = \MediaWiki\MediaWikiServices::getInstance()->getDBLoadBalancerFactory();
//		$factory->beginMasterChanges(__METHOD__);

		while ( true ) {
			$item = $this->itemSource->nextItem();

			if ( $item === null ) {
				break;
			}

			$this->presenter->presentStorageStarted( $item );

			try {
				$this->store->storeItem( $item );
			} catch ( Exception $exception ) {
				$stats->recordFailure();
				$this->presenter->presentStorageFailed( $item, $exception );
				continue;
			}

			$stats->recordSuccess();
			$this->presenter->presentStorageSucceeded( $item );
		}

//		$factory->commitMasterChanges(__METHOD__);
		$this->presenter->presentImportFinished( $stats );
	}

}
