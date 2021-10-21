<?php

declare( strict_types = 1 );

namespace DNB\GND\Tests\Unit\Domain;

use DNB\GND\Domain\PropertyCollection;
use PHPUnit\Framework\TestCase;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Term\Fingerprint;
use Wikibase\DataModel\Term\Term;
use Wikibase\DataModel\Term\TermList;

/**
 * @covers PropertyCollection
 */
class PropertyCollectionTest extends TestCase {

	public function testAsArray(): void {
		$prop1 = new Property(
			new PropertyId( 'P133713371' ),
			new Fingerprint( new TermList( [ new Term( 'en', 'CollectionTest1' ) ] ) ),
			'string'
		);

		$prop2 = new Property(
			new PropertyId( 'P133713372' ),
			new Fingerprint( new TermList( [ new Term( 'en', 'CollectionTest2' ) ] ) ),
			'string'
		);

		$collection = new PropertyCollection( $prop1, $prop2 );

		$this->assertEquals(
			[
				'P133713371' => $prop1,
				'P133713372' => $prop2
			],
			$collection->asArray()
		);
	}

}
