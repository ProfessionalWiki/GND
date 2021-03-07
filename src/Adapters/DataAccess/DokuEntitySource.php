<?php

declare( strict_types = 1 );

namespace DNB\GND\Adapters\DataAccess;

use Deserializers\Deserializer;
use DNB\GND\Domain\EntitySource;
use FileFetcher\FileFetcher;
use Wikibase\DataModel\Entity\EntityDocument;

class DokuEntitySource implements EntitySource {

	private FileFetcher $fileFetcher;
	private Deserializer $entityDeserializer;
	private array $idSource = [];

	public function __construct( FileFetcher $fileFetcher, Deserializer $entityDeserializer ) {
		$this->fileFetcher = $fileFetcher;
		$this->entityDeserializer = $entityDeserializer;
	}

	public function next(): ?EntityDocument {
		if ( $this->idSource === [] ) {
			$this->idSource = $this->newIdSource();
		}

		$id = array_shift( $this->idSource );

		if ( $id === null ) {
			return null;
		}

		// Purposefully not catching exception
		$apiResult = $this->fileFetcher->fetchFile( $this->buildApiFetchUrl( $id ) );

		$deserialization = $this->entityDeserializer->deserialize( $apiResult );

		if ( $deserialization instanceof EntityDocument ) {
			return $deserialization;
		}

		throw new \RuntimeException();
	}

	private function newIdSource(): array {
		return [ 'P61', 'Q150', 'Q151', 'Q152', 'P62', 'Q250', 'Q251' ];
	}

	private function buildApiFetchUrl( string $id ): string {
		$safeId = urlencode( $id );
		return "https://doku.wikibase.wiki/w/api.php?action=wbgetentities&ids=$safeId&format=json";
	}

}
