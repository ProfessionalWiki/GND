<?php

declare( strict_types = 1 );

namespace DNB\GND\UseCases\GetGndDoku;

class FieldDoku {

	private string $label;
	private string $url;
	private array $fieldCodes;
	private array $subfields;

	/**
	 * @param array<string, string> $fieldCodes Example: ['PICA3' => '005', 'PICA+' => '002@']
	 * @param array<int, SubfieldDoku> $subfields
	 */
	public function __construct( string $label, string $url, array $fieldCodes, array $subfields ) {
		$this->label = $label;
		$this->url = $url;
		$this->fieldCodes = $fieldCodes;
		$this->subfields = $subfields;
	}

	public function getLabel(): string {
		return $this->label;
	}

	public function getUrl(): string {
		return $this->url;
	}

	/**
	 * @return array<string, string>
	 */
	public function getFieldCodes(): array {
		return $this->fieldCodes;
	}

	/**
	 * @return array<int, SubfieldDoku>
	 */
	public function getSubfields(): array {
		return $this->subfields;
	}

}
