<?php

declare( strict_types = 1 );

namespace DNB\GND\Tests\Unit\Domain\Doku;

use DNB\GND\Domain\Doku\GndSubfield;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DNB\GND\Domain\Doku\GndSubfield
 */
class GndSubfieldTest extends TestCase {

	public function testCannotConstructWithInvalidReferences(): void {
		$this->expectException( \InvalidArgumentException::class );
		new GndSubfield(
			'P1',
			'label',
			'desc',
			[],
			[],
			[ 'not a reference' ]
		);
	}

	public function testCodingKeysMustBeStrings(): void {
		$this->expectException( \InvalidArgumentException::class );
		new GndSubfield(
			'P1',
			'label',
			'desc',
			[ 'value' ],
			[],
			[]
		);
	}

	public function testCodingValuesMustBeStrings(): void {
		$this->expectException( \InvalidArgumentException::class );
		new GndSubfield(
			'P1',
			'label',
			'desc',
			[ 'string key' => 32202 ],
			[],
			[]
		);
	}

	public function testPossibleValueKeysMustBeStrings(): void {
		$this->expectException( \InvalidArgumentException::class );
		new GndSubfield(
			'P1',
			'label',
			'desc',
			[],
			[ 'value' ],
			[]
		);
	}

	public function testPossibleValueValuesMustBeStrings(): void {
		$this->expectException( \InvalidArgumentException::class );
		new GndSubfield(
			'P1',
			'label',
			'desc',
			[],
			[ 'string key' => 32202 ],
			[]
		);
	}

}
