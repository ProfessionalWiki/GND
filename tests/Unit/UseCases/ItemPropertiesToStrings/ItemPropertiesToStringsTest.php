<?php

declare( strict_types = 1 );

namespace DNB\GND\Tests\Unit\UseCases\ImportItems;

use DataValues\StringValue;
use DNB\GND\Adapters\DataAccess\InMemoryEntitySaver;
use DNB\GND\Adapters\DataAccess\InMemoryItemSource;
use DNB\GND\UseCases\ItemPropertiesToStrings\ItemPropertiesToStrings;
use DNB\GND\UseCases\ItemPropertiesToStrings\PropertyChangePresenter;
use PHPUnit\Framework\TestCase;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Reference;
use Wikibase\DataModel\ReferenceList;
use Wikibase\DataModel\Services\Lookup\InMemoryEntityLookup;
use Wikibase\DataModel\Snak\PropertyNoValueSnak;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Snak\SnakList;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\DataModel\Statement\StatementList;

/**
 * @covers \DNB\GND\UseCases\ItemPropertiesToStrings\ItemPropertiesToStrings
 * @covers \DNB\GND\Adapters\DataAccess\InMemoryItemSource
 * @covers \DNB\GND\Adapters\DataAccess\InMemoryEntitySaver
 */
class ItemPropertiesToStringsTest extends TestCase {

	private InMemoryEntityLookup $propertyLookup;
	private InMemoryEntitySaver $entitySaver;
	private InMemoryItemSource $itemSource;

	public function setUp(): void {
		$this->propertyLookup = new InMemoryEntityLookup();
		$this->entitySaver = new InMemoryEntitySaver();
		$this->itemSource = new InMemoryItemSource();
	}

	private function newUseCase(): ItemPropertiesToStrings {
		return new ItemPropertiesToStrings(
			$this->propertyLookup,
			$this->entitySaver,
			$this->itemSource,
			$this->newNullPresenter()
		);
	}

	private function newNullPresenter(): PropertyChangePresenter {
		return new class () implements PropertyChangePresenter {
			public function presentChangingPropertyType( PropertyId $id, string $oldType, string $newType ) {
			}
			public function presentMigratingItem( ItemId $id ) {
			}
		};
	}

	public function testWhenPropertyHasWrongType_exceptionIsThrown(): void {
		$property = $this->newPropertyWithNonItemType();
		$this->propertyLookup->addEntity( $property );

		$this->expectException( \RuntimeException::class );

		$this->newUseCase()->migrate( $property->getId() );
	}

	private function newPropertyWithNonItemType(): Property {
		return new Property(
			new PropertyId( 'P1' ),
			null,
			 'external-id' // Not 'wikibase-item'
		);
	}

	public function testWhenPropertyHasItemType_itsTypeIsChanged(): void {
		$property = $this->newPropertyWithItemType();
		$this->propertyLookup->addEntity( $property );

		$this->newUseCase()->migrate( $property->getId() );

		$this->assertSame(
			'string',
			$this->entitySaver->getPropertyById( $property->getId() )->getDataTypeId()
		);
	}

	private function newPropertyWithItemType(): Property {
		return new Property(
			new PropertyId( 'P1' ),
			null,
			'wikibase-item'
		);
	}

	public function testStatementMainValuesAreMigrated(): void {
		$itemId = new ItemId( 'Q1' );

		$property = $this->newPropertyWithItemType();
		$this->propertyLookup->addEntity( $property );

		$this->itemSource = new InMemoryItemSource(
			new Item(
				$itemId,
				null,
				null,
				new StatementList(
					new Statement(
						new PropertyValueSnak( $property->getId(), new EntityIdValue( new ItemId( 'Q42' ) ) )
					),
					new Statement(
						new PropertyValueSnak( new PropertyId( 'P404' ), new EntityIdValue( new ItemId( 'Q404' ) ) )
					),
					new Statement(
						new PropertyValueSnak( $property->getId(), new EntityIdValue( new ItemId( 'Q1337' ) ) )
					),
					new Statement(
						new PropertyNoValueSnak( $property->getId() )
					)
				)
			)
		);

		$this->newUseCase()->migrate( $property->getId() );

		$statements = $this->entitySaver->getItemById( $itemId )->getStatements();

		$this->assertEquals(
			[
				new PropertyValueSnak( $property->getId(), new StringValue( 'Q42' ) ),
				new PropertyValueSnak( new PropertyId( 'P404' ), new EntityIdValue( new ItemId( 'Q404' ) ) ),
				new PropertyValueSnak( $property->getId(), new StringValue( 'Q1337' ) ),
				new PropertyNoValueSnak( $property->getId() )
			],
			$statements->getMainSnaks()
		);
	}

	public function testQualifierValuesAreMigrated(): void {
		$itemId = new ItemId( 'Q1' );

		$property = $this->newPropertyWithItemType();
		$this->propertyLookup->addEntity( $property );

		$this->itemSource = new InMemoryItemSource(
			new Item(
				$itemId,
				null,
				null,
				new StatementList(
					new Statement(
						new PropertyNoValueSnak( $property->getId() ),
						new SnakList( [
							new PropertyValueSnak( $property->getId(), new EntityIdValue( new ItemId( 'Q42' ) ) ),
							new PropertyValueSnak( new PropertyId( 'P404' ), new EntityIdValue( new ItemId( 'Q404' ) ) ),
							new PropertyNoValueSnak( $property->getId() )
						] ),
					),
				)
			)
		);

		$this->newUseCase()->migrate( $property->getId() );

		$statements = $this->entitySaver->getItemById( $itemId )->getStatements();

		$this->assertEquals(
			new SnakList( [
				new PropertyValueSnak( $property->getId(), new StringValue( 'Q42' ) ),
				new PropertyValueSnak( new PropertyId( 'P404' ), new EntityIdValue( new ItemId( 'Q404' ) ) ),
				new PropertyNoValueSnak( $property->getId() )
			] ),
			$statements->toArray()[0]->getQualifiers()
		);
	}

	public function testReferencesAreMigrated(): void {
		$itemId = new ItemId( 'Q1' );

		$property = $this->newPropertyWithItemType();
		$this->propertyLookup->addEntity( $property );

		$this->itemSource = new InMemoryItemSource(
			new Item(
				$itemId,
				null,
				null,
				new StatementList(
					new Statement(
						new PropertyNoValueSnak( $property->getId() ),
						null,
						new ReferenceList( [
							new Reference( new SnakList( [
								new PropertyValueSnak( $property->getId(), new EntityIdValue( new ItemId( 'Q42' ) ) ),
								new PropertyValueSnak( new PropertyId( 'P404' ), new EntityIdValue( new ItemId( 'Q404' ) ) ),
							] ) ),
							new Reference( new SnakList( [
								new PropertyNoValueSnak( $property->getId() ),
								new PropertyValueSnak( $property->getId(), new EntityIdValue( new ItemId( 'Q1337' ) ) ),
							] ) )
						] )
					),
				)
			)
		);

		$this->newUseCase()->migrate( $property->getId() );

		$statements = $this->entitySaver->getItemById( $itemId )->getStatements();

		$this->assertTrue( $statements->toArray()[0]->getReferences()->hasReference(
			new Reference( new SnakList( [
				new PropertyValueSnak( $property->getId(), new StringValue( 'Q42' ) ),
				new PropertyValueSnak( new PropertyId( 'P404' ), new EntityIdValue( new ItemId( 'Q404' ) ) ),
			] ) )
		) );

		$this->assertTrue( $statements->toArray()[0]->getReferences()->hasReference(
			new Reference( new SnakList( [
				new PropertyNoValueSnak( $property->getId() ),
				new PropertyValueSnak( $property->getId(), new StringValue( 'Q1337' ) ),
			] ) )
		) );
	}

}
