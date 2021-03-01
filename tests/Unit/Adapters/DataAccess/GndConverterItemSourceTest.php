<?php

declare( strict_types = 1 );

namespace DNB\GND\Tests\Unit\Adapters\DataAccess;

use DNB\GND\Adapters\DataAccess\GndConverter\ItemBuilder;
use DNB\GND\Adapters\DataAccess\GndConverter\ProductionValueBuilder;
use DNB\GND\Adapters\DataAccess\GndConverterItemSource;
use DNB\GND\Domain\ItemSource;
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

		$this->assertNull( $itemSource->nextItem() );
	}

	private function newItemBuilder(): ItemBuilder {
		return new ItemBuilder( new ProductionValueBuilder() );
	}

	public function testWithTestGndJson() {
		$itemSource = new GndConverterItemSource(
			$this->getTestGndIterator(),
			$this->newItemBuilder()
		);

		$this->assertFiveItems( $itemSource );
	}

	private function assertFiveItems( ItemSource $itemSource ) {
		$this->assertNotNull( $itemSource->nextItem(), 'first item should not be null' );
		$this->assertNotNull( $itemSource->nextItem(), 'second item should not be null' );
		$this->assertNotNull( $itemSource->nextItem(), 'third item should not be null' );
		$this->assertNotNull( $itemSource->nextItem(), 'fourth item should not be null' );
		$this->assertNotNull( $itemSource->nextItem(), 'fifth item should not be null' );
		$this->assertNull( $itemSource->nextItem(), 'there should be no sixth item' );
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
