<?php

declare( strict_types = 1 );

namespace DNB\GND\Tests\Integration;

use MediaWiki\MediaWikiServices;
use PHPUnit\Framework\TestCase;

class GndDokuParserFunctionTest extends TestCase {

	public function testHappyPathWithAllParameters(): void {
		$output = $this->parse( '{{#gnd_doku:language=de | codings=PICA+, pica 3}}' );

		$this->assertStringContainsString(
			'Unterfelder anzeigen',
			$output
		);

		$this->assertStringContainsString(
			'PICA+',
			$output
		);

		$this->assertStringContainsString(
			'PICA3',
			$output
		);

		$this->assertStringNotContainsString(
			'MARC 21',
			$output
		);
	}

	private function parse( string $wikiText ): string {
		return MediaWikiServices::getInstance()->getParser()
			->parse(
				$wikiText,
				\Title::newMainPage(),
				new \ParserOptions( \User::newSystemUser( 'TestUser' ) )
			)->getText();
	}

}
