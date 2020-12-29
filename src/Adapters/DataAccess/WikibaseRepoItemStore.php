<?php

declare( strict_types = 1 );

namespace DNB\GND\Adapters\DataAccess;

use DNB\GND\Domain\ItemStore;
use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\DataModel\Entity\Item;
use Wikibase\Repo\EditEntity\EditEntity;
use Wikibase\Repo\WikibaseRepo;

class WikibaseRepoItemStore implements ItemStore {

	private EditEntity $entitySaver;

	public function __construct( EditEntity $entitySaver ) {
		$this->entitySaver = $entitySaver;
	}

	public function storeItem( Item $item ): void {
		$this->entitySaver->attemptSave(
			$item,
			'test summary',
			EDIT_NEW,
			false
		);
	}

}
