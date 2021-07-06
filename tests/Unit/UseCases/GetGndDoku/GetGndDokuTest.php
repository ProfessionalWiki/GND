<?php

declare( strict_types = 1 );

namespace DNB\GND\Tests\Unit\UseCases\GetGndDoku;

use DNB\GND\Adapters\DataAccess\NetworkSparqlQueryDispatcher;
use DNB\GND\GndHooks;
use DNB\GND\UseCases\GetGndDoku\FieldDoku;
use DNB\GND\UseCases\GetGndDoku\GetGndDoku;
use DNB\GND\UseCases\GetGndDoku\GndDokuPresenter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DNB\GND\UseCases\GetGndDoku\GetGndDoku
 */
class GetGndDokuTest extends TestCase {

	public function testNetworkErrorLeadsToErrorMessage(): void {
		$presenter = $this->newSpyPresenter();

		$useCase = new GetGndDoku(
			$presenter,
			new NetworkSparqlQueryDispatcher( 'http://zsdjklsdfjklsdrjkl.wikibase.wiki/404' )
		);

		$useCase->showGndDoku();

		$this->assertSame( 'Could not obtain SPARQL result', $presenter->errorMessage );
	}

	private function newSpyPresenter() {
		return new class() implements GndDokuPresenter {
			/**
			 * @var FieldDoku[]
			 */
			public array $fieldDocs = [];
			public ?string $errorMessage = null;

			public function showGndDoku( FieldDoku ...$fieldDocs ): void {
				$this->fieldDocs = $fieldDocs;
			}

			public function showErrorMessage( string $error ): void {
				$this->errorMessage = $error;
			}
		};
	}

	public function testHappyPathViaNetwork(): void {
		$presenter = $this->newSpyPresenter();

		$useCase = new GetGndDoku(
			$presenter,
			new NetworkSparqlQueryDispatcher( GndHooks::DOKU_SPARQL_ENDPOINT )
		);

		$useCase->showGndDoku();

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
				'https://doku.wikibase.wiki/entity/Q17' => 'Person',
				'https://doku.wikibase.wiki/entity/Q312' => 'Werk'
			],
			$presenter->fieldDocs[0]->getSubfields()[1]->getPossibleValues()
		);
	}



}
