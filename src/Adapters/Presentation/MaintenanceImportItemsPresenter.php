<?php

declare( strict_types = 1 );

namespace DNB\GND\Adapters\Presentation;

use DNB\GND\UseCases\ImportItems\ImportItemsPresenter;
use Wikibase\DataModel\Entity\Item;

class MaintenanceImportItemsPresenter implements ImportItemsPresenter {

	private \Maintenance $maintenance;

	public function __construct( \Maintenance $maintenance ) {
		$this->maintenance = $maintenance;
	}

	public function presentStartStoring( Item $item ): void {
		$this->maintenance->outputChanneled(
			'Importing Item ' . $item->getId()->getSerialization() . '... ',
			$item->getId()->getSerialization()
		);
	}

	public function presentDoneStoring( Item $item ): void {
		$this->maintenance->outputChanneled(
			'done',
			$item->getId()->getSerialization()
		);
	}

}
