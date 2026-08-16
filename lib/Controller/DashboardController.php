<?php

namespace OCA\ZaakAfhandelApp\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;

/**
 * Serves the SPA page shell for the app's dashboard and search routes.
 *
 * This controller used to also register the `api/dashboard` resource quintet
 * (index/show/create/update/destroy). Those five endpoints served a hardcoded
 * four-row demo constant (`TEST_ARRAY`: "Github", "Gitlab", "Woo", "Decat")
 * rather than any real data, had no caller anywhere in `src/`, and were
 * `@NoAdminRequired`, so gate-7 counted three of them as unguarded direct
 * object references. They were demo scaffolding shipping to production, not an
 * IDOR — see ConductionNL/zaakafhandelapp#347, which classes them as a
 * stub-scan matter — and they are removed here together with their route,
 * rather than given a guard they never needed.
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 */
class DashboardController extends Controller {
	public function __construct(
		$appName,
		IRequest $request,
	) {
		parent::__construct($appName, $request);
	}//end __construct()

	/**
	 * This returns the template of the main app's page
	 * It adds some data to the template (app version)
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return TemplateResponse
	 *
	 * @spec openspec/specs/app-configuration/spec.md#REQ-003
	 */
	public function page(): TemplateResponse {
		return new TemplateResponse(
			'zaakafhandelapp',
			'index',
			[]
		);
	}//end page()
}//end class
