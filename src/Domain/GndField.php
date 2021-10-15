<?php

declare( strict_types = 1 );

namespace DNB\GND\Domain;

class GndField {

	public string $id;

	public string $label;
	public string $description;

	/**
	 * @var array<int, string>
	 */
	public array $aliases;

	public string $definition;

	/**
	 * @var array<string, string> Example: [ "PICA+" => "028A" ]
	 */
	public array $codings;

	public bool $isRepeatable;

	/**
	 * @var array<string, GndSubfield> Example: [ "P61" => $gndSubfield ]
	 */
	public array $subfields;

	/**
	 * @var array<int, string>
	 */
	public array $validation;

	/**
	 * @var array<int, string>
	 */
	public array $rulesOfUse;

	/**
	 * @var array<string, string> Example: [ "P262" => "Sarah Hartmann" ]
	 */
	public array $examples;

}
