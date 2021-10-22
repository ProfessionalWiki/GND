<?php

declare( strict_types = 1 );

namespace DNB\GND\Tests\Integration\Adapters\DataAccess;

use DNB\GND\Adapters\DataAccess\WikibaseRepoEntitySaver;
use DNB\GND\GndServicesFactory;
use MediaWikiIntegrationTestCase;
use User;
use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Term\Fingerprint;
use Wikibase\DataModel\Term\Term;
use Wikibase\DataModel\Term\TermList;
use Wikibase\Repo\WikibaseRepo;

/**
 * @group Database
 *
 * @covers \DNB\GND\Adapters\DataAccess\WikibasePropertyCollectionLookup
 * @covers \DNB\GND\Domain\PropertyCollection
 * @covers \DNB\GND\GndServicesFactory
 */
class WikibasePropertyCollectionLookupTest extends MediaWikiIntegrationTestCase {

	public function testPropertiesSavedInWikibaseRepoArePresentInTheCollection(): void {
		$this->saveEntity(
			new Property(
				new PropertyId( 'P133713371' ),
				new Fingerprint( new TermList( [ new Term( 'en', 'CollectionTest1' ) ] ) ),
				'string'
			)
		);

		$this->saveEntity(
			new Property(
				new PropertyId( 'P133713372' ),
				new Fingerprint( new TermList( [ new Term( 'en', 'CollectionTest2' ) ] ) ),
				'string'
			)
		);

		$properties = GndServicesFactory::getInstance()->getPropertyCollectionLookup()->getProperties();

		$this->assertSame(
			'CollectionTest1',
			$properties->getById( new PropertyId( 'P133713371' ) )->getLabels()->getByLanguage( 'en' )->getText()
		);

		$this->assertSame(
			'CollectionTest2',
			$properties->getById( new PropertyId( 'P133713372' ) )->getLabels()->getByLanguage( 'en' )->getText()
		);
	}

	private function saveEntity( EntityDocument $entity ): void {
		GndServicesFactory::getInstance()->newEntitySaver(
			User::newSystemUser( 'WikibasePropertyCollectionLookupTest', [ 'steal' => true ] )
		)->storeEntity( $entity );
	}

}
