<?php

declare( strict_types = 1 );

namespace DNB\GND\Adapters\Presentation;

use DNB\GND\UseCases\GetGndDoku\FieldDoku;
use DNB\GND\UseCases\GetGndDoku\GndDokuPresenter;
use DNB\GND\UseCases\GetGndDoku\SubfieldDoku;

class ParserFunctionDokuPresenter implements GndDokuPresenter {

	private const CODING_PICA3 = 'PICA3';
	private const CODING_PICA_PLUS = 'PICA+';
	private const CODING_MARC21 = 'MARC21';

	/**
	 * @var mixed[]|string
	 */
	private $parserFunctionReturnValue = '';

	private string $langCode;

	/**
	 * @var array<int, string>
	 */
	private array $codingsToShow;

	/**
	 * @return mixed[]|string
	 */
	public function getParserFunctionReturnValue() {
		return $this->parserFunctionReturnValue;
	}

	public function showErrorMessage( string $error ): void {
		$this->parserFunctionReturnValue = $error;
	}

	public function showGndDoku( string $langCode, array $codingsToShow, FieldDoku ...$fieldDocs ): void {
		$this->langCode = $langCode;
		$this->codingsToShow = $codingsToShow;

		$this->parserFunctionReturnValue = [
			'noparse' => true,
			'isHTML' => true,
			$this->fieldDocsToTable( ...$fieldDocs )
		];
	}

	private function fieldDocsToTable( FieldDoku ...$fieldDocs ): string {
		return $this->replaceMessagePlaceholders(
			'<table class="wikitable sortable gnd-doku">'
			. '<thead><tr>'
			. ( $this->shouldShow( self::CODING_PICA3 ) ? '<th>PICA3</th>' : '' )
			. ( $this->shouldShow( self::CODING_PICA_PLUS ) ? '<th>PICA+</th>' : '' )
			. ( $this->shouldShow( self::CODING_MARC21 ) ? '<th>MARC 21</th>' : '' )
			. '<th>gnd-table-description</th>'
			. '</tr></thead>'
			. '<tbody>'
			. $this->fieldDocsToHtmlRows( ...$fieldDocs )
			. '</tbody></table>'
		);
	}

	private function shouldShow( string $coding ): bool {
		return in_array( $coding, $this->codingsToShow );
	}

	private function replaceMessagePlaceholders( string $text ): string {
		return str_replace(
			[
				'gnd-table-description',
				'gnd-table-view-subfields',
				'gnd-table-subfield-description',
			],
			[
				wfGetLangObj( $this->langCode )->getMessage( 'gnd-table-description' ),
				wfGetLangObj( $this->langCode )->getMessage( 'gnd-table-view-subfields' ),
				wfGetLangObj( $this->langCode )->getMessage( 'gnd-table-subfield-description' ),
			],
			$text
		);
	}

	private function fieldDocsToHtmlRows( FieldDoku ...$fieldDocs ): string {
		return implode(
			'',
			array_map(
				function( FieldDoku $fieldDoku ): string {
					return \Html::rawElement(
						'tr',
						[],
						implode(
							'',
							array_map(
								fn( string $content ) => \Html::rawElement( 'td', [], $content ),
								$this->fieldDokuToCellContent( $fieldDoku )
							)
						)
					);
				},
				$fieldDocs
			)
		);
	}

	private function fieldDokuToCellContent( FieldDoku $fieldDoku ): array {
		$cellContent = [];

		if ( $this->shouldShow( self::CODING_PICA3 ) ) {
			// Note: can be improved by using URIs to avoid break when a label changes or another language is used
			$cellContent[] = $fieldDoku->getFieldCodes()['PICA3'] ?? '';
		}

		if ( $this->shouldShow( self::CODING_PICA_PLUS ) ) {
			$cellContent[] = $fieldDoku->getFieldCodes()['PICA+'] ?? '';
		}

		if ( $this->shouldShow( self::CODING_MARC21 ) ) {
			$cellContent[] = $fieldDoku->getFieldCodes()['MARC 21 Format für Normdaten'] ?? $fieldDoku->getFieldCodes()['MARC 21 Format for Authority Data'] ?? '';
		}

		$cellContent[] = $this->getDescriptionCellContent( $fieldDoku );

		return $cellContent;
	}

	private function getDescriptionCellContent( FieldDoku $fieldDoku ): string {
		return
			\Html::element(
				'a',
				[ 'href' => $fieldDoku->getUrl(), 'class' => 'gnd-property' ],
				$fieldDoku->getLabel()
			)
			. <<< 'HTML'
<details>
	<summary>gnd-table-view-subfields</summary>
<table class="wikitable sortable gnd-subfields">
<thead>
	<tr>
HTML
			. ( $this->shouldShow( self::CODING_PICA3 ) ? '<th>PICA3</th>' : '' )
			. ( $this->shouldShow( self::CODING_PICA_PLUS ) ? '<th>PICA+</th>' : '' )
			. ( $this->shouldShow( self::CODING_MARC21 ) ? '<th>MARC 21</th>' : '' )
			. '<th>gnd-table-subfield-description</th>'
			. '</tr></thead><tbody>'
			. implode(
				'',
				$this->subfieldsToTableRows( ...$fieldDoku->getSubfields() )
			)
			. '</tbody></table></details>';
	}

	private function subfieldsToTableRows( SubfieldDoku ...$subfieldDocs ): array {
		return array_map(
			function( SubfieldDoku $subfield ) {
				return \Html::rawElement(
					'tr',
					[],
					( $this->shouldShow( self::CODING_PICA3 ) ?
						\Html::element( 'td', [], $subfield->getSubfieldCodes()['https://doku.wikibase.wiki/entity/Q1316'] ?? '' ) : ''
					)
					. ( $this->shouldShow( self::CODING_PICA_PLUS ) ?
						\Html::element( 'td', [], $subfield->getSubfieldCodes()['https://doku.wikibase.wiki/entity/Q1317'] ?? '' ) : ''
					)
					. ( $this->shouldShow( self::CODING_MARC21 ) ?
						\Html::element( 'td', [], $subfield->getSubfieldCodes()['https://doku.wikibase.wiki/entity/Q1320'] ?? '' ) : ''
					)
					. \Html::rawElement( 'td', [], $this->subfieldToCellContent( $subfield ) )
				);
			},
			$subfieldDocs
		);
	}

	private function subfieldToCellContent( SubfieldDoku $subfield ): string {
		return $subfield->getLabel()
			. $this->escapedArrayToHtmlList( $this->getAllowedValueLinks( $subfield ) );
	}

	private function getAllowedValueLinks( SubfieldDoku $subfield ): array {
		$possibleValues = [];

		foreach ( $subfield->getPossibleValues() as $uri => $value ) {
			$possibleValues[] = \Html::element(
				'a',
				[ 'href' => $uri ],
				$value
			);
		}

		return $possibleValues;
	}

	private function escapedArrayToHtmlList( array $listItems ): string {
		return \Html::rawElement(
			'ul',
			[],
			implode(
				'',
				array_map(
					fn( string $item ) => \Html::rawElement( 'li', [], $item ),
					$listItems
				)
			)
		);
	}

}
