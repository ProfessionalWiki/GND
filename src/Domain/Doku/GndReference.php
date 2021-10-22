<?php

declare( strict_types = 1 );

namespace DNB\GND\Domain\Doku;

class GndReference {

	private string $name;
	private ?string $uri;

	public function __construct( string $name, ?string $uri ) {
		$this->name = $name;
		$this->uri = $uri;
	}

	public function getName(): string {
		return $this->name;
	}

	public function getUri(): ?string {
		return $this->uri;
	}

}
