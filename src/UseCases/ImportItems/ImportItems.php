<?php

declare( strict_types = 1 );

namespace DNB\GND\UseCases\ImportItems;

use Amp\Loop;
use Amp\Parallel\Worker\CallableTask;
use Amp\Parallel\Worker\DefaultPool;
use Amp\Parallel\Worker\Environment;
use Amp\Parallel\Worker\Task;
use DNB\GND\Domain\ItemSource;
use DNB\GND\Domain\ItemStore;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use function Amp\call;
use function Amp\Promise\all;

class ImportItems {

	private ItemSource $itemSource;
	private ItemStore $store;
	private ImportItemsPresenter $presenter;

	public function __construct( ItemSource $itemSource, ItemStore $store, ImportItemsPresenter $presenter ) {
		$this->itemSource = $itemSource;
		$this->store = $store;
		$this->presenter = $presenter;
	}

	private function newTask( ItemStore $itemStore, Item $item ): Task {
		return new class( $itemStore, $item ) implements Task {

			private ItemStore $itemStore;
			private Item $item;

			public function __construct( ItemStore $itemStore, Item $item ) {
				$this->itemStore = $itemStore;
				$this->item = $item;
			}

			public function run( Environment $environment ) {
				sleep( 5 );
				echo $this->item->getId()->getSerialization();
			}

		};
	}

	public static function storeItem( ItemId $id ) {
		return $id->getSerialization();
	}

	public function import(): void {
		$stats = new ImportStats();
		$stats->recordStart();

		$tasks = [
			new CallableTask([ self::class, 'storeItem' ], [ new ItemId( 'Q42' ) ] ),
			new CallableTask([ self::class, 'storeItem' ], [ new ItemId( 'Q43' ) ] ),
		];

		Loop::run(function () use (&$results, $tasks) {
			$timer = Loop::repeat(200, function () {
				\printf(".");
			});
			Loop::unreference($timer);

			$pool = new DefaultPool();

			$coroutines = [];

			foreach ($tasks as $index => $task) {
				$coroutines[] = call(function () use ($pool, $index, $task) {
					$result = yield $pool->enqueue($task);
					\printf("\nRead from task %d: %d bytes\n", $index, \strlen($result));
					return $result;
				});
			}

			$results = yield all($coroutines);

			return yield $pool->shutdown();
		});











		exit;

		while ( true ) {
			$item = $this->itemSource->nextItem();

			if ( $item === null ) {
				break;
			}

			$this->presenter->presentStorageStarted( $item );

			try {
				$this->store->storeItem( $item );
			} catch ( \Exception $exception ) {
				$stats->recordFailure();
				$this->presenter->presentStorageFailed( $item, $exception );
				continue;
			}

			$stats->recordSuccess();
			$this->presenter->presentStorageSucceeded( $item );
		}

		$this->presenter->presentImportFinished( $stats );
	}

}
