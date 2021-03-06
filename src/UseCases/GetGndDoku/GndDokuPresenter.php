<?php

declare( strict_types = 1 );

namespace DNB\GND\UseCases\GetGndDoku;

interface GndDokuPresenter {

	public function showGndDoku( FieldDoku ...$fieldDocs ): void;

	public function showErrorMessage( string $error ): void;

}
