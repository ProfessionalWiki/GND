<?php

declare( strict_types = 1 );

namespace DNB\GND;

use DNB\GND\Adapters\Presentation\ParserFunctionDokuPresenter;
use DNB\GND\UseCases\GetGndDoku\GetGndDoku;
use Parser;

final class GndHooks {

	public static function onExtensionRegistration(): void {

	}

	public static function onParserFirstCallInit( Parser $parser ): void {
		$parser->setFunctionHook(
			'gnd_doku',
			function( Parser $parser, string ...$parameters ) {
				$presenter = new ParserFunctionDokuPresenter();

				$useCase = new GetGndDoku( $presenter );
				$useCase->showGndDoku();

				return $presenter->getParserFunctionReturnValue();
			}
		);
	}

}
