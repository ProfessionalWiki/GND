<?php

declare( strict_types = 1 );

namespace DNB\GND\Maintenance;

use Maintenance;
use Wikibase\Lib\WikibaseSettings;

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

		$this->output( "done\n" );
	}

}

$maintClass = SyncDokuVocabulary::class;
require_once RUN_MAINTENANCE_IF_MAIN;
