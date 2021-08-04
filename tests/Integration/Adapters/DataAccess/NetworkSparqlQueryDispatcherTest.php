<?php

declare( strict_types = 1 );

namespace DNB\GND\Tests\Integration\Adapters\DataAccess;

use DNB\GND\Adapters\DataAccess\NetworkSparqlQueryDispatcher;
use DNB\GND\GndDokuFunction;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DNB\GND\Adapters\DataAccess\NetworkSparqlQueryDispatcher
 */
class NetworkSparqlQueryDispatcherTest extends TestCase {

	public function testDokuWikiQuery(): void {
		$dispatcher = new NetworkSparqlQueryDispatcher( GndDokuFunction::DOKU_SPARQL_ENDPOINT );

		$sparqlQueryString = <<< 'SPARQL'
PREFIX prop: <https://doku.wikibase.wiki/prop/direct/>
PREFIX item: <https://doku.wikibase.wiki/entity/>

SELECT ?pId WHERE {
  ?property prop:P2 item:Q2 .

  BIND(STRAFTER(STR(?property), '/entity/') as ?pId)
}
ORDER BY ASC(xsd:integer(STRAFTER(STR(?property), '/entity/P')))

SPARQL;

		$this->assertSame(
			[
				'vars' => [ 'pId' ]
			],
			$dispatcher->query( $sparqlQueryString )['head']
		);
	}

}
