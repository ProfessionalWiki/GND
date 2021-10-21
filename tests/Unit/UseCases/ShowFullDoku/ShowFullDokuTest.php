<?php

declare( strict_types = 1 );

namespace DNB\GND\Tests\Unit\UseCases\ShowFullDoku;

use DataValues\StringValue;
use DNB\GND\Domain\Doku\GndField;
use DNB\GND\Domain\PropertyCollection;
use DNB\GND\UseCases\ShowFullDoku\ShowFullDoku;
use DNB\GND\Tests\TestDoubles\SpyFullDokuPresenter;
use DNB\GND\Tests\TestDoubles\StubPropertyCollectionLookup;
use PHPUnit\Framework\TestCase;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Services\Lookup\InMemoryEntityLookup;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Snak\SnakList;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\DataModel\Statement\StatementList;
use Wikibase\DataModel\Term\AliasGroup;
use Wikibase\DataModel\Term\AliasGroupList;
use Wikibase\DataModel\Term\Fingerprint;
use Wikibase\DataModel\Term\Term;
use Wikibase\DataModel\Term\TermList;

/**
 * @covers \DNB\GND\UseCases\ShowFullDoku\ShowFullDoku
 */
class ShowFullDokuTest extends TestCase {

	private const LANG_CODE = 'en';

	public function testResultIsEmptyWhenThereAreNoProperties(): void {
		$properties = [];
		$items = [];

		$this->assertSame(
			[],
			$this->getUseCasePresentedValueForPropsAndItems( $properties, $items )
		);
	}

	/**
	 * @param Property[] $properties
	 * @param Item[] $items
	 * @return GndField[]
	 */
	private function getUseCasePresentedValueForPropsAndItems( array $properties, array $items ): array {
		$presenter = new SpyFullDokuPresenter();

		$useCase = new ShowFullDoku(
			$presenter,
			new StubPropertyCollectionLookup( new PropertyCollection( ...$properties ) ),
			new InMemoryEntityLookup( ...$items )
		);

		$useCase->showFullDoku( self::LANG_CODE );

		return $presenter->getFields();
	}

	public function testPropertyToGndField(): void {
		$properties = [
			new Property(
				new PropertyId( 'P4242' ),
				new Fingerprint(
					new TermList( [
						new Term( 'wrong', 'WrongLabel' ),
						new Term( self::LANG_CODE, 'ExpectedLabel' ),
						new Term( 'nope', 'WrongLabel' ),
					] ),
					new TermList( [
						new Term( 'wrong', 'WrongDescription' ),
						new Term( self::LANG_CODE, 'ExpectedDescription' ),
						new Term( 'nope', 'WrongDescription' ),
					] ),
					new AliasGroupList( [
						new AliasGroup( 'wrong', [ 'a', 'b' ] ),
						new AliasGroup( self::LANG_CODE, [ 'foo', 'bar' ] ),
						new AliasGroup( 'nope', [ 'c', 'd' ] ),
					] )
				),
				'string',
				new StatementList(
					new Statement( new PropertyValueSnak( new PropertyId( 'P1' ), new StringValue( 'ExpectedDefinition' ) ) ),
					new Statement( new PropertyValueSnak( new PropertyId( 'P2' ), new EntityIdValue( new ItemId( 'Q2' ) ) ) ),

					new Statement(
						new PropertyValueSnak( new PropertyId( 'P4' ), new StringValue( '028A' ) ),
						new SnakList( [
							new PropertyValueSnak( new PropertyId( 'P3' ), new EntityIdValue( new ItemId( 'Q1317' ) ) )
						] )
					),
					new Statement(
						new PropertyValueSnak( new PropertyId( 'P4' ), new StringValue( '100' ) ),
						new SnakList( [
							new PropertyValueSnak( new PropertyId( 'P3' ), new EntityIdValue( new ItemId( 'Q1320' ) ) )
						] )
					),
					new Statement(
						new PropertyValueSnak( new PropertyId( 'P4' ), new StringValue( '100' ) ),
						new SnakList( [
							new PropertyValueSnak( new PropertyId( 'P3' ), new EntityIdValue( new ItemId( 'Q1316' ) ) )
						] )
					),
				)
			)
		];
		$items = [];

		$gndField = new GndField();
		$gndField->id = 'P4242';
		$gndField->label = 'ExpectedLabel';
		$gndField->description = 'ExpectedDescription';
		$gndField->aliases = [ 'foo', 'bar' ];
		$gndField->definition = 'ExpectedDefinition';
		$gndField->codings = [
			'PICA+' => '028A',
			'MARC 21' => '100',
			'PICA3' => '100',
		];

		$this->assertEquals(
			[
				$gndField
			],
			$this->getUseCasePresentedValueForPropsAndItems( $properties, $items )
		);
	}

	public function testPropertyThatIsNotElementOfGndFieldIsSkipped(): void {
		$property = $this->newMinimalValidGndProperty();

		$property->setStatements( new StatementList(
			// P2 needs to be Q2
			new Statement( new PropertyValueSnak( new PropertyId( 'P2' ), new EntityIdValue( new ItemId( 'Q1' ) ) ) )
		) );

		$this->assertIsSkipped( $property );
	}

	private function newMinimalValidGndProperty(): Property {
		return new Property(
			new PropertyId( 'P4242' ),
			new Fingerprint( new TermList( [] ) ),
			'string',
			new StatementList(
				new Statement( new PropertyValueSnak( new PropertyId( 'P2' ), new EntityIdValue( new ItemId( 'Q2' ) ) ) )
			)
		);
	}

	private function assertIsSkipped( Property $property ): void {
		$this->assertSame(
			[],
			$this->getUseCasePresentedValueForPropsAndItems( [ $property ], [] )
		);
	}

	public function testPropertyHasNoElementOfStatementIsSkipped(): void {
		$property = $this->newMinimalValidGndProperty();

		$property->setStatements( new StatementList() );

		$this->assertIsSkipped( $property );
	}

	public function testMinimalPropertyWithNoOptionalStatementsIsValid(): void {
		$fields = $this->getUseCasePresentedValueForPropsAndItems(
			[ $this->newMinimalValidGndProperty() ],
			[]
		);

		$this->assertSame( 'P4242', $fields[0]->id );
		$this->assertSame( '', $fields[0]->label );
		$this->assertSame( '', $fields[0]->description );
		$this->assertSame( [], $fields[0]->aliases );
		$this->assertSame( '', $fields[0]->definition );
		$this->assertSame( [], $fields[0]->codings );
	}

}
