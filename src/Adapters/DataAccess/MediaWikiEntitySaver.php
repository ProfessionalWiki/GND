<?php

declare( strict_types = 1 );

namespace DNB\GND\Adapters\DataAccess;

use CommentStoreComment;
use DNB\GND\Domain\EntitySaver;
use RuntimeException;
use Title;
use User;
use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\Repo\WikibaseRepo;
use WikiPage;
use WikitextContent;

class MediaWikiEntitySaver implements EntitySaver {

	private User $user;

	public function __construct( User $user ) {
		$this->user = $user;
	}

	public function storeEntity( EntityDocument $entity ): void {
		if ( $entity->getId() === null ) {
			throw new RuntimeException( 'Cannot store items that do not have an ID' );
		}

		$titleObject = Title::newFromText( $entity->getId()->getSerialization() );
		$page = new WikiPage( $titleObject );

		$updater = $page->newPageUpdater( $this->user );
		$updater->setContent( 'main', new WikitextContent(
			json_encode( WikibaseRepo::getDefaultInstance()->getBaseDataModelSerializerFactory()->newEntitySerializer()->serialize( $entity ) )
		) );
		$updater->saveRevision( CommentStoreComment::newUnsavedComment( 'test summary ' . $entity->getId() ) );
	}

}
