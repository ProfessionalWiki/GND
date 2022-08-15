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
use Wikibase\DataModel\Term\Fingerprint;

/**
 * @covers \DNB\GND\Adapters\DataAccess\GndConverter\ItemBuilder
 */
class ItemBuilderTest extends TestCase {

	public function testEmptyRecordResultsInNoIdException(): void {
		$this->expectException( RuntimeException::class );
		$this->buildItem( new GndItem() );
	}

	private function assertStatementsAreBuild( GndItem $input, StatementList $expected ) {
		$this->assertEquals(
			$expected,
			$this->buildItem( $input )->getStatements()
		);
	}

	private function buildItem( GndItem $input ): Item {
		$builder = new ItemBuilder(
			new ProductionValueBuilder(),
			new StubDataTypeLookup( 'string' )
		);

		return $builder->build( $input );
	}

	public function testMultipleValuesForMultipleProperties() {
		$gndItem = new GndItem();
		$gndItem->addGndStatement( new GndStatement( GndItem::GND_IDN_PID, '123' ) );
		$gndItem->addGndStatement( new GndStatement( 'P42', 'a' ) );
		$gndItem->addGndStatement( new GndStatement( 'P42', 'b' ) );
		$gndItem->addGndStatement( new GndStatement( 'P42', 'c' ) );
		$gndItem->addGndStatement( new GndStatement( 'P1337', 'x' ) );
		$gndItem->addGndStatement( new GndStatement( 'P1337', 'a' ) );

		$statements = new StatementList();
		$statements->addNewStatement(
			new PropertyValueSnak( new PropertyId( GndItem::GND_IDN_PID ), new StringValue( '123' ) )
		);
		$statements->addNewStatement(
			new PropertyValueSnak( new PropertyId( 'P42' ), new StringValue( 'a' ) )
		);
		$statements->addNewStatement(
			new PropertyValueSnak( new PropertyId( 'P42' ), new StringValue( 'b' ) )
		);
		$statements->addNewStatement(
			new PropertyValueSnak( new PropertyId( 'P42' ), new StringValue( 'c' ) )
		);
		$statements->addNewStatement(
			new PropertyValueSnak( new PropertyId( 'P1337' ), new StringValue( 'x' ) )
		);
		$statements->addNewStatement(
			new PropertyValueSnak( new PropertyId( 'P1337' ), new StringValue( 'a' ) )
		);

		$this->assertStatementsAreBuild( $gndItem, $statements );
	}

	public function testIdIsBuild(): void {
		$gndItem = new GndItem();
		$gndItem->addGndStatement( new GndStatement( 'P42', 'a' ) );
		$gndItem->addGndStatement( new GndStatement( GndItem::GND_IDN_PID, '123' ) );
		$gndItem->addGndStatement( new GndStatement( 'P42', 'b' ) );

		$this->assertSame(
			'Q1230',
			$this->buildItem( $gndItem )->getId()->getSerialization()
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

		$statements = new StatementList();
		$statements->addNewStatement(
			new PropertyValueSnak( new PropertyId( GndItem::GND_ID ), new StringValue( '42X' ) )
		);

		$statements->addNewStatement(
			new PropertyValueSnak( new PropertyId( 'P1' ), new StringValue( 'main value' ) ),
			[
				new PropertyValueSnak( new PropertyId( 'P50' ), new StringValue( 'A1' ) ),
				new PropertyValueSnak( new PropertyId( 'P52' ), new StringValue( 'C1' ) ),
				new PropertyValueSnak( new PropertyId( 'P52' ), new StringValue( 'C2' ) ),
			]
		);

		$this->assertStatementsAreBuild( $gndItem,	$statements );
	}

	public function testSkipsPropertiesWithUnknownTypes(): void {
		$builder = new ItemBuilder(
			new ProductionValueBuilder(),
			new InMemoryDataTypeLookup()
		);

		$gndItem = new GndItem();
		$gndItem->addGndStatement( new GndStatement( GndItem::GND_IDN_PID, '42X' ) );

		$this->assertEquals(
			new StatementList(),
			$builder->build( $gndItem )->getStatements()
		);
	}

	public function testLabelAndAliases(): void {
		$gndItem = new GndItem();
		$gndItem->addGndStatement( new GndStatement( 'P1', 'a' ) );
		$gndItem->addGndStatement( new GndStatement( GndItem::GND_IDN_PID, '123' ) );
		$gndItem->addGndStatement( new GndStatement( 'P90', 'foo' ) ); // Label PID
		$gndItem->addGndStatement( new GndStatement( 'P91', 'bar' ) ); // Label PID
		$gndItem->addGndStatement( new GndStatement( 'P2', 'c' ) );
		$gndItem->addGndStatement( new GndStatement( GndItem::INTERNAL_ID_PID, '456' ) );
		$gndItem->addGndStatement( new GndStatement( 'P3', 'a' ) );

		$expected = new Fingerprint();
		$expected->setLabel( 'de', 'foo' );
		$expected->setAliasGroup( 'de', [ '123', '456' ] );

		$this->assertEquals(
			$expected,
			$this->buildItem( $gndItem )->getFingerprint()
		);
	}

}
