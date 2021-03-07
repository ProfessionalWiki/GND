<?php

declare( strict_types = 1 );

namespace DNB\GND\Tests\Unit\UseCases\ImportItems;

use DNB\GND\Adapters\DataAccess\InMemoryEntitySaver;
use DNB\GND\Adapters\DataAccess\InMemoryItemSource;
use DNB\GND\UseCases\ImportItems\ImportEntities;
use DNB\GND\UseCases\ImportItems\ImportEntitiesPresenter;
use DNB\GND\UseCases\ImportItems\ImportStats;
use PHPUnit\Framework\TestCase;
use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;

/**
 * @covers \DNB\GND\UseCases\ImportItems\ImportEntities
 * @covers \DNB\GND\Adapters\DataAccess\InMemoryItemSource
 * @covers \DNB\GND\Adapters\DataAccess\InMemoryEntitySaver
 */
class ImportItemsTest extends TestCase {

	private InMemoryItemSource $itemSource;
	private InMemoryEntitySaver $store;
	private ImportEntitiesPresenter $presenter;

	public function setUp(): void {
		$this->itemSource = new InMemoryItemSource();
		$this->store = new InMemoryEntitySaver();
		$this->presenter = new class() implements ImportEntitiesPresenter {
			public array $stored = [];
			public array $failed = [];
			public ImportStats $stats;

			public function presentStorageStarted( EntityDocument $entity ): void {
			}

			public function presentStorageSucceeded( EntityDocument $entity ): void {
				$this->stored[] = $entity->getId()->getSerialization();
			}

			public function presentStorageFailed( EntityDocument $entity, \Exception $exception ): void {
				$this->failed[] = $entity->getId()->getSerialization();
			}

			public function presentImportFinished( ImportStats $stats ): void {
				$this->stats = $stats;
			}
		};
	}

	private function newUseCase(): ImportEntities {
		return new ImportEntities(
			$this->itemSource,
			$this->store,
			$this->presenter
		);
	}

	public function testNoItems() {
		$this->newUseCase()->import();

		$this->assertSame( [], $this->store->getEntities() );
	}

	public function testSomeItems() {
		$firstItem = new Item( new ItemId( 'Q23' ) );
		$secondItem = new Item( new ItemId( 'Q42' ) );

		$this->itemSource = new InMemoryItemSource( $firstItem, $secondItem );

		$this->newUseCase()->import();

		$this->assertEquals(
			[ $firstItem, $secondItem ],
			$this->store->getEntities()
		);
	}

	public function testHandlesStorageException() {
		$this->itemSource = new InMemoryItemSource(
			new Item( new ItemId( 'Q1' ) ),
			new Item( new ItemId( 'Q2' ) ),
			new Item( new ItemId( 'Q3' ) )
		);
		$this->store->throwOnId( 'Q2' );

		$this->newUseCase()->import();

		$this->assertSame( [ 'Q1', 'Q3' ], $this->presenter->stored );
		$this->assertSame( [ 'Q2' ], $this->presenter->failed );
	}

	public function testStats() {
		$this->itemSource = new InMemoryItemSource(
			new Item( new ItemId( 'Q1' ) ),
			new Item( new ItemId( 'Q2' ) ),
			new Item( new ItemId( 'Q3' ) )
		);
		$this->store->throwOnId( 'Q2' );

		$this->newUseCase()->import();

		$this->assertSame( 3, $this->presenter->stats->getItemCount() );
		$this->assertSame( 1, $this->presenter->stats->getFailureCount() );
	}

}
