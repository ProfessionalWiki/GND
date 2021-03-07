<?php

declare( strict_types = 1 );

namespace DNB\GND\UseCases\ImportItems;

use Exception;
use Wikibase\DataModel\Entity\EntityDocument;

interface ImportEntitiesPresenter {

	public function presentStorageStarted( EntityDocument $entity ): void;

	public function presentStorageSucceeded( EntityDocument $entity ): void;

	public function presentStorageFailed( EntityDocument $entity, Exception $exception ): void;

	public function presentImportFinished( ImportStats $stats ): void;

}
