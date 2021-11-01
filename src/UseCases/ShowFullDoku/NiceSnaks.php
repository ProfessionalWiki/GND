<?php

declare( strict_types = 1 );

namespace DNB\GND\UseCases\ShowFullDoku;

use DataValues\DataValue;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Snak\Snak;
use Wikibase\DataModel\Snak\SnakList;

/**
 * Wrapper around SnakList that provides convenient generic accessor methods.
 */
class NiceSnaks {

	/**
	 * @var array<int, Snak>
	 */
	private array $snaks = [];

	/**
	 * @param SnakList|Snak[] $snaks
	 */
	public function __construct( $snaks ) {
		foreach ( $snaks as $snak ) {
			$this->snaks[] = $snak;
		}
	}

	/**
	 * @return array<int, DataValue>
	 */
	public function getDataValuesForPropertyId( PropertyId $id ): array {
		$values = [];

		foreach ( $this->getAllValueSnaks() as $snak ) {
			if ( $snak->getPropertyId()->equals( $id ) ) {
				$values[] = $snak->getDataValue();
			}
		}

		return $values;
	}

	/**
	 * @return array<string, DataValue>
	 */
	public function getLastDataValueByPropertyId(): array {
		$valuesById = [];

		foreach ( $this->getAllValueSnaks() as $snak ) {
			$valuesById[$snak->getPropertyId()->getSerialization()] = $snak->getDataValue();
		}

		return $valuesById;
	}

	/**
	 * @return array<int, DataValue>
	 */
	public function getAllDataValues(): array {
		return array_map(
			fn( PropertyValueSnak $snak ) => $snak->getDataValue(),
			$this->getAllValueSnaks()
		);
	}

	/**
	 * @return array<int, PropertyValueSnak>
	 */
	public function getAllValueSnaks(): array {
		return array_filter(
			$this->snaks,
			fn( Snak $snak ) => $snak instanceof PropertyValueSnak
		);
	}

}
