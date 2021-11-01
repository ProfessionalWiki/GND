<?php

declare( strict_types = 1 );

namespace DNB\GND\Tests\Unit\UseCases\ShowFullDoku;

use DataValues\BooleanValue;
use DataValues\StringValue;
use DNB\GND\Domain\Doku\GndField;
use DNB\GND\Domain\Doku\GndSubfield;
use DNB\GND\Domain\Doku\GndReference;
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
use Wikibase\DataModel\Reference;
use Wikibase\DataModel\ReferenceList;
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
 * @covers \DNB\GND\UseCases\ShowFullDoku\NiceSnaks
 * @covers \DNB\GND\UseCases\ShowFullDoku\NiceStatement
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
					new Statement(
						new PropertyValueSnak( new PropertyId( 'P4' ), new StringValue( 'unknown coding type' ) ),
						new SnakList( [
							new PropertyValueSnak( new PropertyId( 'P3' ), new EntityIdValue( new ItemId( 'Q404' ) ) )
						] )
					),

					new Statement(
						new PropertyValueSnak( new PropertyId( 'P15' ), new EntityIdValue( new PropertyId( 'P41' ) ) ),
						new SnakList( [
							new PropertyValueSnak( new PropertyId( 'P12' ), new BooleanValue( false ) ),
							new PropertyValueSnak( new PropertyId( 'P7' ), new StringValue( 'Hinweis zur Erfassung von Personen' ) ),
						] ),
						new ReferenceList( [
							new Reference( [
								new PropertyValueSnak( new PropertyId( 'P7' ), new StringValue( 'EH-P-15: Notnamen' ) ),
								new PropertyValueSnak( new PropertyId( 'P371' ), new StringValue( 'https://wiki.dnb.de/download/attachments/90411361/EH-P-15.pdf' ) ),
							] ),
							new Reference( [
								new PropertyValueSnak( new PropertyId( 'P7' ), new StringValue( 'Example with no URL' ) ),
							] ),
							new Reference( [
								new PropertyValueSnak( new PropertyId( 'P100000' ), new StringValue( 'Missing P7' ) ),
							] )
						] )
					),
					new Statement(
						new PropertyValueSnak( new PropertyId( 'P15' ), new EntityIdValue( new PropertyId( 'P21' ) ) ),
						new SnakList( [
							new PropertyValueSnak( new PropertyId( 'P12' ), new BooleanValue( true ) ),
						] )
					),
					new Statement(
						new PropertyValueSnak( new PropertyId( 'P15' ), new EntityIdValue( new PropertyId( 'P404' ) ) ),

						new SnakList( [
							new PropertyValueSnak( new PropertyId( 'P12' ), new StringValue( 'Nein' ) ),
						] )
					),
					new Statement(
						new PropertyValueSnak( new PropertyId( 'P15' ), new EntityIdValue( new PropertyId( 'P61' ) ) ),
						new SnakList( [
							new PropertyValueSnak( new PropertyId( 'P8' ), new EntityIdValue( new ItemId( 'Q17' ) ) ),
							new PropertyValueSnak( new PropertyId( 'P8' ), new EntityIdValue( new ItemId( 'Q151' ) ) ),
							new PropertyValueSnak( new PropertyId( 'P8' ), new EntityIdValue( new ItemId( 'Q404' ) ) ),

							new PropertyValueSnak( new PropertyId( 'P12' ), new StringValue( 'Ja' ) ),
						] )
					),

					new Statement( new PropertyValueSnak( new PropertyId( 'P9' ), new StringValue( 'Das Feld ist für die Satzart Personen ...' ) ) ),
					new Statement( new PropertyValueSnak( new PropertyId( 'P9' ), new StringValue( 'Im Feld muss mindestens das Unterfeld ...' ) ) ),

					new Statement( new PropertyValueSnak( new PropertyId( 'P10' ), new StringValue( 'Der bevorzugte Name einer Person setzt ...' ) ) ),
					new Statement( new PropertyValueSnak( new PropertyId( 'P10' ), new StringValue( 'Das Unterfeld für unterscheidende Zusätze ...' ) ) ),

					new Statement( new PropertyValueSnak( new PropertyId( 'P11' ), new EntityIdValue( new ItemId( 'Q262' ) ) ) ),
					new Statement( new PropertyValueSnak( new PropertyId( 'P11' ), new EntityIdValue( new ItemId( 'Q1330' ) ) ) ),
					new Statement( new PropertyValueSnak( new PropertyId( 'P11' ), new EntityIdValue( new ItemId( 'Q404' ) ) ) ),
				)
			),

			new Property(
				new PropertyId( 'P41' ),
				new Fingerprint(
					new TermList( [
						new Term( self::LANG_CODE, 'Persönlicher Name' ),
					] ),
				),
				'string',
				new StatementList(
					new Statement(
						new PropertyValueSnak( new PropertyId( 'P4' ), new StringValue( '$P' ) ),
						new SnakList( [
							new PropertyValueSnak( new PropertyId( 'P3' ), new EntityIdValue( new ItemId( 'Q1317' ) ) )
						] )
					),
					new Statement(
						new PropertyValueSnak( new PropertyId( 'P4' ), new StringValue( '$a' ) ),
						new SnakList( [
							new PropertyValueSnak( new PropertyId( 'P3' ), new EntityIdValue( new ItemId( 'Q1320' ) ) )
						] )
					),
					new Statement(
						new PropertyValueSnak( new PropertyId( 'P4' ), new StringValue( '$P' ) ),
						new SnakList( [
							new PropertyValueSnak( new PropertyId( 'P3' ), new EntityIdValue( new ItemId( 'Q1316' ) ) )
						] )
					),
				)
			),
			new Property(
				new PropertyId( 'P21' ),
				new Fingerprint(
					new TermList( [
						new Term( 'WRONG', 'Surname' ),
					] ),
				),
				'string',
			)
		];

		$items = [
			new Item(
				new ItemId( 'Q262' ),
				new Fingerprint(
					new TermList( [
						new Term( 'wrong', 'Not Sarah' ),
						new Term( self::LANG_CODE, 'Sarah Hartmann' ),
					] )
				)
			),
			new Item(
				new ItemId( 'Q1330' ),
				new Fingerprint(
					new TermList( [
						new Term( self::LANG_CODE, 'Hadley Hemingway Mowrer' ),
					] )
				)
			),
			new Item(
				new ItemId( 'Q17' ),
				new Fingerprint(
					new TermList( [
						new Term( self::LANG_CODE, 'Person' ),
					] )
				)
			),
			new Item(
				new ItemId( 'Q151' ),
				new Fingerprint(
					new TermList( [
						new Term( 'wrong', 'Corporate body' ),
					] )
				)
			)
		];

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
		$gndField->isRepeatable = false;
		$gndField->subfields = [
			new GndSubfield(
				"P41",
				"Persönlicher Name",
				"Hinweis zur Erfassung von Personen",
				[
					'PICA+' => '$P',
					'MARC 21' => '$a',
					'PICA3' => '$P',
				],
				[],
				[
					new GndReference( 'EH-P-15: Notnamen', 'https://wiki.dnb.de/download/attachments/90411361/EH-P-15.pdf' ),
					new GndReference( 'Example with no URL', null ),
				],
				false
			),
			new GndSubfield(
				"P21",
				"P21",
				"",
				[],
				[],
				[],
				true
			),
			new GndSubfield(
				"P404",
				"P404",
				"",
				[],
				[],
				[],
				false
			),
			new GndSubfield(
				"P61",
				"P61",
				"",
				[
				],
				[
					'Q17' => 'Person',
					'Q151' => 'Q151',
					'Q404' => 'Q404',
				],
				[],
				true
			)
		];

		$gndField->validation = [
			'Das Feld ist für die Satzart Personen ...',
			'Im Feld muss mindestens das Unterfeld ...',
		];

		$gndField->rulesOfUse = [
			'Der bevorzugte Name einer Person setzt ...',
			'Das Unterfeld für unterscheidende Zusätze ...',
		];

		$gndField->examples = [
			'Q262' => 'Sarah Hartmann',
			'Q1330' => 'Hadley Hemingway Mowrer',
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
		$this->assertFalse( $fields[0]->isRepeatable );
		$this->assertSame( [], $fields[0]->subfields );
		$this->assertSame( [], $fields[0]->validation );
		$this->assertSame( [], $fields[0]->rulesOfUse );
		$this->assertSame( [], $fields[0]->examples );
	}

}
