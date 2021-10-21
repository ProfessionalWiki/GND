<?php

declare( strict_types = 1 );

namespace DNB\GND\UseCases\ShowFullDoku;

use DNB\GND\Domain\Doku\GndField;

interface FullDokuPresenter {

	/**
	 * @param GndField[] $gndFields
	 */
	public function present( array $gndFields ): void;

}
