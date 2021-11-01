<?php

declare( strict_types = 1 );

namespace DNB\GND\UseCases\ShowFullDoku;

use DataValues\DataValue;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Snak\SnakList;

/**
 * Wrapper around SnakList that provides convenient generic accessor methods.
 */
class Snaks {

	private SnakList $snaks;

	public function __construct( SnakList $snaks ) {
		$this->snaks = $snaks;
	}

	/**
	 * @return array<int, DataValue>
	 */
	public function getAllValuesForPropertyId( PropertyId $id ): array {
		$values = [];

		foreach ( $this->snaks as $snak ) {
			if ( $snak instanceof PropertyValueSnak ) {
				if ( $snak->getPropertyId()->equals( $id ) ) {
					$values[] = $snak->getDataValue();
				}
			}
		}

		return $values;
	}

	/**
	 * @return array<string, DataValue>
	 */
	public function getLastValueByPropertyId(): array {
		$valuesById = [];

		foreach ( $this->snaks as $snak ) {
			if ( $snak instanceof PropertyValueSnak ) {
				$valuesById[$snak->getPropertyId()->getSerialization()] = $snak->getDataValue();
			}
		}

		return $valuesById;
	}

}
