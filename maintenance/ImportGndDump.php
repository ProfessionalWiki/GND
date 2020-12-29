<?php

declare( strict_types = 1 );

namespace DNB\GND\Maintenance;

use Maintenance;
use User;
use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\Lib\WikibaseSettings;
use Wikibase\Repo\EditEntity\EditEntity;
use Wikibase\Repo\WikibaseRepo;

$basePath = getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' )
	: __DIR__ . '/../../../..';

require_once $basePath . '/maintenance/Maintenance.php';

class ImportGndDump extends Maintenance {

	public function __construct() {
		parent::__construct();

		$this->addDescription( 'Imports a GND dump in PICA+ format into Wikibase Repository' );
	}

	public function execute() {
		$this->ensureWikibaseIsLoaded();

		$item = new Item();
		$item->setId( new ItemId( 'Q50' ) );
		$item->setLabel( 'en', 'testing' );

		$this->saveItem( $item );

		$this->output( 'done' );
	}

	private function ensureWikibaseIsLoaded() {
		if ( !WikibaseSettings::isRepoEnabled() ) {
			$this->output( "You need to have Wikibase enabled in order to use this script!\n" );
			exit;
		}
	}

	private function saveItem( Item $item ) {
		$status = $this->createEntity( $item );

		if ( $status->isOK() ) {
			$this->output( "\n" . $status->getValue()['revision']->getEntity()->getId() . "\n" );
		} else {
			$this->output( "\n" . $status->getValue() . "\n" );
		}
	}

	private function createEntity( EntityDocument $entity ): \Status {
		return $this->newEntitySaver()->attemptSave( $entity, 'test summary', EDIT_NEW, false );
	}

	private function newEntitySaver(): EditEntity {
		return WikibaseRepo::getDefaultInstance()->newEditEntityFactory()->newEditEntity(
			User::newSystemUser( 'Import Script', [ 'steal' => true ] )
		);
	}

}

$maintClass = ImportGndDump::class;
require_once RUN_MAINTENANCE_IF_MAIN;
