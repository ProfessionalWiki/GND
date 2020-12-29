<?php

declare( strict_types = 1 );

namespace DNB\GND\Maintenance;

use DNB\GND\Adapters\DataAccess\GndConverterItemBuilder;
use DNB\GND\Adapters\DataAccess\GndConverterItemSource;
use DNB\GND\Adapters\DataAccess\WikibaseRepoItemStore;
use DNB\GND\Domain\ItemSource;
use DNB\GND\Domain\ItemStore;
use DNB\GND\UseCases\ImportItems\ImportItems;
use DNB\GND\UseCases\ImportItems\ImportItemsPresenter;
use Maintenance;
use Wikibase\Lib\WikibaseSettings;

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

		$this->newImportItemsUseCase()->import();

		$this->output( 'done' );
	}

	private function ensureWikibaseIsLoaded() {
		if ( !WikibaseSettings::isRepoEnabled() ) {
			$this->output( "You need to have Wikibase enabled in order to use this script!\n" );
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
			new GndConverterItemBuilder()
		);
	}

	private function getItemStore(): ItemStore {
		return new WikibaseRepoItemStore();
	}

	private function getImportItemsPresenter(): ImportItemsPresenter {
		return new class() implements ImportItemsPresenter {

		};
	}

}

$maintClass = ImportGndDump::class;
require_once RUN_MAINTENANCE_IF_MAIN;
