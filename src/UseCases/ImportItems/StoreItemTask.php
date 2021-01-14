<?php

declare( strict_types = 1 );

namespace DNB\GND\UseCases\ImportItems;

use Amp\Parallel\Worker\Environment;
use Amp\Parallel\Worker\Task;
use DNB\GND\Domain\ItemStore;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;

class StoreItemTask implements Task {

	private ItemId $item;

	public function __construct( ItemId $item ) {
		$this->item = $item;
	}

	public function run( Environment $environment ) {
		sleep( 5 );
		return $this->item->getSerialization();
	}

}
