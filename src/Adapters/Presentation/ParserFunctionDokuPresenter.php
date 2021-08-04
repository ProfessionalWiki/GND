<?php

declare( strict_types = 1 );

namespace DNB\GND\Adapters\Presentation;

use DNB\GND\UseCases\GetGndDoku\FieldDoku;
use DNB\GND\UseCases\GetGndDoku\GndDokuPresenter;
use DNB\GND\UseCases\GetGndDoku\SubfieldDoku;

class ParserFunctionDokuPresenter implements GndDokuPresenter {

	/**
	 * @var mixed[]|string
	 */
	private $parserFunctionReturnValue = '';

	/**
	 * @return mixed[]|string
	 */
	public function getParserFunctionReturnValue() {
		return $this->parserFunctionReturnValue;
	}

	public function showErrorMessage( string $error ): void {
		$this->parserFunctionReturnValue = $error;
	}

	public function showGndDoku( string $langCode, FieldDoku ...$fieldDocs ): void {
		$this->parserFunctionReturnValue = [
			'noparse' => true,
			'isHTML' => true,
			$this->fieldDocsToTable( $langCode, ...$fieldDocs )
		];
	}

	private function fieldDocsToTable( string $langCode, FieldDoku ...$fieldDocs ): string {
		$tableHtml = <<< 'HTML'
<table class="wikitable sortable gnd-doku">
<thead>
	<tr>
		<th>PICA3</th>
		<th>PICA+</th>
		<th>MARC 21</th>
		<th>gnd-table-description</th>
	</tr>
</thead>
<tbody>
HTML . $this->fieldDocsToHtmlRows( ...$fieldDocs ) . '</tbody></table>';

		return $this->replaceMessagePlaceholders( $langCode, $tableHtml );
	}

	private function replaceMessagePlaceholders( string $langCode, string $text ): string {
		return str_replace(
			[
				'gnd-table-description',
				'gnd-table-view-subfields',
				'gnd-table-subfield-description',
			],
			[
				wfGetLangObj( $langCode )->getMessage( 'gnd-table-description' ),
				wfGetLangObj( $langCode )->getMessage( 'gnd-table-view-subfields' ),
				wfGetLangObj( $langCode )->getMessage( 'gnd-table-subfield-description' ),
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
		return [
			// Note: can be improved by using URIs to avoid break when a label changes or another language is used
			$fieldDoku->getFieldCodes()['PICA3'] ?? '',
			$fieldDoku->getFieldCodes()['PICA+'] ?? '',
			$fieldDoku->getFieldCodes()['MARC 21 Format für Normdaten'] ?? $fieldDoku->getFieldCodes()['MARC 21 Format for Authority Data'] ?? '',
			$this->getDescriptionCellContent( $fieldDoku )
		];
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
		<th>PICA3</th>
		<th>PICA+</th>
		<th>MARC 21</th>
		<th>gnd-table-subfield-description</th>
	</tr>
</thead>
<tbody>
HTML
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
					\Html::element( 'td', [], $subfield->getSubfieldCodes()['https://doku.wikibase.wiki/entity/Q1316'] ?? '' )
					. \Html::element( 'td', [], $subfield->getSubfieldCodes()['https://doku.wikibase.wiki/entity/Q1317'] ?? '' )
					. \Html::element( 'td', [], $subfield->getSubfieldCodes()['https://doku.wikibase.wiki/entity/Q1320'] ?? '' )
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
