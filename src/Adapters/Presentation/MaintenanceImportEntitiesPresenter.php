<?php

declare( strict_types = 1 );

namespace DNB\GND\Adapters\Presentation;

use Closure;
use DNB\GND\UseCases\ImportItems\ImportEntitiesPresenter;
use DNB\GND\UseCases\ImportItems\ImportStats;
use Exception;
use Maintenance;
use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\DataModel\Entity\EntityId;

class MaintenanceImportEntitiesPresenter implements ImportEntitiesPresenter {

	private Maintenance $maintenance;
	private bool $quiet;
	private Closure $exceptionLogger;

	public function __construct( Maintenance $maintenance, bool $quiet, Closure $exceptionLogger ) {
		$this->maintenance = $maintenance;
		$this->quiet = $quiet;
		$this->exceptionLogger = $exceptionLogger;
	}

	public function presentStorageStarted( EntityDocument $entity ): void {
		$this->outputItemProgress(
			$entity->getId(),
			'Importing ' . $entity->getId()->getSerialization() . '... '
		);
	}

	public function presentStorageSucceeded( EntityDocument $entity ): void {
		$this->outputItemProgress(
			$entity->getId(),
			'done'
		);
	}

	public function presentStorageFailed( EntityDocument $entity, Exception $exception ): void {
		$exceptionLogger = $this->exceptionLogger;
		$exceptionLogger( $exception );

		$this->outputItemProgress(
			$entity->getId(),
			'failed: ' . $exception->getMessage()
		);
	}

	private function outputItemProgress( EntityId $id, string $message ): void {
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
