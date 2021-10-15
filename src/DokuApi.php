<?php

declare( strict_types = 1 );

namespace DNB\GND;

use MediaWiki\Rest\SimpleHandler;
use Wikimedia\ParamValidator\ParamValidator;

class DokuApi extends SimpleHandler {

	public static function factory(): self {
		return new self();
	}

	public function run( string $propertyId = null ): array {
		return [ 'ids' => $propertyId ?? 'all' ];
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