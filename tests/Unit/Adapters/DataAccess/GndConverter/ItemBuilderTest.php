<?php

declare( strict_types = 1 );

namespace DNB\GND\Tests\Unit\Adapters\DataAccess\GndConverter;

use DataValues\StringValue;
use DNB\GND\Adapters\DataAccess\GndConverter\ItemBuilder;
use DNB\GND\Adapters\DataAccess\GndConverter\ProductionValueBuilder;
use DNB\GND\Tests\TestDoubles\StubDataTypeLookup;
use DNB\WikibaseConverter\GndItem;
use DNB\WikibaseConverter\GndQualifier;
use DNB\WikibaseConverter\GndStatement;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Services\Lookup\InMemoryDataTypeLookup;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Statement\StatementList;

/**
 * @covers \DNB\GND\Adapters\DataAccess\GndConverter\ItemBuilder
 */
class ItemBuilderTest extends TestCase {

	public function testEmptyRecordResultsInNoIdException(): void {
		$this->expectException( RuntimeException::class );
		$this->testItemIsBuild(
			new GndItem(),
			new Item()
		);
	}

	private function testItemIsBuild( GndItem $input, Item $expected ) {
		$builder = new ItemBuilder(
			new ProductionValueBuilder(),
			new StubDataTypeLookup( 'string' )
		);

		$this->assertEquals(
			$expected,
			$builder->build( $input )
		);
	}

	public function testMultipleValuesForMultipleProperties() {
		$gndItem = new GndItem();
		$gndItem->addGndStatement( new GndStatement( GndItem::GND_ID, '123' ) );
		$gndItem->addGndStatement( new GndStatement( 'P42', 'a' ) );
		$gndItem->addGndStatement( new GndStatement( 'P42', 'b' ) );
		$gndItem->addGndStatement( new GndStatement( 'P42', 'c' ) );
		$gndItem->addGndStatement( new GndStatement( 'P1337', 'x' ) );
		$gndItem->addGndStatement( new GndStatement( 'P1337', 'a' ) );

		$item = new Item( new ItemId( 'Q1230' ) );
		$item->getStatements()->addNewStatement(
			new PropertyValueSnak( new PropertyId( GndItem::GND_ID ), new StringValue( '123' ) )
		);
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

	public function testQualifiers(): void {
		$gndItem = new GndItem();
		$gndItem->addGndStatement( new GndStatement( GndItem::GND_ID, '42X' ) );

		$gndItem->addGndStatement( new GndStatement(
			'P1',
			'main value',
			[
				new GndQualifier( 'P50', 'A1' ),
				new GndQualifier( 'P52', 'C1' ),
				new GndQualifier( 'P52', 'C2' ),
			]
		) );

		$item = new Item( new ItemId( 'Q421' ) );
		$item->getStatements()->addNewStatement(
			new PropertyValueSnak( new PropertyId( GndItem::GND_ID ), new StringValue( '42X' ) )
		);

		$item->getStatements()->addNewStatement(
			new PropertyValueSnak( new PropertyId( 'P1' ), new StringValue( 'main value' ) ),
			[
				new PropertyValueSnak( new PropertyId( 'P50' ), new StringValue( 'A1' ) ),
				new PropertyValueSnak( new PropertyId( 'P52' ), new StringValue( 'C1' ) ),
				new PropertyValueSnak( new PropertyId( 'P52' ), new StringValue( 'C2' ) ),
			]
		);

		$this->testItemIsBuild(
			$gndItem,
			$item
		);
	}

	public function testSkipsPropertiesWithUnknownTypes(): void {
		$builder = new ItemBuilder(
			new ProductionValueBuilder(),
			new InMemoryDataTypeLookup()
		);

		$gndItem = new GndItem();
		$gndItem->addGndStatement( new GndStatement( GndItem::GND_ID, '42X' ) );

		$this->assertEquals(
			new StatementList(),
			$builder->build( $gndItem )->getStatements()
		);
	}

}
