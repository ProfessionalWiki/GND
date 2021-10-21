<?php

declare( strict_types = 1 );

namespace DNB\GND\Maintenance;

use DNB\GND\Adapters\DataAccess\FullLocalItemSource;
use DNB\GND\Adapters\DataAccess\InMemoryItemSource;
use DNB\GND\Adapters\DataAccess\WikibaseRepoEntitySaver;
use DNB\GND\GndServicesFactory;
use DNB\GND\UseCases\ItemPropertiesToStrings\ItemPropertiesToStrings as ItemPropertiesToStringsUseCase;
use DNB\GND\UseCases\ItemPropertiesToStrings\PropertyChangePresenter;
use Maintenance;
use User;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\Lib\WikibaseSettings;
use Wikibase\Repo\Store\Sql\SqlEntityIdPagerFactory;
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

		$this->outputChanneled( "Done!" );
	}

	private function getPropertyIdsFromOptions(): array {
		return array_map(
			fn( string $id ) => new PropertyId( trim( $id ) ),
			explode( ',', $this->getOption( 'properties' ) )
		);
	}

	private function newUseCase(): ItemPropertiesToStringsUseCase {
		$servicesFactory = GndServicesFactory::getInstance();

		return new ItemPropertiesToStringsUseCase(
			$servicesFactory->getPropertyLookup(),
			$servicesFactory->newEntitySaver( $this->newUser() ),
			$servicesFactory->newFullLocalItemSource(),
			$this->newPresenter()
		);
	}

	private function newUser(): User {
		return User::newSystemUser( 'Property migration script', [ 'steal' => true ] );
	}

	private function newPresenter(): PropertyChangePresenter {
		return new class( $this ) implements PropertyChangePresenter {
			private Maintenance $maintenance;

			public function __construct( Maintenance $maintenance ) {
				$this->maintenance = $maintenance;
			}

			public function presentChangingPropertyType( PropertyId $id, string $oldType, string $newType ) {
				$this->maintenance->outputChanneled( "Updating property $id from $oldType to $newType" );
			}

			public function presentMigratingItem( ItemId $id ) {
				$this->maintenance->outputChanneled( "Migrating values of item $id" );
			}
		};
	}

}

$maintClass = ItemPropertiesToStrings::class;
require_once RUN_MAINTENANCE_IF_MAIN;
