<?php

declare( strict_types = 1 );

namespace DNB\GND\UseCases\GetGndDoku;

class GetGndDoku {

	private GndDokuPresenter $presenter;

	public function __construct( GndDokuPresenter $presenter ) {
		$this->presenter = $presenter;
	}

	public function showGndDoku(): void {
		$this->presenter->showErrorMessage( 'my error' );
	}

}
