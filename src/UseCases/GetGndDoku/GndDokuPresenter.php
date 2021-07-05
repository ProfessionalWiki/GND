<?php

declare( strict_types = 1 );

namespace DNB\GND\UseCases\GetGndDoku;

interface GndDokuPresenter {

	/**
	 * @return mixed[]|string
	 */
	public function getParserFunctionReturnValue();

	public function showGndDoku( FieldDoku ...$fieldDocs ): void;

	public function showErrorMessage( string $error ): void;

}
