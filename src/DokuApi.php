<?php

declare( strict_types = 1 );

namespace DNB\GND;

use DNB\GND\Adapters\Presentation\ApiFullDokuPresenter;
use DNB\GND\UseCases\ShowFullDoku\FullDokuPresenter;
use DNB\GND\UseCases\ShowFullDoku\ShowFullDoku;
use MediaWiki\Rest\SimpleHandler;
use Wikimedia\ParamValidator\ParamValidator;

class DokuApi extends SimpleHandler {

	public static function factory(): self {
		return new self();
	}

	public function run( string $propertyId = null ): array {
		$presenter = new ApiFullDokuPresenter();

		$this->newUseCase( $presenter )->showFullDoku( 'de' ); // TODO: inject lang code

		return $presenter->getArray();
	}

	private function newUseCase( FullDokuPresenter $presenter ): ShowFullDoku {
		$servicesFactory = GndServicesFactory::getInstance();

		return new ShowFullDoku(
			$presenter,
			$servicesFactory->getPropertyCollectionLookup(),
			$servicesFactory->getItemLookup()
		);
	}

	public function needsWriteAccess(): bool {
		return false;
	}

	public function getParamSettings(): array {
		return [
			'property_id' => [
				self::PARAM_SOURCE => 'path',
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => false,
			],
		];
	}

}
