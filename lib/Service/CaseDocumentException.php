<?php

namespace OCA\ZaakAfhandelApp\Service;

use Exception;

/**
 * Thrown by CaseDocumentService when a document file operation fails.
 *
 * Typed so callers never swallow a storage failure as a silent null
 * (no catch-return-null fail-open).
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 */
class CaseDocumentException extends Exception
{
}//end class
