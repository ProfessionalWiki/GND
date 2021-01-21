<?php

declare( strict_types = 1 );

namespace DNB\GND\Adapters\DataAccess;

use CommentStoreComment;
use DNB\GND\Domain\ItemStore;
use User;
use Wikibase\DataModel\Entity\Item;
use Wikibase\Repo\Content\ItemContent;

class MediaWikiItemStore implements ItemStore {

	private User $user;

	public function __construct( User $user ) {
		$this->user = $user;
	}

	public function storeItem( Item $item ): void {
		if ( $item->getId() === null ) {
			throw new \RuntimeException( 'Cannot store items that do not have an ID' );
		}

		$titleObject = \Title::newFromText( $item->getId()->getSerialization(), WB_NS_ITEM );
		$page = new \WikiPage( $titleObject );

		$updater = $page->newPageUpdater( $this->user );
		$updater->setContent( 'main', ItemContent::newFromItem( $item ) );
		$updater->saveRevision( CommentStoreComment::newUnsavedComment( 'test summary ' . $item->getId() ) );
	}

}
