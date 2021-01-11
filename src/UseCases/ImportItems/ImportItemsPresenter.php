<?php

declare( strict_types = 1 );

namespace DNB\GND\UseCases\ImportItems;

use Wikibase\DataModel\Entity\Item;

interface ImportItemsPresenter {

	public function presentStorageStarted( Item $item ): void;

	public function presentStorageSucceeded( Item $item ): void;

	public function presentImportStarted(): void;

	public function presentImportFinished(): void;

	public function presentStorageFailed( Item $item, \Exception $exception ): void;

}
