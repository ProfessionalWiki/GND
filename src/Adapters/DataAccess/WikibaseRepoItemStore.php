<?php

declare( strict_types = 1 );

namespace DNB\GND\Adapters\DataAccess;

use DNB\GND\Domain\ItemStore;
use Exception;
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
		try {
			$this->entityStore->saveEntity(
				$item,
				'test summary ' . $item->getId(),
				$this->user,
				$item->getId() === null ? EDIT_NEW : EDIT_UPDATE
			);
		} catch ( Exception $ex ) {
			throw new \RuntimeException( $item->getId()->getSerialization(), 0, $ex );
		}
	}

}
