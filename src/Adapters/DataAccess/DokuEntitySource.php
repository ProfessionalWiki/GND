<?php

declare( strict_types = 1 );

namespace DNB\GND\Adapters\DataAccess;

use Deserializers\Deserializer;
use DNB\GND\Domain\EntitySource;
use FileFetcher\FileFetcher;
use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\DataModel\Statement\StatementListProvider;

class DokuEntitySource implements EntitySource {

	private array $entityIds;
	private FileFetcher $fileFetcher;
	private Deserializer $entityDeserializer;

	/**
	 * @param string[] $entityIds
	 * @param FileFetcher $fileFetcher
	 * @param Deserializer $entityDeserializer
	 */
	public function __construct( array $entityIds, FileFetcher $fileFetcher, Deserializer $entityDeserializer ) {
		$this->fileFetcher = $fileFetcher;
		$this->entityDeserializer = $entityDeserializer;
		$this->entityIds = $entityIds;
	}

	public function next(): ?EntityDocument {
		$id = array_shift( $this->entityIds );

		if ( $id === null ) {
			return null;
		}

		// Purposefully not catching exception
		$apiResult = $this->fileFetcher->fetchFile( $this->buildApiFetchUrl( $id ) );

		$deserialization = $this->entityDeserializer->deserialize(
			json_decode( $apiResult, true )['entities'][$id]
		);

		if ( $deserialization instanceof EntityDocument ) {
			if ( $deserialization instanceof StatementListProvider ) {
				$deserialization->getStatements()->clear();
			}
			return $deserialization;
		}

		throw new \RuntimeException();
	}

	private function buildApiFetchUrl( string $id ): string {
		$safeId = urlencode( $id );
		return "https://doku.wikibase.wiki/w/api.php?action=wbgetentities&ids=$safeId&format=json";
	}

}
