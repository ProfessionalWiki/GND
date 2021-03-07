<?php

declare( strict_types = 1 );

namespace DNB\GND\Domain;

use RuntimeException;
use Wikibase\DataModel\Entity\EntityDocument;

interface EntitySaver {

	/**
	 * Creates or updates the provided entity.
	 * In case of creation, a newly generated EntityId gets assigned to the entity instance.
	 *
	 * @param EntityDocument $entity
	 *
	 * @throws RuntimeException
	 */
	public function storeEntity( EntityDocument $entity ): void;

}
