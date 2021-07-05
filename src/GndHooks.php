<?php

declare( strict_types = 1 );

namespace DNB\GND;

use Parser;

final class GndHooks {

	public static function onExtensionRegistration(): void {

	}

	public static function onParserFirstCallInit( Parser $parser ): void {
		$parser->setFunctionHook( 'gnd_doku', [ self::class, 'spikySpike' ] );
	}

	/**
	 * @param Parser $parser
	 * @param string ...$parameters
	 *
	 * @return mixed[]|string
	 */
	public static function spikySpike( Parser $parser, string ...$parameters ) {
		return 'hi there';
	}

}
