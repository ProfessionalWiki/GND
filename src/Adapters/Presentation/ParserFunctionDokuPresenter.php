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

	public function showGndDoku( FieldDoku ...$fieldDocs ): void {
		$this->parserFunctionReturnValue = [
			'noparse' => true,
			'isHTML' => true,
			$this->fieldDocsToTable( ...$fieldDocs )
		];
	}

	private function fieldDocsToTable( FieldDoku ...$fieldDocs ): string {
		return <<< 'HTML'
<table class="wikitable">
<thead>
	<tr>
		<th>MARC 21</th>
		<th>PICA+</th>
		<th>PICA3</th>
		<th>Beschreibung</th>
	</tr>
</thead>
<tbody>
HTML . $this->fieldDocsToHtmlRows( ...$fieldDocs ) . '</tbody></table>';
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
			$fieldDoku->getFieldCodes()['MARC 21 Format für Normdaten'] ?? '',
			$fieldDoku->getFieldCodes()['PICA+'] ?? '',
			$fieldDoku->getFieldCodes()['PICA3'] ?? '',
			$this->getDescriptionCellContent( $fieldDoku )
		];
	}

	private function getDescriptionCellContent( FieldDoku $fieldDoku ): string {
		return
			\Html::element(
				'a',
				[ 'href' => $fieldDoku->getUrl() ],
				$fieldDoku->getLabel()
			)
			. <<< 'HTML'
<table class="wikitable">
<thead>
	<tr>
		<th>Unterfelder</th>
	</tr>
</thead>
<tbody>
HTML
			. implode(
				'',
				$this->subfieldsToTableRows( ...$fieldDoku->getSubfields() )
			)
			. '</tbody></table>';
	}

	private function subfieldsToTableRows( SubfieldDoku ...$subfieldDocs ): array {
		return array_map(
			fn( SubfieldDoku $subfield ) => \Html::rawElement( 'tr', [], \Html::rawElement( 'td', [], $this->subfieldToCellContent( $subfield ) ) ),
			$subfieldDocs
		);
	}

	private function subfieldToCellContent( SubfieldDoku $subfield ): string {
		return $subfield->getDescription()
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
