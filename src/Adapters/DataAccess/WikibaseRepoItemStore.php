<?php

declare( strict_types = 1 );

namespace DNB\GND\Adapters\DataAccess;

use DNB\GND\Domain\ItemStore;
use User;
use Wikibase\DataModel\Entity\Item;
use Wikibase\Lib\Store\EntityStore;

class WikibaseRepoItemStore implements ItemStore {

	private EntityStore $entityStore;
	private User $user;

	public function __construct( EntityStore $entitySaver, User $user ) {
		$this->entityStore = $entitySaver;
		$this->user = $user;
	}

	public function storeItem( Item $item ): void {
		$this->entityStore->saveEntity(
			$item,
			'test summary',
			$this->user,
			EDIT_NEW
		);
	}

}
