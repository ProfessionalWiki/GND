<?php

declare( strict_types = 1 );

namespace DNB\GND\Tests\Unit\UseCases\ImportItems;

use DNB\GND\Adapters\DataAccess\InMemoryItemSource;
use DNB\GND\Adapters\DataAccess\InMemoryItemStore;
use DNB\GND\UseCases\ImportItems\ImportItems;
use DNB\GND\UseCases\ImportItems\ImportItemsPresenter;
use PHPUnit\Framework\TestCase;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;

/**
 * @covers \DNB\GND\UseCases\ImportItems\ImportItems
 * @covers \DNB\GND\Adapters\DataAccess\InMemoryItemSource
 * @covers \DNB\GND\Adapters\DataAccess\InMemoryItemStore
 */
class ImportItemsTest extends TestCase {

	private InMemoryItemSource $itemSource;
	private InMemoryItemStore $store;
	private ImportItemsPresenter $presenter;

	public function setUp(): void {
		$this->itemSource = new InMemoryItemSource();
		$this->store = new InMemoryItemStore();
		$this->presenter = new class() implements ImportItemsPresenter {
			public function presentStartStoring( Item $item ): void {
			}

			public function presentDoneStoring( Item $item ): void {
			}

			public function presentImportStarted(): void {
			}

			public function presentImportFinished(): void {
			}
		};
	}

	private function newUseCase(): ImportItems {
		return new ImportItems(
			$this->itemSource,
			$this->store,
			$this->presenter
		);
	}

	public function testNoItems() {
		$this->newUseCase()->import();

		$this->assertSame( [], $this->store->getItems() );
	}

	public function testSomeItems() {
		$firstItem = new Item( new ItemId( 'Q23' ) );
		$secondItem = new Item( new ItemId( 'Q42' ) );

		$this->itemSource = new InMemoryItemSource( $firstItem, $secondItem );

		$this->newUseCase()->import();

		$this->assertEquals(
			[ $firstItem, $secondItem ],
			$this->store->getItems()
		);
	}

}
