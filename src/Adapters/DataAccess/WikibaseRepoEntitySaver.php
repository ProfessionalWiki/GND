<?php

declare( strict_types = 1 );

namespace DNB\GND\Adapters\DataAccess;

use DNB\GND\Domain\EntitySaver;
use Exception;
use RuntimeException;
use User;
use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\Lib\Store\EntityStore;

class WikibaseRepoEntitySaver implements EntitySaver {

	private EntityStore $entityStore;
	private User $user;

	public function __construct( EntityStore $entityStore, User $user ) {
		$this->entityStore = $entityStore;
		$this->user = $user;
	}

	public function storeEntity( EntityDocument $entity ): void {
		if ( $entity->getId() === null ) {
			throw new RuntimeException( 'Cannot store items that do not have an ID' );
		}

		try {
			$this->entityStore->saveEntity(
				$entity,
				'test summary ' . $entity->getId(),
				$this->user,
			);
		} catch ( Exception $ex ) {
			throw new RuntimeException(
				'Could not save '  . $entity->getId()->getSerialization() . '. ' .  $ex->getMessage(),
				0,
				$ex
			);
		}
	}

}
