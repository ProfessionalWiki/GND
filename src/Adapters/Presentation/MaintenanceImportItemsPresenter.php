<?php

declare( strict_types = 1 );

namespace DNB\GND\Adapters\Presentation;

use Closure;
use DNB\GND\UseCases\ImportItems\ImportItemsPresenter;
use DNB\GND\UseCases\ImportItems\ImportStats;
use Exception;
use Maintenance;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;

class MaintenanceImportItemsPresenter implements ImportItemsPresenter {

	private Maintenance $maintenance;
	private bool $quiet;
	private Closure $exceptionLogger;

	public function __construct( Maintenance $maintenance, bool $quiet, Closure $exceptionLogger ) {
		$this->maintenance = $maintenance;
		$this->quiet = $quiet;
		$this->exceptionLogger = $exceptionLogger;
	}

	public function presentStorageStarted( Item $item ): void {
		$this->outputItemProgress(
			$item->getId(),
			'Importing Item ' . $item->getId()->getSerialization() . '... '
		);
	}

	public function presentStorageSucceeded( Item $item ): void {
		$this->outputItemProgress(
			$item->getId(),
			'done'
		);
	}

	public function presentStorageFailed( Item $item, Exception $exception ): void {
		$exceptionLogger = $this->exceptionLogger;
		$exceptionLogger( $exception );

		$this->outputItemProgress(
			$item->getId(),
			'failed: ' . $exception->getMessage()
		);
	}

	private function outputItemProgress( ItemId $id, string $message ): void {
		if ( !$this->quiet ) {
			$this->maintenance->outputChanneled(
				$message,
				$id->getSerialization()
			);
		}
	}

	public function presentImportFinished( ImportStats $stats ): void {
		$this->maintenance->outputChanneled( 'Import complete!' );
		$this->maintenance->outputChanneled( "Duration:\t" . number_format( $stats->getDurationInSeconds(), 2 ) . ' seconds' );
		$this->maintenance->outputChanneled( "Items:\t\t" . $stats->getItemCount() );
		$this->maintenance->outputChanneled( "Items/second:\t" . number_format( $stats->getItemsPerSecond(), 2 ) );
		$this->maintenance->outputChanneled( sprintf(
			"Failures:\t%1d (%2.4f%%)",
			$stats->getFailureCount(),
			$stats->getFailurePercentage()
		) );
	}

}
