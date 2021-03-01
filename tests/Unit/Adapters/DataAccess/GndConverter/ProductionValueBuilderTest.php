<?php

declare( strict_types = 1 );

namespace DNB\GND\Tests\Unit\Adapters\DataAccess\GndConverter;

use DataValues\StringValue;
use DNB\GND\Adapters\DataAccess\GndConverter\ProductionValueBuilder;
use PHPUnit\Framework\TestCase;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\ItemId;

/**
 * @covers \DNB\GND\Adapters\DataAccess\GndConverter\ProductionValueBuilder
 */
class ProductionValueBuilderTest extends TestCase {

	public function testStringValue(): void {
		$this->assertEquals(
			new StringValue( 'hi' ),
			$this->newBuilder()->stringToDataValue( 'hi', 'string' )
		);
	}

	private function newBuilder(): ProductionValueBuilder {
		return new ProductionValueBuilder();
	}

	public function testItemIdValue(): void {
		$this->assertEquals(
			new EntityIdValue( new ItemId( 'Q42' ) ),
			$this->newBuilder()->stringToDataValue( 'Q42', 'wikibase-entityid' )
		);
	}

}
