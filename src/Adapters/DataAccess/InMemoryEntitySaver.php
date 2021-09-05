<?php

declare( strict_types = 1 );

namespace DNB\GND\Adapters\DataAccess;

use DNB\GND\Domain\EntitySaver;
use RuntimeException;
use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;

class InMemoryEntitySaver implements EntitySaver {

	private array $entities = [];
	private array $idsToThrowOn = [];
	private int $nextItemId = 1000;
	private int $nextPropertyId = 100;

	public function __construct( EntityDocument ...$entities ) {
		foreach ( $entities as $item ) {
			$this->storeEntity( $item );
		}
	}

	public function storeEntity( EntityDocument $entity ): void {
		if ( $entity->getId() === null ) {
			$entity->setId( $this->newId( $entity->getType() ) );
		}
		else if ( in_array( $entity->getId()->getSerialization(), $this->idsToThrowOn ) ) {
			throw new RuntimeException( $entity->getId()->getSerialization() );
		}

		$this->entities[$entity->getId()->getSerialization()] = $entity;
	}

	private function newId( string $type ): EntityId {
		if ( $type === 'item' ) {
			return ItemId::newFromNumber( $this->nextItemId++ );
		}
		if ( $type === 'property' ) {
			return PropertyId::newFromNumber( $this->nextPropertyId++ );
		}
		throw new \InvalidArgumentException();
	}

	/**
	 * @return EntityDocument[]
	 */
	public function getEntities(): array {
		return array_values( $this->entities );
	}

	public function throwOnId( string $itemId ): void {
		$this->idsToThrowOn[] = $itemId;
	}

	public function getItemById( ItemId $id ): Item {
		return $this->entities[$id->getSerialization()];
	}

	public function getPropertyById( PropertyId $id ): Property {
		return $this->entities[$id->getSerialization()];
	}

}
