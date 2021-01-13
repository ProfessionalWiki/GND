<?php

declare( strict_types = 1 );

namespace DNB\GND\Adapters\Presentation;

use DNB\GND\UseCases\ImportItems\ImportItemsPresenter;
use Wikibase\DataModel\Entity\Item;

class MaintenanceImportItemsPresenter implements ImportItemsPresenter {

	private \Maintenance $maintenance;
	private float $startTime;
	private int $itemCount = 0;
	private bool $quiet;

	public function __construct( \Maintenance $maintenance, bool $quiet ) {
		$this->maintenance = $maintenance;
		$this->quiet = $quiet;
	}

	public function presentStorageStarted( Item $item ): void {
		if ( !$this->quiet ) {
			$this->maintenance->outputChanneled(
				'Importing Item ' . $item->getId()->getSerialization() . '... ',
				$item->getId()->getSerialization()
			);
		}
	}

	public function presentStorageSucceeded( Item $item ): void {
		$this->itemCount++;

		if ( !$this->quiet ) {
			$this->maintenance->outputChanneled(
				'done',
				$item->getId()->getSerialization()
			);
		}
	}

	public function presentStorageFailed( Item $item, \Exception $exception ): void {
		// TODO: log stack trace

		$this->maintenance->outputChanneled(
			'failed: ' . $exception->getMessage(),
			$item->getId()->getSerialization()
		);
	}

	public function presentImportStarted(): void {
		$this->startTime = (float)hrtime(true);
	}

	public function presentImportFinished(): void {
		$this->maintenance->outputChanneled( 'Import complete!' );
		$this->maintenance->outputChanneled( "Duration:\t" . number_format( $this->getDurationInSeconds(), 2 ) . ' seconds' );
		$this->maintenance->outputChanneled( "Items:\t\t" . $this->itemCount );
		$this->maintenance->outputChanneled( "Items/second:\t" . number_format( $this->getItemsPerSecond(), 2 ) );
	}

	private function getDurationInSeconds(): float {
		return ( (float)hrtime(true) - $this->startTime ) / 1000000000;
	}

	private function getItemsPerSecond(): float {
		return $this->itemCount / $this->getDurationInSeconds();
	}


}
