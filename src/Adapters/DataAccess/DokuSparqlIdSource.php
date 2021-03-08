<?php

declare( strict_types = 1 );

namespace DNB\GND\Adapters\DataAccess;

class DokuSparqlIdSource {

	/**
	 * @return string[]
	 */
	public function getVocabularyIds(): array {

		// https://jira.dnb.de/browse/GNDWIKIBAS-10
		// SELECT ?item WHERE { ?item wdt:P110 wd:Q1 }
		// https://query.wikidata.org/#SELECT%20%3Fitem%20WHERE%20%7B%20%3Fitem%20wdt%3AP31%20wd%3AQ146%20%7D

		return [ 'P2', 'Q250' ];
	}

}
