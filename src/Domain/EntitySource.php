<?php

declare( strict_types = 1 );

namespace DNB\GND\Domain;

use Wikibase\DataModel\Entity\EntityDocument;

interface EntitySource {

	public function next(): ?EntityDocument;

}
