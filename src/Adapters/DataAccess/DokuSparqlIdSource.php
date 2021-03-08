<?php

declare( strict_types = 1 );

namespace DNB\GND\Adapters\DataAccess;

class DokuSparqlIdSource {

	/**
	 * @return string[]
	 */
	public function getVocabularyIds(): array {
		return [ 'P2', 'Q250' ];
	}

}
