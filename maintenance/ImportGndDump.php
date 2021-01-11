<?php

declare( strict_types = 1 );

namespace DNB\GND\Maintenance;

use DNB\GND\Adapters\DataAccess\GndConverterItemBuilder;
use DNB\GND\Adapters\DataAccess\GndConverterItemSource;
use DNB\GND\Adapters\DataAccess\WikibaseRepoItemStore;
use DNB\GND\Adapters\Presentation\MaintenanceImportItemsPresenter;
use DNB\GND\Domain\ItemSource;
use DNB\GND\Domain\ItemStore;
use DNB\GND\UseCases\ImportItems\ImportItems;
use DNB\GND\UseCases\ImportItems\ImportItemsPresenter;
use Maintenance;
use Onoi\MessageReporter\CallbackMessageReporter;
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
	}

	public function execute() {
		$this->ensureWikibaseIsLoaded();
		$this->ensureFileExists();

		$this->newImportItemsUseCase()->import();

		$this->output( 'done' );
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

	public function newImportItemsUseCase(): ImportItems {
		return new ImportItems(
			$this->getItemSource(),
			$this->getItemStore(),
			$this->getImportItemsPresenter(),
		);
	}

	private function getItemSource(): ItemSource {
		return new GndConverterItemSource(
			$this->getLineIterator(),
			new GndConverterItemBuilder()
		);
	}

	private function getLineIterator(): \Iterator {
		$file = new SplFileObject( $this->getOption( 'path' ) );

		while ( !$file->eof() ) {
			yield $file->fgets();
		}
	}

	private function getItemStore(): ItemStore {
		return new WikibaseRepoItemStore(
			$this->newEntityStore(),
			User::newSystemUser( 'Import Script', [ 'steal' => true ] )
		);
	}

	private function getImportItemsPresenter(): ImportItemsPresenter {
		return new MaintenanceImportItemsPresenter( $this );
	}

	private function newEntityStore(): EntityStore {
		return WikibaseRepo::getDefaultInstance()->getEntityStore();
	}

}

$maintClass = ImportGndDump::class;
require_once RUN_MAINTENANCE_IF_MAIN;
