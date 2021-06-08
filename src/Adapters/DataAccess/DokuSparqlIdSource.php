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
		// https://jira.dnb.de/browse/GNDWIKIBAS-10
		// https://doku.wikibase.wiki/query/#%0ASELECT%20%3Fentity%20WHERE%20%7B%20%3Fentity%20%3Chttps%3A%2F%2Fdoku.wikibase.wiki%2Fprop%2Fdirect%2FP110%3E%20%3Chttps%3A%2F%2Fdoku.wikibase.wiki%2Fentity%2FQ1%3E%20%7D%20ORDER%20BY%20%3Fentity

		return 'https://doku.wikibase.wiki/query/proxy/wdqs/bigdata/namespace/wdq/sparql?query=SELECT%20%3Fentity%20WHERE%20%7B%20%3Fentity%20%3Chttps%3A%2F%2Fdoku.wikibase.wiki%2Fprop%2Fdirect%2FP110%3E%20%3Chttps%3A%2F%2Fdoku.wikibase.wiki%2Fentity%2FQ1%3E%20%7D%20ORDER%20BY%20%3Fentity';
	}

	private function getIdsFromSparqlXmlResult( string $xml ): array {
		$matches= [];
		preg_match_all( '~entity/(.*?)</uri>~s', $xml, $matches );
		return $matches[1];
	}

}
