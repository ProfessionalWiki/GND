<?php

declare( strict_types = 1 );

namespace DNB\GND\Domain;

interface SparqlQueryDispatcher {

	public function query( string $sparqlQuery ): array;

}
