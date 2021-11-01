<?php

declare( strict_types = 1 );

namespace DNB\GND\Adapters\Presentation;

use DNB\GND\Domain\Doku\GndField;
use DNB\GND\Domain\Doku\GndSubfield;
use DNB\GND\Domain\Doku\GndReference;

class GndDokuSerializer {

	private const PROPERTY_VIEW_URL = 'https://doku.wikibase.wiki/wiki/datafield?property=$1';
	private const PROPERTY_EDIT_URL = 'https://doku.wikibase.wiki/wiki/Property:$1';
	private const ITEM_VIEW_URL = 'https://doku.wikibase.wiki/wiki/datafield?item=$1';
	private const ITEM_EDIT_URL = 'https://doku.wikibase.wiki/wiki/Item:$1';

	public function fieldsToArrays( GndField ...$fields ): array {
		$fieldsArray = [];

		foreach ( $fields as $field ) {
			$fieldsArray[$field->id] = $this->fieldToArray( $field );
		}

		return $fieldsArray;
	}

	private function fieldToArray( GndField $field ): array {
		return [
			'label' => $field->label,
			'description' => $field->description,
			'aliases' => $field->aliases,

			'definition' => $field->definition,

			'repeatable' => $field->isRepeatable,

			'subfields' => $this->subfieldsToArray( $field ),

			'codings' => $field->codings,
			'validation' => $field->validation,
			'rulesOfUse' => $field->rulesOfUse,
			'examples' => $this->examplesToArray( $field ),

			'viewLink' => str_replace( '$1', $field->id, self::PROPERTY_VIEW_URL ),
			'editLink' => str_replace( '$1', $field->id, self::PROPERTY_EDIT_URL )
		];
	}

	private function subfieldsToArray( GndField $field ): array {
		$subfieldsArray = [];

		foreach ( $field->subfields as $subfield ) {
			$subfieldsArray[$subfield->getId()] = [
				'label' => $subfield->getLabel(),
				'description' => $subfield->getDescription(),

				'codings' => $subfield->getCodings(),
				'allowedValues' => $subfield->getPossibleValues(),
				'references' => $this->referencesToArray( $subfield ),
				'repeatable' => $subfield->isRepeatable(),

				'viewLink' => str_replace( '$1', $subfield->getId(), self::PROPERTY_VIEW_URL ),
				'editLink' => str_replace( '$1', $subfield->getId(), self::PROPERTY_EDIT_URL )
			];
		}

		return $subfieldsArray;
	}

	private function referencesToArray( GndSubfield $subfield ): array {
		return array_map(
			fn( GndReference $reference ) => [
				'description' => $reference->getDescription(),
				'URL' => $reference->getUrl(),
			],
			$subfield->getReferences()
		);
	}

	private function examplesToArray( GndField $field ): array {
		$examples = array_map(
			fn ( string $label ) => [ 'label' => $label ],
			$field->examples
		);

		foreach ( $examples as $itemId => $example ) {
			$examples[$itemId]['viewLink'] = str_replace( '$1', $itemId, self::ITEM_VIEW_URL );
			$examples[$itemId]['editLink'] = str_replace( '$1', $itemId, self::ITEM_EDIT_URL );
		}

		return $examples;
	}

}
