<?php

declare( strict_types = 1 );

namespace DNB\GND\Domain;

use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;

class PropertyCollection {

	/**
	 * @var array<string, Property>
	 */
	private array $properties = [];

	public function __construct( Property ...$properties ) {
		foreach ( $properties as $property ) {
			$this->properties[$property->getId()->serialize()] = $property;
		}
	}

	/**
	 * @return array<string, Property>
	 */
	public function asArray(): array {
		return $this->properties;
	}

	public function getById( PropertyId $id ): Property {
		return $this->properties[$id->serialize()];
	}

	public function hasId( PropertyId $id ) {
		return array_key_exists( $id->serialize(), $this->properties );
	}

}
