<?php

declare( strict_types = 1 );

namespace DNB\GND\Tests\Unit\Adapters\DataAccess;

use DNB\GND\Adapters\DataAccess\GndConverter\ItemBuilder;
use DNB\GND\Adapters\DataAccess\GndConverter\ProductionValueBuilder;
use DNB\GND\Adapters\DataAccess\GndConverterItemSource;
use DNB\GND\Domain\ItemSource;
use DNB\GND\Tests\TestDoubles\StubDataTypeLookup;
use PHPUnit\Framework\TestCase;
use SplFileObject;

/**
 * @covers \DNB\GND\Adapters\DataAccess\GndConverterItemSource
 */
class GndConverterItemSourceTest extends TestCase {

	public function testReturnsNullWhenIteratorIsEmpty() {
		$itemSource = new GndConverterItemSource(
			new \ArrayIterator( [] ),
			$this->newItemBuilder()
		);

		$this->assertNull( $itemSource->next() );
	}

	private function newItemBuilder(): ItemBuilder {
		return new ItemBuilder(
			new ProductionValueBuilder(),
			new StubDataTypeLookup( 'string' )
		);
	}

	public function testWithTestGndJson() {
		$itemSource = new GndConverterItemSource(
			$this->getTestGndIterator(),
			$this->newItemBuilder()
		);

		$this->assertFiveItems( $itemSource );
	}

	private function assertFiveItems( ItemSource $itemSource ) {
		$this->assertNotNull( $itemSource->next(), 'first item should not be null' );
		$this->assertNotNull( $itemSource->next(), 'second item should not be null' );
		$this->assertNotNull( $itemSource->next(), 'third item should not be null' );
		$this->assertNotNull( $itemSource->next(), 'fourth item should not be null' );
		$this->assertNotNull( $itemSource->next(), 'fifth item should not be null' );
		$this->assertNull( $itemSource->next(), 'there should be no sixth item' );
	}

	private function getTestGndIterator(): \Iterator {
		$file = new SplFileObject( __DIR__ . '/../../../GND.json' );

		while ( !$file->eof() ) {
			yield $file->fgets();
		}
	}

	public function testInvalidLinesAreSkipped() {
		$itemSource = new GndConverterItemSource(
			( function(): \Iterator {
				yield '';
				yield 'not json';
				yield '{"fields": "invalid json"}';
				yield from $this->getTestGndIterator();
			} )(),
			$this->newItemBuilder()
		);

		$this->assertFiveItems( $itemSource );
	}

}
