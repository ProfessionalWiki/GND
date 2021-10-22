<?php

declare( strict_types = 1 );

namespace DNB\GND\Domain\Doku;

class GndSubfield {

	private string $id;
	private string $label;
	private string $description;

	/**
	 * @var array<string, string> Example: [ "PICA+" => "$0 Position 2" ]
	 */
	private array $codings;

	/**
	 * @var array<string, string> Example: [ "Q17" => "Person" ]
	 */
	private array $possibleValues;

	/**
	 * @var array<int, GndReference>
	 */
	private array $references;

	/**
	 * @param array<string, string> $codings Example: [ "PICA+" => "$0 Position 2" ]
	 * @param array<string, string> $possibleValues Example: [ "Q17" => "Person" ]
	 * @param array<int, GndReference> $references
	 */
	public function __construct(
		string $id,
		string $label,
		string $description,
		array $codings,
		array $possibleValues,
		array $references
	) {
		$this->id = $id;
		$this->label = $label;
		$this->description = $description;
		$this->codings = $codings;
		$this->possibleValues = $possibleValues;
		$this->references = $references;
	}

	public function getId(): string {
		return $this->id;
	}

	public function getLabel(): string {
		return $this->label;
	}

	public function getDescription(): string {
		return $this->description;
	}

	/**
	 * @return array<string, string> Example: [ "PICA+" => "$0 Position 2" ]
	 */
	public function getCodings(): array {
		return $this->codings;
	}

	/**
	 * @return array<string, string> Example: [ "Q17" => "Person" ]
	 */
	public function getPossibleValues(): array {
		return $this->possibleValues;
	}

	/**
	 * @return GndReference[]
	 */
	public function getReferences(): array {
		return $this->references;
	}

}
