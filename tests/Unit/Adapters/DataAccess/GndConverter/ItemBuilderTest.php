<?php

declare( strict_types = 1 );

namespace DNB\GND\Tests\Unit\Adapters\DataAccess\GndConverter;

use DataValues\StringValue;
use DNB\GND\Adapters\DataAccess\GndConverter\ItemBuilder;
use DNB\GND\Adapters\DataAccess\GndConverter\ProductionValueBuilder;
use DNB\WikibaseConverter\GndItem;
use DNB\WikibaseConverter\GndStatement;
use PHPUnit\Framework\TestCase;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\PropertyValueSnak;

/**
 * @covers \DNB\GND\Adapters\DataAccess\GndConverter\ItemBuilder
 */
class ItemBuilderTest extends TestCase {

	public function testEmptyRecordResultsInEmptyItem() {
		$this->testItemIsBuild(
			new GndItem(),
			new Item()
		);
	}

	private function testItemIsBuild( GndItem $input, Item $expected ) {
		$builder = new ItemBuilder( new ProductionValueBuilder() );

		$this->assertEquals(
			$expected,
			$builder->build( $input )
		);
	}

	public function testMultipleValuesForMultipleProperties() {
		$gndItem = new GndItem();
		$gndItem->addGndStatement( new GndStatement( 'P42', 'a' ) );
		$gndItem->addGndStatement( new GndStatement( 'P42', 'b' ) );
		$gndItem->addGndStatement( new GndStatement( 'P42', 'c' ) );
		$gndItem->addGndStatement( new GndStatement( 'P1337', 'x' ) );
		$gndItem->addGndStatement( new GndStatement( 'P1337', 'a' ) );

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
			$gndItem,
			$item
		);
	}

}
