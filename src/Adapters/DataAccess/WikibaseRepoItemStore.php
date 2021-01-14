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
		if ( $item->getId() === null ) {
			throw new \RuntimeException( 'Cannot store items that do not have an ID' );
		}

		try {
			$this->entityStore->saveEntity(
				$item,
				'test summary ' . $item->getId(), // TODO
				$this->user
			);
		} catch ( Exception $ex ) {
			throw new \RuntimeException(
				'Could not save '  . $item->getId()->getSerialization() . '. ' .  $ex->getMessage(),
				0,
				$ex
			);
		}
	}

}
