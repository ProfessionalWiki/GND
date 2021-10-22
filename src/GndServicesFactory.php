<?php

declare( strict_types = 1 );

namespace DNB\GND;

use DNB\GND\Adapters\DataAccess\FullLocalItemSource;
use DNB\GND\Adapters\DataAccess\WikibasePropertyCollectionLookup;
use DNB\GND\Adapters\DataAccess\WikibaseRepoEntitySaver;
use DNB\GND\Domain\EntitySaver;
use DNB\GND\Domain\ItemSource;
use DNB\GND\Domain\PropertyCollectionLookup;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Services\EntityId\SeekableEntityIdPager;
use Wikibase\DataModel\Services\Lookup\ItemLookup;
use Wikibase\DataModel\Services\Lookup\LegacyAdapterItemLookup;
use Wikibase\DataModel\Services\Lookup\LegacyAdapterPropertyLookup;
use Wikibase\DataModel\Services\Lookup\PropertyLookup;
use Wikibase\Repo\Store\Sql\SqlEntityIdPagerFactory;
use Wikibase\Repo\WikibaseRepo;

class GndServicesFactory {

	public static function getInstance(): self {
		static $instance = null;
		$instance ??= new self();
		return $instance;
	}

	private function __construct() {
	}

	public function newFullLocalItemSource(): ItemSource {
		return new FullLocalItemSource(
			$this->getItemIdPager(),
			$this->getItemLookup()
		);
	}

	public function getItemLookup(): ItemLookup {
		return new LegacyAdapterItemLookup( WikibaseRepo::getDefaultInstance()->getEntityLookup() );
	}

	public function getPropertyLookup(): PropertyLookup {
		return new LegacyAdapterPropertyLookup( WikibaseRepo::getDefaultInstance()->getEntityLookup() );
	}

	public function newEntitySaver( \User $user ): EntitySaver {
		return new WikibaseRepoEntitySaver(
			WikibaseRepo::getDefaultInstance()->getEntityStore(),
			$user
		);
	}

	public function getPropertyIdPager(): SeekableEntityIdPager {
		return $this->newIdPagerFactory()->newSqlEntityIdPager( [ Property::ENTITY_TYPE ] );
	}

	public function getItemIdPager(): SeekableEntityIdPager {
		return $this->newIdPagerFactory()->newSqlEntityIdPager( [ Item::ENTITY_TYPE ] );
	}

	private function newIdPagerFactory(): SqlEntityIdPagerFactory {
		$repo = WikibaseRepo::getDefaultInstance();

		return new SqlEntityIdPagerFactory(
			$repo->getEntityNamespaceLookup(),
			$repo->getEntityIdLookup()
		);
	}

	public function getPropertyCollectionLookup(): PropertyCollectionLookup {
		return new WikibasePropertyCollectionLookup(
			$this->getPropertyIdPager(),
			$this->getPropertyLookup()
		);
	}

}
