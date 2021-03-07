<?php

declare( strict_types = 1 );

namespace DNB\GND\UseCases\ImportItems;

use DNB\GND\Domain\ItemSource;
use DNB\GND\Domain\EntitySaver;
use Exception;

class ImportItems {

	private ItemSource $itemSource;
	private EntitySaver $store;
	private ImportItemsPresenter $presenter;

	public function __construct( ItemSource $itemSource, EntitySaver $store, ImportItemsPresenter $presenter ) {
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
			$item = $this->itemSource->next();

			if ( $item === null ) {
				break;
			}

			$this->presenter->presentStorageStarted( $item );

			try {
				$this->store->storeEntity( $item );
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
