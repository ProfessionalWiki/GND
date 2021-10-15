<?php

declare( strict_types = 1 );

namespace DNB\GND\Domain;

class GndSubfield {

	public string $label;
	public string $description;

	/**
	 * @var array<string, string> Example: [ "Q17" => "Person" ]
	 */
	public array $possibleValues;

	/**
	 * @var array<string, string> Example: [ "PICA+" => "$0 Position 2" ]
	 */
	public array $codings;

}
