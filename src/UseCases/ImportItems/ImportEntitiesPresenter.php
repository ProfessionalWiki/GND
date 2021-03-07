<?php

declare( strict_types = 1 );

namespace DNB\GND\UseCases\ImportItems;

use Exception;
use Wikibase\DataModel\Entity\EntityDocument;

interface ImportEntitiesPresenter {

	public function presentStorageStarted( EntityDocument $item ): void;

	public function presentStorageSucceeded( EntityDocument $item ): void;

	public function presentStorageFailed( EntityDocument $item, Exception $exception ): void;

	public function presentImportFinished( ImportStats $stats ): void;

}
