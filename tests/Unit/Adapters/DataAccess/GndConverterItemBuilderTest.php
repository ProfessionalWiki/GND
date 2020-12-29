<?php

declare( strict_types = 1 );

namespace DNB\GND\Tests\Adapters\DataAccess;

use DNB\GND\Application\Adapters\DataAccess\GndConverterItemBuilder;
use DNB\WikibaseConverter\WikibaseRecord;
use PHPUnit\Framework\TestCase;
use Wikibase\DataModel\Entity\Item;

/**
 * @covers \DNB\GND\Application\Adapters\DataAccess\GndConverterItemBuilder
 */
class GndConverterItemBuilderTest extends TestCase {

	public function testCanRunTests() {
		$this->testItemIsBuild(
			new WikibaseRecord(),
			new Item()
		);
	}

	private function testItemIsBuild( WikibaseRecord $input, Item $expected ) {
		$builder = new GndConverterItemBuilder();

		$this->assertEquals(
			$expected,
			$builder->build( $input )
		);
	}

}
