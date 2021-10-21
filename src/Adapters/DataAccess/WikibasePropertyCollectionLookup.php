<?php

declare( strict_types = 1 );

namespace DNB\GND\Adapters\DataAccess;

use DNB\GND\Domain\PropertyCollection;
use DNB\GND\Domain\PropertyCollectionLookup;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Services\EntityId\EntityIdPager;
use Wikibase\DataModel\Services\Lookup\PropertyLookup;

class WikibasePropertyCollectionLookup implements PropertyCollectionLookup {

	private EntityIdPager $propertyIdPager;
	private PropertyLookup $propertyLookup;

	public function __construct( EntityIdPager $propertyIdPager, PropertyLookup $propertyLookup ) {
		$this->propertyIdPager = $propertyIdPager;
		$this->propertyLookup = $propertyLookup;
	}

	public function getProperties(): PropertyCollection {
		$properties = [];

		while ( true ) {
			$ids = $this->propertyIdPager->fetchIds( 1 );

			if ( $ids === [] ) {
				break;
			}

			if ( !( $ids[0] instanceof PropertyId ) ) {
				throw new \RuntimeException( 'Got ID of wrong entity type, thanks generic interface' );
			}

			$properties[] = $this->propertyLookup->getPropertyForId( $ids[0] );
		}

		return new PropertyCollection( ...$properties );
	}

}
