<?php

declare( strict_types = 1 );

namespace DNB\GND\UseCases\ItemPropertiesToStrings;

use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;

interface PropertyChangePresenter {

	public function presentChangingPropertyType( PropertyId $id, string $oldType, string $newType );

	public function presentMigratingItem( ItemId $id );

}
