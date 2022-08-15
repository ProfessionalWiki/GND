<?php

declare( strict_types = 1 );

namespace DNB\GND\Tests\Unit\UseCases\GetGndDoku;

use DNB\GND\Adapters\DataAccess\NetworkSparqlQueryDispatcher;
use DNB\GND\GndDokuFunction;
use DNB\GND\UseCases\GetGndDoku\FieldDoku;
use DNB\GND\UseCases\GetGndDoku\GetGndDoku;
use DNB\GND\UseCases\GetGndDoku\GndDokuPresenter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DNB\GND\UseCases\GetGndDoku\GetGndDoku
 */
class GetGndDokuTest extends TestCase {

	private const VALID_CODINGS = [ 'MARC 21', 'PICA+', 'PICA3' ];

	// TODO: stop using network now we have a system test via GndDokuParserFunctionTest
	public function testNetworkErrorLeadsToErrorMessage(): void {
		$presenter = $this->newSpyPresenter();

		$useCase = new GetGndDoku(
			$presenter,
			new NetworkSparqlQueryDispatcher( 'http://zsdjklsdfjklsdrjkl.wikibase.wiki/404' )
		);

		$useCase->showGndDoku( 'de', self::VALID_CODINGS );

		$this->assertSame( 'Could not obtain SPARQL result', $presenter->errorMessage );
	}

	private function newSpyPresenter() {
		return new class() implements GndDokuPresenter {
			/**
			 * @var FieldDoku[]
			 */
			public array $fieldDocs = [];
			public ?string $errorMessage = null;

			public function showGndDoku( string $langCode, array $codingsToShow, FieldDoku ...$fieldDocs ): void {
				$this->fieldDocs = $fieldDocs;
			}

			public function showErrorMessage( string $error ): void {
				$this->errorMessage = $error;
			}
		};
	}

	// TODO: stop using network now we have a system test via GndDokuParserFunctionTest
	public function testHappyPathViaNetwork(): void {
		$presenter = $this->newSpyPresenter();

		$useCase = new GetGndDoku(
			$presenter,
			new NetworkSparqlQueryDispatcher( GndDokuFunction::DOKU_SPARQL_ENDPOINT )
		);

		$useCase->showGndDoku( null, self::VALID_CODINGS );

		$this->assertNull( $presenter->errorMessage );

		$this->assertSame( 'Satzart', $presenter->fieldDocs[0]->getLabel() );
		$this->assertSame( 'https://doku.wikibase.wiki/entity/P53', $presenter->fieldDocs[0]->getUrl() );

		$this->assertSame(
			[
				'PICA+' => '002@',
				'MARC 21 Format für Normdaten' => 'Satzkennung Pos. 06 = „z“',
				'PICA3' => '005',
			],
			$presenter->fieldDocs[0]->getFieldCodes()
		);

		$this->assertEquals(
			[
				'https://doku.wikibase.wiki/entity/Q151' => 'Körperschaft',
				'https://doku.wikibase.wiki/entity/Q152' => 'Konferenz',
				'https://doku.wikibase.wiki/entity/Q153' => 'Geografikum',
				'https://doku.wikibase.wiki/entity/Q154' => 'Sachbegriff',
				'https://doku.wikibase.wiki/entity/Q17' => 'Person oder Familie',
				'https://doku.wikibase.wiki/entity/Q155' => 'Werk'
			],
			$presenter->fieldDocs[0]->getSubfields()[3]->getPossibleValues()
		);

		$this->assertEquals(
			[
				'https://doku.wikibase.wiki/entity/Q1316' => '-ohne-',
				'https://doku.wikibase.wiki/entity/Q1320' => '008 Pos. 09 = „b“',
				'https://doku.wikibase.wiki/entity/Q1317' => '-ohne-'
			],
			$presenter->fieldDocs[0]->getSubfields()[2]->getSubfieldCodes()
		);
	}

	public function testInvalidLanguageCodeLeadsToErrorMessage(): void {
		$presenter = $this->newSpyPresenter();

		$useCase = new GetGndDoku(
			$presenter,
			new NetworkSparqlQueryDispatcher( GndDokuFunction::DOKU_SPARQL_ENDPOINT )
		);

		$useCase->showGndDoku( 'NOPE', self::VALID_CODINGS );

		$this->assertSame( 'Invalid language code. Supported: en, de', $presenter->errorMessage );
	}

	public function testEnglishViaNetwork(): void {
		$presenter = $this->newSpyPresenter();

		$useCase = new GetGndDoku(
			$presenter,
			new NetworkSparqlQueryDispatcher( GndDokuFunction::DOKU_SPARQL_ENDPOINT )
		);

		$useCase->showGndDoku( 'en', self::VALID_CODINGS );

		$this->assertNull( $presenter->errorMessage );

		$this->assertSame( 'Type of record', $presenter->fieldDocs[0]->getLabel() );

		$this->assertEquals(
			[
				'https://doku.wikibase.wiki/entity/Q151' => 'Corporate Body',
				'https://doku.wikibase.wiki/entity/Q153' => 'Place',
				'https://doku.wikibase.wiki/entity/Q17' => 'Person',
				'https://doku.wikibase.wiki/entity/Q152' => 'Conference',
				'https://doku.wikibase.wiki/entity/Q154' => 'Subject term',
				'https://doku.wikibase.wiki/entity/Q155' => 'Work'
			],
			$presenter->fieldDocs[0]->getSubfields()[3]->getPossibleValues()
		);
	}

}
