<?php

declare( strict_types = 1 );

namespace DNB\GND\Tests\Integration;

use DNB\GND\DokuApi;
use MediaWiki\Rest\RequestData;
use MediaWiki\Tests\Rest\Handler\HandlerTestTrait;
use MediaWikiIntegrationTestCase;

/**
 * @covers \DNB\GND\DokuApi
 * @covers \DNB\GND\GndServicesFactory
 * @covers \DNB\GND\Adapters\Presentation\ApiFullDokuPresenter
 */
class DokuApiTest extends MediaWikiIntegrationTestCase {
	use HandlerTestTrait;

	public function testHappyPath(): void {
		$response = $this->executeHandler(
			DokuApi::factory(),
			new RequestData( [ 'pathParams' => [ 'property_id' => 'P1' ] ] )
		);

		$this->assertSame( 'application/json', $response->getHeaderLine( 'Content-Type' ) );

		$data = json_decode( $response->getBody()->getContents(), true );
		$this->assertIsArray( $data, 'Body must be a JSON array' );

		$this->assertArrayHasKey( 'fields', $data );
		$this->assertIsArray( $data['fields'] );
	}

}
