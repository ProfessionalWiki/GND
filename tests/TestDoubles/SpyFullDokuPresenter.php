<?php

declare( strict_types = 1 );

namespace DNB\GND\Tests\TestDoubles;

use DNB\GND\Domain\Doku\GndField;
use DNB\GND\UseCases\ShowFullDoku\FullDokuPresenter;

class SpyFullDokuPresenter implements FullDokuPresenter {

	private array $gndFields;

	public function present( array $gndFields ): void {
		$this->gndFields = $gndFields;
	}

	/**
	 * @return GndField[]
	 */
	public function getFields(): array {
		return $this->gndFields;
	}

}
