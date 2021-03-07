<?php

declare( strict_types = 1 );

namespace DNB\GND\UseCases\ImportItems;

use DNB\GND\Domain\EntitySaver;
use DNB\GND\Domain\EntitySource;
use Exception;

class ImportEntities {

	private EntitySource $entitySource;
	private EntitySaver $store;
	private ImportEntitiesPresenter $presenter;

	public function __construct( EntitySource $entitySource, EntitySaver $store, ImportEntitiesPresenter $presenter ) {
		$this->entitySource = $entitySource;
		$this->store = $store;
		$this->presenter = $presenter;
	}

	public function import(): void {
		$stats = new ImportStats();
		$stats->recordStart();

//		$factory = \MediaWiki\MediaWikiServices::getInstance()->getDBLoadBalancerFactory();
//		$factory->beginMasterChanges(__METHOD__);

		while ( true ) {
			$entity = $this->entitySource->next();

			if ( $entity === null ) {
				break;
			}

			$this->presenter->presentStorageStarted( $entity );

			try {
				$this->store->storeEntity( $entity );
			} catch ( Exception $exception ) {
				$stats->recordFailure();
				$this->presenter->presentStorageFailed( $entity, $exception );
				continue;
			}

			$stats->recordSuccess();
			$this->presenter->presentStorageSucceeded( $entity );
		}

//		$factory->commitMasterChanges(__METHOD__);
		$this->presenter->presentImportFinished( $stats );
	}

}
