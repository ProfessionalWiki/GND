<?php

declare( strict_types = 1 );

namespace DNB\GND;

use DNB\GND\Adapters\DataAccess\NetworkSparqlQueryDispatcher;
use DNB\GND\Adapters\Presentation\ParserFunctionDokuPresenter;
use DNB\GND\UseCases\GetGndDoku\GetGndDoku;
use Parser;

final class GndHooks {

	public const DOKU_SPARQL_ENDPOINT = 'https://doku.wikibase.wiki/query/proxy/wdqs/bigdata/namespace/wdq/sparql';

	public static function onExtensionRegistration(): void {

	}

	public static function onParserFirstCallInit( Parser $parser ): void {
		$parser->setFunctionHook(
			'gnd_doku',
			function( Parser $parser, string ...$parameters ) {
				$presenter = new ParserFunctionDokuPresenter();

				$useCase = new GetGndDoku(
					$presenter,
					new NetworkSparqlQueryDispatcher( self::DOKU_SPARQL_ENDPOINT )
				);

				$parameters = self::parserArgumentsToKeyValuePairs( $parameters );

				$useCase->showGndDoku(
					$parameters['language'] ?? null,
					explode( ',', $parameters['codings'] ?? '' )
				);

				$parser->getOutput()->addModules( 'ext.gnd' );

				return $presenter->getParserFunctionReturnValue();
			}
		);
	}

	private static function parserArgumentsToKeyValuePairs( array $arguments ): array {
		$pairs = [];

		foreach ( $arguments as $argument ) {
			if ( false !== strpos( $argument, '=' ) ) {
				[$key, $value] = explode( '=', $argument );
				$pairs[trim($key)] = trim($value);
			}
		}

		return $pairs;
	}

}
