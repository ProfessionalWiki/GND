<?php

declare( strict_types = 1 );

namespace DNB\GND\Adapters\Presentation;

use DNB\GND\Domain\Doku\GndField;
use DNB\GND\UseCases\ShowFullDoku\FullDokuPresenter;

class ApiFullDokuPresenter implements FullDokuPresenter {

	/**
	 * @var array<int, GndField>
	 */
	private array $gndFields;

	/**
	 * @param array<int, GndField> $gndFields
	 */
	public function present( array $gndFields ): void {
		$this->gndFields = $gndFields;
	}

	public function getArray(): array {
		return [
			'fields' => ( new GndDokuSerializer() )->fieldsToArrays( ...$this->gndFields )
		];
	}

}
