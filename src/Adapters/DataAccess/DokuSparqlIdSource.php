<?php

declare( strict_types = 1 );

namespace DNB\GND\Adapters\DataAccess;

use FileFetcher\FileFetcher;

class DokuSparqlIdSource {

	private FileFetcher $fileFetcher;

	public function __construct( FileFetcher $fileFetcher ) {
		$this->fileFetcher = $fileFetcher;
	}

	/**
	 * @return string[]
	 */
	public function getVocabularyIds(): array {
		return $this->getIdsFromSparqlXmlResult( $this->fileFetcher->fetchFile( $this->buildSparqlUrl() ) );
	}

	private function buildSparqlUrl(): string {
		// TODO
		// https://jira.dnb.de/browse/GNDWIKIBAS-10
		// SELECT ?item WHERE { ?item wdt:P110 wd:Q1 }
		// https://query.wikidata.org/#SELECT%20%3Fitem%20WHERE%20%7B%20%3Fitem%20wdt%3AP31%20wd%3AQ146%20%7D

		return 'https://query.wikidata.org/#SELECT%20%3Fitem%20WHERE%20%7B%20%3Fitem%20wdt%3AP31%20wd%3AQ146%20%7D';
	}

	private function getIdsFromSparqlXmlResult( string $xml ): array {
		$matches= [];
		preg_match_all( '~entity/(.*?)</uri>~s', $xml, $matches );
		return $matches[1];
	}

}
