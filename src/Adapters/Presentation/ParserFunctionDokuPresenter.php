<?php

declare( strict_types = 1 );

namespace DNB\GND\Adapters\Presentation;

use DNB\GND\UseCases\GetGndDoku\FieldDoku;
use DNB\GND\UseCases\GetGndDoku\GndDokuPresenter;

class ParserFunctionDokuPresenter implements GndDokuPresenter {

	/**
	 * @var mixed[]|string
	 */
	private $parserFunctionReturnValue = '';

	public function getParserFunctionReturnValue() {
		return $this->parserFunctionReturnValue;
	}

	public function showGndDoku( FieldDoku ...$fieldDocs ): void {
		$this->parserFunctionReturnValue = [
			'noparse' => true,
			'isHTML' => true,
			'pewpew' // TODO
		];
	}

	public function showErrorMessage( string $error ): void {
		$this->parserFunctionReturnValue = $error;
	}

}
