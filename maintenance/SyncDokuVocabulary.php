<?php

declare( strict_types = 1 );

namespace DNB\GND\Maintenance;

use DNB\GND\Adapters\DataAccess\DokuEntitySource;
use DNB\GND\Adapters\DataAccess\DokuSparqlIdSource;
use DNB\GND\Adapters\DataAccess\MediaWikiFileFetcher;
use DNB\GND\Adapters\DataAccess\WikibaseRepoEntitySaver;
use DNB\GND\Adapters\Presentation\MaintenanceImportEntitiesPresenter;
use DNB\GND\Domain\EntitySaver;
use DNB\GND\Domain\EntitySource;
use DNB\GND\UseCases\ImportItems\ImportEntities;
use DNB\GND\UseCases\ImportItems\ImportEntitiesPresenter;
use Maintenance;
use User;
use Wikibase\Lib\Store\EntityStore;
use Wikibase\Lib\WikibaseSettings;
use Wikibase\Repo\WikibaseRepo;

$basePath = getenv( 'MW_INSTALL_PATH' ) !== false ? getenv( 'MW_INSTALL_PATH' ) : __DIR__ . '/../../..';

require_once $basePath . '/maintenance/Maintenance.php';

class SyncDokuVocabulary extends Maintenance {

	public function __construct() {
		parent::__construct();

		$this->requireExtension( 'GND' );
		$this->addDescription( 'Saves the Doku wiki vocabulary properties and items into the local wiki, replacing existing content with the same IDs.' );
	}

	public function execute() {
		if ( !WikibaseSettings::isRepoEnabled() ) {
			$this->output( "You need to have Wikibase enabled in order to use this maintenance script!\n" );
			exit;
		}

		$this->newImportItemsUseCase()->import();
	}

	public function newImportItemsUseCase(): ImportEntities {
		return new ImportEntities(
			$this->getEntitySource(),
			$this->getEntitySaver(),
			$this->getImportItemsPresenter(),
		);
	}

	private function getEntitySource(): EntitySource {

		return new DokuEntitySource(
			$this->getEntityIds(),
			new MediaWikiFileFetcher(),
			WikibaseRepo::getDefaultInstance()->getBaseDataModelDeserializerFactory()->newEntityDeserializer()
		);
	}

	private function getEntityIds(): array {
		if ( !$this->hasOption( 'quiet' ) ) {
			$this->outputChanneled( 'Finding vocabulary entities via SPARQL... ', 'sparql' );
		}

		$ids = ( new DokuSparqlIdSource( new MediaWikiFileFetcher() ) )->getVocabularyIds();

		if ( !$this->hasOption( 'quiet' ) ) {
			$this->outputChanneled( 'done', 'sparql' );
			$this->output( "\nStarting import of " . count( $ids ) . " entities\n" );
		}

		return $ids;
	}

	private function getEntitySaver(): EntitySaver {
		return new WikibaseRepoEntitySaver(
			$this->newEntityStore(),
			$this->newUser()
		);
	}

	private function newEntityStore(): EntityStore {
		return WikibaseRepo::getDefaultInstance()->getEntityStore();
	}

	private function newUser(): User {
		return User::newSystemUser( 'Doku Sync Script', [ 'steal' => true ] );
	}

	private function getImportItemsPresenter(): ImportEntitiesPresenter {
		return new MaintenanceImportEntitiesPresenter(
			$this,
			$this->hasOption( 'quiet' ),
			function() {}
		);
	}

}

$maintClass = SyncDokuVocabulary::class;
require_once RUN_MAINTENANCE_IF_MAIN;
