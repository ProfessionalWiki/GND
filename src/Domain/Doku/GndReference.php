<?php

declare( strict_types = 1 );

namespace DNB\GND\Domain\Doku;

class GndReference {

	private string $description;
	private ?string $url;

	public function __construct( string $description, ?string $url ) {
		$this->description = $description;
		$this->url = $url;
	}

	public function getDescription(): string {
		return $this->description;
	}

	public function getUrl(): ?string {
		return $this->url;
	}

}
