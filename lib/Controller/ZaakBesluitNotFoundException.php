<?php

namespace OCA\ZaakAfhandelApp\Controller;

use Exception;

/**
 * Thrown when a zaakbesluit is absent or does not belong to the routed zaak.
 *
 * Mapped to HTTP 404 by ZaakBesluitenController; used by the zaak-scoped IDOR
 * guard so a relation of another case is never disclosed.
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 */
class ZaakBesluitNotFoundException extends Exception
{
}//end class
