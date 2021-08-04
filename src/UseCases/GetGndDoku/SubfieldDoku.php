<?php

declare( strict_types = 1 );

namespace DNB\GND\UseCases\GetGndDoku;

class SubfieldDoku {

	private string $description;
	private string $label;
	private array $possibleValues;
	private array $subfieldCodes;

	/**
	 * @param array<string, string> $possibleValues [URI => value]
	 * @param $subfieldCodes array<string, string> $fieldCodes Example:
	 * 		['https://doku.wikibase.wiki/entity/Q1316' => '-ohne- Position 2', 'https://doku.wikibase.wiki/entity/Q1317' => '0 Position 2']
	 */
	public function __construct( string $description, string $label, array $possibleValues, array $subfieldCodes ) {
		$this->description = $description;
		$this->label = $label;
		$this->possibleValues = $possibleValues;
		$this->subfieldCodes = $subfieldCodes;
	}

	public function getDescription(): string {
		return $this->description;
	}

	public function getLabel(): string {
		return $this->label;
	}

	/**
	 * @return array<string, string>
	 */
	public function getPossibleValues(): array {
		return $this->possibleValues;
	}

	/**
	 * @return array<string, string>
	 */
	public function getSubfieldCodes(): array {
		return $this->subfieldCodes;
	}

}
