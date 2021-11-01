<?php

declare( strict_types = 1 );

namespace DNB\GND\UseCases\ShowFullDoku;

use DataValues\DataValue;
use DataValues\StringValue;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Statement\Statement;

/**
 * Wrapper around Statement that provides convenient generic accessor methods.
 */
class NiceStatement {

	private Statement $statement;

	public function __construct( Statement $statement ) {
		$this->statement = $statement;
	}

	public function getQualifierValue( string $propertyId ): ?DataValue {
		foreach ( $this->statement->getQualifiers() as $qualifier ) {
			if ( $qualifier instanceof PropertyValueSnak ) {
				if ( $qualifier->getPropertyId()->equals( new PropertyId( $propertyId ) ) ) {
					return $qualifier->getDataValue();
				}
			}
		}

		return null;
	}

	public function getQualifierStringValue( string $propertyId ): ?string {
		$dataValue = $this->getQualifierValue( $propertyId );

		if ( $dataValue instanceof StringValue ) {
			return $dataValue->getValue();
		}

		return null;
	}

}
