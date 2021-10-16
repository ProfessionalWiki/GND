<?php

declare( strict_types = 1 );

namespace DNB\GND\Adapters\Presentation;

use DNB\GND\Domain\Doku\GndField;

class GndDokuSerializer {

	public function fieldsToArrays( GndField ...$fields ): array {
		$fieldsArray = [];

		foreach ( $fields as $field ) {
			$fieldsArray[$field->id] = $this->fieldToArray( $field );
		}

		return [
			'fields' => $fieldsArray
		];
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

			'viewLink' => 'https://doku.wikibase.wiki/wiki/datafield?property=' . $field->id,
			'editLink' => 'https://doku.wikibase.wiki/wiki/Property:' . $field->id
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
			];
		}

		return $subfieldsArray;
	}

	private function examplesToArray( GndField $field ): array {
		return array_map(
			fn ( string $label ) => [ 'label' => $label ],
			$field->examples
		);
	}

}
