<?php

declare( strict_types = 1 );

namespace DNB\GND\Tests\Unit\Adapters\DataAccess;

use DataValues\StringValue;
use DNB\GND\Adapters\DataAccess\GndConverterItemBuilder;
use DNB\WikibaseConverter\PropertyWithValues;
use DNB\WikibaseConverter\WikibaseRecord;
use PHPUnit\Framework\TestCase;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\PropertyValueSnak;

/**
 * @covers \DNB\GND\Adapters\DataAccess\GndConverterItemBuilder
 */
class GndConverterItemBuilderTest extends TestCase {

	public function testEmptyRecordResultsInEmptyItem() {
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

	public function testMultipleValuesForMultipleProperties() {
		$record = new WikibaseRecord();
		$record->addValuesOfOneProperty(
			new PropertyWithValues(
				'P42',
				[ 'a', 'b', 'c' ]
			)
		);
		$record->addValuesOfOneProperty(
			new PropertyWithValues(
				'P1337',
				[ 'x', 'a' ]
			)
		);

		$item = new Item();
		$item->getStatements()->addNewStatement(
			new PropertyValueSnak( new PropertyId( 'P42' ), new StringValue( 'a' ) )
		);
		$item->getStatements()->addNewStatement(
			new PropertyValueSnak( new PropertyId( 'P42' ), new StringValue( 'b' ) )
		);
		$item->getStatements()->addNewStatement(
			new PropertyValueSnak( new PropertyId( 'P42' ), new StringValue( 'c' ) )
		);
		$item->getStatements()->addNewStatement(
			new PropertyValueSnak( new PropertyId( 'P1337' ), new StringValue( 'x' ) )
		);
		$item->getStatements()->addNewStatement(
			new PropertyValueSnak( new PropertyId( 'P1337' ), new StringValue( 'a' ) )
		);

		$this->testItemIsBuild(
			$record,
			$item
		);
	}

}
