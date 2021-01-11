<?php

declare( strict_types = 1 );

namespace DNB\GND\UseCases\ImportItems;

use Wikibase\DataModel\Entity\Item;

interface ImportItemsPresenter {

	public function presentStartStoring( Item $item ): void;

	public function presentDoneStoring( Item $item ): void;

}
