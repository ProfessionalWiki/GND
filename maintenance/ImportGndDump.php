<?php

declare( strict_types = 1 );

namespace DNB\GND\Maintenance;

use DNB\GND\Adapters\DataAccess\GndConverter\ItemBuilder;
use DNB\GND\Adapters\DataAccess\GndConverter\ProductionValueBuilder;
use DNB\GND\Adapters\DataAccess\GndConverterItemSource;
use DNB\GND\Adapters\DataAccess\WikibaseRepoEntitySaver;
use DNB\GND\Adapters\Presentation\MaintenanceImportEntitiesPresenter;
use DNB\GND\Domain\EntitySaver;
use DNB\GND\Domain\ItemSource;
use DNB\GND\UseCases\ImportItems\ImportEntities;
use DNB\GND\UseCases\ImportItems\ImportEntitiesPresenter;
use Maintenance;
use SplFileObject;
use User;
use Wikibase\Lib\Store\EntityStore;
use Wikibase\Lib\WikibaseSettings;
use Wikibase\Repo\WikibaseRepo;

$basePath = getenv( 'MW_INSTALL_PATH' ) !== false ? getenv( 'MW_INSTALL_PATH' ) : __DIR__ . '/../../..';

require_once $basePath . '/maintenance/Maintenance.php';

class ImportGndDump extends Maintenance {

	public function __construct() {
		parent::__construct();

		$this->requireExtension( 'GND' );
		$this->addDescription( 'Imports a GND dump in PICA+ format into Wikibase Repository' );

		$this->addOption(
			'path',
			'Path to the PICA+ GND dump',
			true,
			true
		);

		$this->addOption(
			'limit',
			'Limit how many records are imported',
			false,
			true
		);

		$this->addOption(
			'offset',
			'Number of records to skip over before starting the import',
			false,
			true
		);

		$this->addOption(
			'error-log',
			'Log file to append errors to',
			false,
			true
		);
	}

	public function execute() {
		$this->ensureWikibaseIsLoaded();
		$this->ensureFileExists();

		$this->newImportItemsUseCase()->import();
	}

	private function ensureWikibaseIsLoaded() {
		if ( !WikibaseSettings::isRepoEnabled() ) {
			$this->output( "You need to have Wikibase enabled in order to use this script!\n" );
			exit;
		}
	}

	private function ensureFileExists() {
		$path = $this->getOption( 'path' );

		if ( !is_readable( $path ) ) {
			$this->output( "Could not read file: $path!\n" );
			exit;
		}
	}

	public function newImportItemsUseCase(): ImportEntities {
		return new ImportEntities(
			$this->getItemSource(),
			$this->getItemStore(),
			$this->getImportItemsPresenter(),
		);
	}

	private function getItemSource(): ItemSource {
		return new GndConverterItemSource(
			$this->getLineIterator(),
			$this->newItemBuilder()
		);
	}

	private function getLineIterator(): \Iterator {
		$file = new \LimitIterator(
			new SplFileObject( $this->getOption( 'path' ) ),
			(int)$this->getOption( 'offset', 0 ),
			(int)$this->getOption( 'limit', -1 )
		);

		foreach ( $file as $line ) {
			yield $line;
		}
	}

	private function newItemBuilder(): ItemBuilder {
		return new ItemBuilder(
			new ProductionValueBuilder(),
			WikibaseRepo::getDefaultInstance()->getPropertyDataTypeLookup()
		);
	}

	private function getItemStore(): EntitySaver {
		return new WikibaseRepoEntitySaver(
			$this->newEntityStore(),
			$this->newUser()
		);
	}

	private function newUser(): User {
		return User::newSystemUser( 'Import Script', [ 'steal' => true ] );
	}

	private function getImportItemsPresenter(): ImportEntitiesPresenter {
		return new MaintenanceImportEntitiesPresenter(
			$this,
			$this->hasOption( 'quiet' ),
			$this->newExceptionLogger()
		);
	}

	private function newExceptionLogger(): \Closure {
		if ( !$this->hasOption( 'error-log' ) ) {
			return function() {};
		}

		$fileHandle = fopen( $this->getOption( 'error-log' ), 'a' );

		return function( \Exception $exception ) use ( $fileHandle ) {
			fwrite(
				$fileHandle,
				$exception->getMessage() . "\n" .
				$exception->getTraceAsString() . "\n" .
				( $exception->getPrevious() ? $exception->getPrevious()->getTraceAsString() . "\n" : '' ) . "\n"
			);

			fflush( $fileHandle );
		};
	}

	private function newEntityStore(): EntityStore {
		return WikibaseRepo::getDefaultInstance()->getEntityStore();
	}

}

$maintClass = ImportGndDump::class;
require_once RUN_MAINTENANCE_IF_MAIN;
