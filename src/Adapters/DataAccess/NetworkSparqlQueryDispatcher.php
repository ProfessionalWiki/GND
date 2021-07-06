<?php

declare( strict_types = 1 );

namespace DNB\GND\Adapters\DataAccess;

use DNB\GND\Domain\SparqlQueryDispatcher;

class NetworkSparqlQueryDispatcher implements SparqlQueryDispatcher {

	private string $endpointUrl;

	public function __construct( string $endpointUrl ) {
		$this->endpointUrl = $endpointUrl;
	}

	public function query( string $sparqlQuery ): array {
		return json_decode(
			file_get_contents(
				$this->getUrl( $sparqlQuery ),
				false,
				stream_context_create( $this->getRequestOptions() )
			),
			true
		);
	}

	private function getUrl( string $sparqlQuery ): string {
		return $this->endpointUrl . '?query=' . urlencode( $sparqlQuery );
	}

	private function getRequestOptions(): array {
		return [
			'http' => [
				'method' => 'GET',
				'header' => [
					'Accept: application/sparql-results+json',
					'User-Agent: MediaWiki-GND'
				]
			]
		];
	}

}
