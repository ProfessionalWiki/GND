<?php

declare( strict_types = 1 );

namespace DNB\GND\UseCases\GetGndDoku;

class SubfieldDoku {

	private string $description;
	private array $options;

	/**
	 * @param array<string, string> $possibleValues [URI => value]
	 */
	public function __construct( string $description, array $possibleValues ) {
		$this->description = $description;
		$this->options = $possibleValues;
	}

	public function getDescription(): string {
		return $this->description;
	}

	/**
	 * @return array<string, string>
	 */
	public function getPossibleValues(): array {
		return $this->options;
	}

}
