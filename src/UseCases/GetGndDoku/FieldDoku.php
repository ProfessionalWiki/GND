<?php

declare( strict_types = 1 );

namespace DNB\GND\UseCases\GetGndDoku;

class FieldDoku {

	/**
	 * @param array<string, string> $fieldCodes Example: ['PICA3' => '005', 'PICA+' => '002@']
	 * @param SubfieldDoku[] $subfields
	 */
	public function __construct( string $label, string $url, array $fieldCodes, array $subfields ) {
	}

}
