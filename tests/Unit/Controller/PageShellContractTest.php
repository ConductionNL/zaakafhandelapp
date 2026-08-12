<?php

/**
 * Wire-contract tests for the SPA shell page routes.
 *
 * @category Tests
 * @package  OCA\ZaakAfhandelApp\Tests\Unit\Controller
 *
 * @author    Conduction Development Team <info@conduction.nl>
 * @copyright 2026 Conduction B.V.
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: 2026 Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 *
 * @link https://github.com/ConductionNL/zaakafhandelapp
 */

declare(strict_types=1);

namespace OCA\ZaakAfhandelApp\Tests\Unit\Controller;

use OCA\ZaakAfhandelApp\Controller\BerichtenController;
use OCA\ZaakAfhandelApp\Controller\ContactMomentenController;
use OCA\ZaakAfhandelApp\Controller\DashboardController;
use OCA\ZaakAfhandelApp\Controller\MedewerkersController;
use OCA\ZaakAfhandelApp\Controller\ZaakInformatieObjectenController;
use OCA\ZaakAfhandelApp\Service\ObjectService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IAppConfig;
use OCP\IRequest;
use OCP\IUserSession;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Locks the wire contract of the SPA shell routes:
 *
 *   GET /berichten/{id}            -> berichten#page
 *   GET /contactmomenten/{id}      -> contactMomenten#page
 *   GET /zoeken                    -> dashboard#page
 *   GET /medewerkers/{id}          -> medewerkers#page
 *   GET /zaakinformatieobjecten    -> zaakInformatieObjecten#page
 *
 * These render no data — they hand the browser the app shell and let the Vue
 * router take over. That does NOT make them contract-free, and it is why they
 * are tested here rather than annotated `@contract exclude`. Three things are
 * asserted, and each has a distinct failure the user sees:
 *
 *   1. HTTP 200 with RENDER_AS_USER. A deep link that answers 500, or renders
 *      as a guest/blank page, is a dead bookmark — the SPA never boots.
 *   2. the `index` template of THIS app. A page route pointed at the `error`
 *      template still answers 200 and still renders; it just never loads the
 *      bundle. Naming the template is the only way to tell those apart.
 *   3. an empty parameter array. The shell is state-free by design; server-side
 *      state belongs in IInitialState (ADR-004), not smuggled into template
 *      params where the frontend would have to scrape it out of the DOM.
 *
 * The three deep-link routes additionally widen connect-src, without which the
 * SPA's calls to the configured ZGW endpoints are blocked by CSP on exactly the
 * pages that need them.
 */
class PageShellContractTest extends TestCase {

	/**
	 * Every controller whose page() renders the SPA shell.
	 *
	 * @return array<string, array{0: class-string}>
	 */
	public static function pageProvider(): array {
		return [
			'berichten#page' => [BerichtenController::class],
			'contactMomenten#page' => [ContactMomentenController::class],
			'medewerkers#page' => [MedewerkersController::class],
			'dashboard#page' => [DashboardController::class],
			'zaakInformatieObjecten#page' => [ZaakInformatieObjectenController::class],
		];
	}//end pageProvider()

	/**
	 * The subset of page routes that additionally relax connect-src — the
	 * deep-linkable detail pages, from which the SPA calls out to the
	 * configured ZGW endpoints.
	 *
	 * @return array<string, array{0: class-string}>
	 */
	public static function cspPageProvider(): array {
		return [
			'berichten#page' => [BerichtenController::class],
			'contactMomenten#page' => [ContactMomentenController::class],
			'medewerkers#page' => [MedewerkersController::class],
		];
	}//end cspPageProvider()

	/**
	 * Build the named controller and call its page() with the arity it declares.
	 *
	 * The three deep-link controllers take the optional route parameter; the
	 * two flat ones take none.
	 *
	 * @param class-string $class Controller class to build.
	 *
	 * @return TemplateResponse The rendered shell response.
	 */
	private function renderPage(string $class): TemplateResponse {
		$request = $this->createMock(IRequest::class);
		$session = $this->createMock(IUserSession::class);

		if ($class === DashboardController::class) {
			$controller = new DashboardController(
				'zaakafhandelapp',
				$request,
				$this->createMock(IAppConfig::class),
				$session
			);
			return $controller->page();
		}

		if ($class === ZaakInformatieObjectenController::class) {
			$controller = new ZaakInformatieObjectenController(
				'zaakafhandelapp',
				$request,
				$this->createMock(IAppConfig::class),
				$session
			);
			return $controller->page();
		}

		$controller = new $class(
			'zaakafhandelapp',
			$request,
			$this->createMock(ObjectService::class),
			$session,
			$this->createMock(LoggerInterface::class)
		);

		return $controller->page('some-id');
	}//end renderPage()

	/**
	 * Every page route serves this app's `index` shell as a 200, rendered for a
	 * logged-in user, carrying no server-side state.
	 *
	 * @param class-string $class Controller under test.
	 *
	 * @return void
	 */
	#[DataProvider('pageProvider')]
	public function testPageServesTheAppShell(string $class): void {
		$response = $this->renderPage($class);

		$this->assertSame(Http::STATUS_OK, $response->getStatus(), $class . ' status');
		$this->assertSame('zaakafhandelapp', $response->getApp(), $class . ' app');
		$this->assertSame('index', $response->getTemplateName(), $class . ' template');
		$this->assertSame(TemplateResponse::RENDER_AS_USER, $response->getRenderAs(), $class . ' renderAs');
		$this->assertSame([], $response->getParams(), $class . ' params');
	}//end testPageServesTheAppShell()

	/**
	 * The deep-link page routes relax connect-src so the SPA can reach the
	 * configured ZGW endpoints from them.
	 *
	 * @param class-string $class Controller under test.
	 *
	 * @return void
	 */
	#[DataProvider('cspPageProvider')]
	public function testDeepLinkPagesWidenConnectSrc(string $class): void {
		$policy = $this->renderPage($class)->getContentSecurityPolicy()->buildPolicy();

		$this->assertMatchesRegularExpression(
			'/connect-src[^;]*\*/',
			$policy,
			$class . ' must allow the SPA to connect out'
		);
	}//end testDeepLinkPagesWidenConnectSrc()
}//end class
