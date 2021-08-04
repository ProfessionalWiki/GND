<?php

declare( strict_types = 1 );

namespace DNB\GND;

use DNB\GND\Adapters\DataAccess\NetworkSparqlQueryDispatcher;
use DNB\GND\Adapters\Presentation\ParserFunctionDokuPresenter;
use DNB\GND\UseCases\GetGndDoku\GetGndDoku;
use Parser;

final class GndDokuFunction {

	public const DOKU_SPARQL_ENDPOINT = 'https://doku.wikibase.wiki/query/proxy/wdqs/bigdata/namespace/wdq/sparql';

	public static function onParserFirstCallInit( Parser $parser ): void {
		$parser->setFunctionHook(
			'gnd_doku',
			function( Parser $parser, string ...$parameters ) {
				$parser->getOutput()->updateCacheExpiry( 0 );
				$parser->getOutput()->addModules( 'ext.gnd' );
				return ( new self() )->render( $parameters );
			}
		);
	}

	public function render( array $rawParameters ) {
		$presenter = new ParserFunctionDokuPresenter();

		$useCase = new GetGndDoku(
			$presenter,
			new NetworkSparqlQueryDispatcher( self::DOKU_SPARQL_ENDPOINT )
		);

		$parameters = $this->parserArgumentsToKeyValuePairs( $rawParameters );

		$useCase->showGndDoku(
			$parameters['language'] ?? null,
			$this->normalizeCodingsParameter( $parameters )
		);

		return $presenter->getParserFunctionReturnValue();
	}

	private function parserArgumentsToKeyValuePairs( array $rawParameters ): array {
		$pairs = [];

		foreach ( $rawParameters as $argument ) {
			if ( false !== strpos( $argument, '=' ) ) {
				[$key, $value] = explode( '=', $argument );
				$pairs[trim($key)] = trim($value);
			}
		}

		return $pairs;
	}

	private function normalizeCodingsParameter( array $parameters ): array {
		return array_map(
			'strtoupper',
			array_map(
				fn ( string $s ) => preg_replace( '/\s+/', '', $s ),
				explode( ',', $parameters['codings'] ?? '' )
			)
		);
	}

}
