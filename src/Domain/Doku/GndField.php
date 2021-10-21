<?php

declare( strict_types = 1 );

namespace DNB\GND\Domain\Doku;

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
	 * @var array<int, GndSubfield> Example: [ $gndSubfield1, $gndSubfield2 ]
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
