<?php

declare( strict_types = 1 );

namespace DNB\GND\UseCases\GetGndDoku;

use DNB\GND\Domain\SparqlQueryDispatcher;

class GetGndDoku {

	private GndDokuPresenter $presenter;
	private SparqlQueryDispatcher $queryDispatcher;

	public function __construct( GndDokuPresenter $presenter, SparqlQueryDispatcher $sparqlQueryDispatcher ) {
		$this->presenter = $presenter;
		$this->queryDispatcher = $sparqlQueryDispatcher;
	}

	public function showGndDoku(): void {
		try {
			$codings = $this->queryCodings();
			$subfields = $this->querySubfields();
		}
		catch ( \Exception $exception ) {
			$this->presenter->showErrorMessage( 'Could not obtain SPARQL result' );
			return;
		}

		$this->presenter->showGndDoku( ...$this->fieldDocsFromSparqlResults( $codings, $subfields ) );
	}

	/**
	 * @return FieldDoku[]
	 */
	private function fieldDocsFromSparqlResults( array $codings, array $subfields ): array {
		$fieldDocs = [];

		foreach ( $this->getInfoPerProperty( $codings, $subfields ) as $propertyInfo ) {
			$fieldDocs[] = $this->newFieldDokuFromResult( $propertyInfo );
		}

		return $fieldDocs;
	}

	private function getInfoPerProperty( array $codings, array $subfields ): array {
		$propInfo = [];

		foreach ( $codings['results']['bindings'] as $resultRow ) {
			$propInfo[$resultRow['pId']['value']]['propertyUri'] = $resultRow['property']['value'];
			$propInfo[$resultRow['pId']['value']]['propertyLabel'] = $resultRow['propertyLabel']['value'];
			$propInfo[$resultRow['pId']['value']]['codings'][$resultRow['codingTypeLabel']['value']] = $resultRow['coding']['value'];
		}

		foreach( $propInfo as $pId => $prop ) {
			foreach ( $subfields['results']['bindings'] as $subfieldRow ) {
				if ( $subfieldRow['pId']['value'] === $pId ) {
					if ( $subfieldRow['subfieldQualifierPropLabel']['value'] === 'https://doku.wikibase.wiki/prop/qualifier/P8' ) {
						$propInfo[$pId]['subfields'][$subfieldRow['subfieldProperty']['value']]['possibleValues']
							[$subfieldRow['subfieldQualifierValue']['value']] = $subfieldRow['subfieldQualifierValueLabel']['value'];
					}

					if ( $subfieldRow['subfieldQualifierPropLabel']['value'] === 'https://doku.wikibase.wiki/prop/qualifier/P7' ) {
						$propInfo[$pId]['subfields'][$subfieldRow['subfieldProperty']['value']]['description'] = $subfieldRow['subfieldQualifierValueLabel']['value'];
					}
				}
			}
		}

		return $propInfo;
	}

	private function newFieldDokuFromResult( array $propertyInfo ): FieldDoku {
		return new FieldDoku(
			$propertyInfo['propertyLabel'],
			$propertyInfo['propertyUri'],
			$propertyInfo['codings'] ?? [],
			array_values(
				array_map(
					fn( array $subfield ) => new SubfieldDoku(
						$subfield['description'] ?? '',
						$subfield['possibleValues'] ?? []
					),
					$propertyInfo['subfields'] ?? []
				)
			)
		);
	}

	private function queryCodings(): array {
		return $this->queryDispatcher->query(
			<<< 'SPARQL'
PREFIX p: <https://doku.wikibase.wiki/prop/>
PREFIX prop: <https://doku.wikibase.wiki/prop/direct/>
PREFIX item: <https://doku.wikibase.wiki/entity/>
PREFIX qualifier: <https://doku.wikibase.wiki/prop/qualifier/>
PREFIX statement: <https://doku.wikibase.wiki/prop/statement/>

SELECT ?property ?pId ?propertyLabel ?coding ?codingTypeLabel WHERE {
  ?property prop:P2 item:Q2 .
  ?property p:P4 ?codingProp .
  ?codingProp statement:P4 ?coding .
  ?codingProp qualifier:P3 ?codingType

  SERVICE wikibase:label { bd:serviceParam wikibase:language "[AUTO_LANGUAGE],de" }

  BIND(STRAFTER(STR(?property), '/entity/') as ?pId)
}
ORDER BY ASC(xsd:integer(STRAFTER(STR(?property), '/entity/P')))
SPARQL
		);
	}

	private function querySubfields(): array {
		return $this->queryDispatcher->query(
			<<< 'SPARQL'
PREFIX p: <https://doku.wikibase.wiki/prop/>
PREFIX prop: <https://doku.wikibase.wiki/prop/direct/>
PREFIX item: <https://doku.wikibase.wiki/entity/>
PREFIX qualifier: <https://doku.wikibase.wiki/prop/qualifier/>
PREFIX statement: <https://doku.wikibase.wiki/prop/statement/>

SELECT ?pId ?subfieldProperty ?subfieldPropertyLabel ?subfieldQualifierPropLabel ?subfieldQualifierValue ?subfieldQualifierValueLabel WHERE {
  ?property prop:P2 item:Q2 .
  ?property p:P15 ?subfieldsProp .
  ?subfieldsProp statement:P15 ?subfieldProperty .
  ?subfieldsProp ?subfieldQualifierProp ?subfieldQualifierValue

  SERVICE wikibase:label { bd:serviceParam wikibase:language "[AUTO_LANGUAGE],de" }

  BIND(STRAFTER(STR(?property), '/entity/') as ?pId)
}
ORDER BY ASC(xsd:integer(STRAFTER(STR(?property), '/entity/P')))
SPARQL
		);
	}

}
