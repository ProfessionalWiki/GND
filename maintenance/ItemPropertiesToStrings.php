<?php

declare( strict_types = 1 );

namespace DNB\GND\Maintenance;

use DNB\GND\Adapters\DataAccess\InMemoryItemSource;
use DNB\GND\Adapters\DataAccess\WikibaseRepoEntitySaver;
use DNB\GND\UseCases\ItemPropertiesToStrings\ItemPropertiesToStrings as ItemPropertiesToStringsUseCase;
use Maintenance;
use User;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\Lib\WikibaseSettings;
use Wikibase\Repo\WikibaseRepo;

$basePath = getenv( 'MW_INSTALL_PATH' ) !== false ? getenv( 'MW_INSTALL_PATH' ) : __DIR__ . '/../../..';

require_once $basePath . '/maintenance/Maintenance.php';

class ItemPropertiesToStrings extends Maintenance {

	public function __construct() {
		parent::__construct();

		$this->requireExtension( 'GND' );
		$this->addDescription( 'Changed the type of properties from item to string, including migration of values in the wiki' );

		$this->addOption(
			'properties',
			'IDs of the properties to change from item to string',
			true,
			true
		);
	}

	public function execute() {
		if ( !WikibaseSettings::isRepoEnabled() ) {
			$this->output( "You need to have Wikibase enabled in order to use this maintenance script!\n" );
			exit;
		}

		$this->newUseCase()->migrate( ...$this->getPropertyIdsFromOptions() );
	}

	private function getPropertyIdsFromOptions(): array {
		return array_map(
			fn( string $id ) => new PropertyId( trim( $id ) ),
			explode( ',', $this->getOption( 'properties' ) )
		);
	}

	private function newUseCase(): ItemPropertiesToStringsUseCase {
		$repo = WikibaseRepo::getDefaultInstance();

		return new ItemPropertiesToStringsUseCase(
			$repo->getPropertyLookup(),
			new WikibaseRepoEntitySaver(
				$repo->getEntityStore(),
				$this->newUser()
			),
			new InMemoryItemSource() // TODO: create FullLocalItemSource
		);
	}

	private function newUser(): User {
		return User::newSystemUser( 'Property migration script', [ 'steal' => true ] );
	}

}

$maintClass = ItemPropertiesToStrings::class;
require_once RUN_MAINTENANCE_IF_MAIN;
