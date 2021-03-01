<?php

declare( strict_types = 1 );

namespace DNB\GND\Tests\Unit\Adapters\DataAccess\GndConverter;

use DataValues\StringValue;
use DNB\GND\Adapters\DataAccess\GndConverter\ProductionValueBuilder;
use PHPUnit\Framework\TestCase;

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

}
