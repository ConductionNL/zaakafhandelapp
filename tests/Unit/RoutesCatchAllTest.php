<?php

declare(strict_types=1);

namespace OCA\ZaakAfhandelApp\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Guards the SPA catch-all in appinfo/routes.php against swallowing the ZGW API.
 *
 * Being LAST in the `routes` array is NOT enough, which is the whole trap.
 * Nextcloud's RouteParser processes the `routes` array before the `resources`
 * array (RouteParser::parseDefaultRoutes) and Symfony matches in insertion
 * order, so the catch-all registers ahead of every route generated from the
 * seventeen `api/...` resources. `.+` matches slashes, so without the
 * `(?!api/)` lookahead the catch-all answered GET api/taken, api/klanten and
 * api/zrc/zaken with the SPA shell — at HTTP 200, so a JSON caller received
 * HTML and nothing failed loudly.
 *
 * @group routes
 */
class RoutesCatchAllTest extends TestCase {
	/**
	 * @return array<string, mixed> The catch-all route definition.
	 */
	private function catchAllRoute(): array {
		$config = include __DIR__ . '/../../appinfo/routes.php';
		$matches = array_values(
			array_filter(
				$config['routes'],
				static fn (array $route): bool => ($route['name'] ?? '') === 'dashboard#catchAll'
			)
		);

		$this->assertCount(1, $matches, 'exactly one SPA catch-all must be declared');

		return $matches[0];
	}//end catchAllRoute()

	/**
	 * The catch-all must stay last so every explicit route keeps priority.
	 *
	 * @return void
	 */
	public function testCatchAllIsDeclaredLast(): void {
		$config = include __DIR__ . '/../../appinfo/routes.php';
		$last = end($config['routes']);

		$this->assertSame('dashboard#catchAll', $last['name'], 'the catch-all must stay last so explicit routes win');
	}//end testCatchAllIsDeclaredLast()

	/**
	 * The catch-all must never match a ZGW API path.
	 *
	 * @return void
	 */
	public function testCatchAllDoesNotMatchApiPaths(): void {
		$route = $this->catchAllRoute();
		$pattern = '#^' . $route['requirements']['path'] . '$#';

		$apiPaths = [
			'api/taken',
			'api/klanten',
			'api/berichten',
			'api/zrc/zaken',
			'api/zrc/zaken/abc-123/besluiten',
			'api/ztc/zaaktypen',
			'api/drc/enkelvoudiginformatieobjecten',
		];

		foreach ($apiPaths as $path) {
			$this->assertSame(0, preg_match($pattern, $path), "the catch-all must NOT match $path");
		}
	}//end testCatchAllDoesNotMatchApiPaths()

	/**
	 * The catch-all must still serve every SPA deep link.
	 *
	 * @return void
	 */
	public function testCatchAllStillMatchesSpaDeepLinks(): void {
		$route = $this->catchAllRoute();
		$pattern = '#^' . $route['requirements']['path'] . '$#';

		$spaPaths = [
			'zaken',
			'zaken/abc-123',
			'klanten',
			'auditTrail',
			'features-roadmap',
		];

		foreach ($spaPaths as $path) {
			$this->assertSame(1, preg_match($pattern, $path), "the catch-all MUST match $path");
		}
	}//end testCatchAllStillMatchesSpaDeepLinks()
}//end class
