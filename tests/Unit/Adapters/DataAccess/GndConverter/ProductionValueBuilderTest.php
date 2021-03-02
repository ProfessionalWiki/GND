<?php

declare( strict_types = 1 );

namespace DNB\GND\Tests\Unit\Adapters\DataAccess\GndConverter;

use DataValues\StringValue;
use DataValues\TimeValue;
use DNB\GND\Adapters\DataAccess\GndConverter\ProductionValueBuilder;
use InvalidArgumentException;
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
			$this->newBuilder()->stringToDataValue( 'Q42', 'wikibase-item' )
		);
	}

	public function testUrlValue(): void {
		$this->assertEquals(
			new StringValue( 'https://professional.wiki' ),
			$this->newBuilder()->stringToDataValue( 'https://professional.wiki', 'url' )
		);
	}

	public function testUnknownPropertyType(): void {
		$this->expectException( InvalidArgumentException::class );
		$this->newBuilder()->stringToDataValue( 'maw', '404' );
	}

	public function testTimeValueWithDayPrecision(): void {
		$this->assertEquals(
			new TimeValue( '+1749-08-28T00:00:00Z', 0, 0, 0, TimeValue::PRECISION_DAY, TimeValue::CALENDAR_GREGORIAN ),
			$this->newBuilder()->stringToDataValue( '28.08.1749', 'time' )
		);
	}

	public function testTimeValueWithYearPrecision(): void {
		$this->assertEquals(
			new TimeValue( '+1749-00-00T00:00:00Z', 0, 0, 0, TimeValue::PRECISION_YEAR, TimeValue::CALENDAR_GREGORIAN ),
			$this->newBuilder()->stringToDataValue( '1749', 'time' )
		);
	}

	public function testExternalIdValue(): void {
		$this->assertEquals(
			new StringValue( 'hi' ),
			$this->newBuilder()->stringToDataValue( 'hi', 'external-id' )
		);
	}

}
