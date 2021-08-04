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

	// TODO: 'MARC 21', 'PICA+', 'PICA3'
	public function showGndDoku( ?string $langCode, array $codingsToShow ): void {
		$langCode ??= 'de';

		if ( !in_array( $langCode, [ 'en', 'de' ] ) ) {
			$this->presenter->showErrorMessage( 'Invalid language code. Supported: en, de' );
			return;
		}

		try {
			$codings = $this->queryCodings( $langCode );
			$subfields = $this->querySubfields( $langCode );
			$subfieldCodes = $this->querySubfieldCodings( $langCode );
		}
		catch ( \Exception $exception ) {
			$this->presenter->showErrorMessage( 'Could not obtain SPARQL result' );
			return;
		}

		$this->presenter->showGndDoku(
			$langCode,
			$codingsToShow,
			...$this->fieldDocsFromSparqlResults( $codings, $subfields, $subfieldCodes )
		);
	}

	/**
	 * @return FieldDoku[]
	 */
	private function fieldDocsFromSparqlResults( array $codings, array $subfields, array $subfieldCodes ): array {
		$fieldDocs = [];

		foreach ( $this->getInfoPerProperty( $codings, $subfields, $subfieldCodes ) as $propertyInfo ) {
			$fieldDocs[] = $this->newFieldDokuFromResult( $propertyInfo );
		}

		return $fieldDocs;
	}

	private function getInfoPerProperty( array $codings, array $subfields, array $subfieldCodes ): array {
		$propInfo = [];

		foreach ( $codings['results']['bindings'] as $resultRow ) {
			$propInfo[$resultRow['pId']['value']]['propertyUri'] = $resultRow['property']['value'];
			$propInfo[$resultRow['pId']['value']]['propertyLabel'] = $resultRow['propertyLabel']['value'];
			$propInfo[$resultRow['pId']['value']]['codings'][$resultRow['codingTypeLabel']['value']] = $resultRow['coding']['value'];
		}

		foreach( $propInfo as $pId => $_ ) {
			foreach ( $subfields['results']['bindings'] as $subfieldRow ) {
				if ( $subfieldRow['pId']['value'] === $pId ) {
					if ( $subfieldRow['subfieldQualifierPropLabel']['value'] === 'https://doku.wikibase.wiki/prop/qualifier/P8' ) {
						$propInfo[$pId]['subfields'][$subfieldRow['subfieldProperty']['value']]['possibleValues']
							[$subfieldRow['subfieldQualifierValue']['value']] = $subfieldRow['subfieldQualifierValueLabel']['value'];
					}

					if ( $subfieldRow['subfieldQualifierPropLabel']['value'] === 'https://doku.wikibase.wiki/prop/qualifier/P7' ) {
						$propInfo[$pId]['subfields'][$subfieldRow['subfieldProperty']['value']]['description'] = $subfieldRow['subfieldQualifierValueLabel']['value'];
					}

					$propInfo[$pId]['subfields'][$subfieldRow['subfieldProperty']['value']]['label'] = $subfieldRow['subfieldPropertyLabel']['value'];
				}
			}
		}

		foreach ( $propInfo as $pId => $prop ) {
			foreach ( $prop['subfields'] ?? [] as $subfieldPropUri => $subfield ) {
				foreach ( $subfieldCodes['results']['bindings'] as $sfCodeRow ) {
					if ( $subfieldPropUri === $sfCodeRow['property']['value'] ) {
						$propInfo[$pId]['subfields'][$subfieldPropUri]['codes'][$sfCodeRow['codingType']['value']] = $sfCodeRow['subfieldCoding']['value'];
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
						$subfield['label'] ?? '',
						$subfield['possibleValues'] ?? [],
						$subfield['codes'] ?? [],
					),
					$propertyInfo['subfields'] ?? []
				)
			)
		);
	}

	private function queryCodings( string $langCode ): array {
		$sparql = <<< 'SPARQL'
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

  SERVICE wikibase:label { bd:serviceParam wikibase:language "[AUTO_LANGUAGE],langCodePlaceholder" }

  BIND(STRAFTER(STR(?property), '/entity/') as ?pId)
}
ORDER BY ASC(xsd:integer(STRAFTER(STR(?property), '/entity/P')))
SPARQL;

		return $this->queryDispatcher->query(
			str_replace( 'langCodePlaceholder', $langCode, $sparql )
		);
	}

	private function querySubfields( string $langCode ): array {
		$sparql = <<< 'SPARQL'
PREFIX p: <https://doku.wikibase.wiki/prop/>
PREFIX prop: <https://doku.wikibase.wiki/prop/direct/>
PREFIX item: <https://doku.wikibase.wiki/entity/>
PREFIX qualifier: <https://doku.wikibase.wiki/prop/qualifier/>
PREFIX statement: <https://doku.wikibase.wiki/prop/statement/>

SELECT ?pId ?subfieldProperty ?subfieldPropertyLabel ?subfieldQualifierPropLabel ?subfieldQualifierValue ?subfieldQualifierValueLabel WHERE {
  ?property prop:P2 item:Q2 .
  ?property p:P15 ?subfieldsProp .
  ?subfieldsProp statement:P15 ?subfieldProperty . # OPTIONAL{ ?subfieldsProp statement:P15 ?subfieldProperty }
  ?subfieldsProp ?subfieldQualifierProp ?subfieldQualifierValue

  SERVICE wikibase:label { bd:serviceParam wikibase:language "[AUTO_LANGUAGE],langCodePlaceholder" }

  BIND(STRAFTER(STR(?property), '/entity/') as ?pId)
}
ORDER BY ASC(xsd:integer(STRAFTER(STR(?property), '/entity/P')))
SPARQL;

		return $this->queryDispatcher->query(
			str_replace( 'langCodePlaceholder', $langCode, $sparql )
		);
	}

	public function querySubfieldCodings( string $langCode ): array {
		$sparql = <<< 'SPARQL'
PREFIX p: <https://doku.wikibase.wiki/prop/>
PREFIX prop: <https://doku.wikibase.wiki/prop/direct/>
PREFIX item: <https://doku.wikibase.wiki/entity/>
PREFIX qualifier: <https://doku.wikibase.wiki/prop/qualifier/>
PREFIX statement: <https://doku.wikibase.wiki/prop/statement/>

SELECT ?property ?subfieldCoding ?codingType WHERE {
  ?property prop:P2 item:Q3 .
  ?property p:P4 ?subfieldCodingProp .
  ?subfieldCodingProp statement:P4 ?subfieldCoding .
  ?subfieldCodingProp qualifier:P3 ?codingType

  SERVICE wikibase:label { bd:serviceParam wikibase:language "[AUTO_LANGUAGE],langCodePlaceholder" }
}
ORDER BY ASC(xsd:integer(STRAFTER(STR(?property), '/entity/P')))
SPARQL;

		return $this->queryDispatcher->query(
			str_replace( 'langCodePlaceholder', $langCode, $sparql )
		);
	}

}
