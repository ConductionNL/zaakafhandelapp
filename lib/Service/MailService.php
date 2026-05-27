<?php

namespace OCA\ZaakAfhandelApp\Service;

use OCP\IURLGenerator;
use OCP\Mail\IMailer;

/**
 * Service class for sending e-mails
 *
 * This service sends e-mails when an 'medewerker' field is filled on an object.
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 */
class MailService
{
    /**
     * Constructor for MailService.
     */
    public function __construct(
        private readonly IMailer $mailer,
        private readonly IURLGenerator $urlGenerator,
    ) {
    }//end __construct()

    /**
     * Sends an e-mail when a task is connected to an employee.
     *
     * Security notes:
     * - medewerker is validated as a well-formed e-mail address before use as To address to
     *   prevent open-relay / spam-primitive abuse (#272).
     * - The deep-equality guard now compares the medewerker field specifically rather than
     *   using === on full object arrays (distinct PHP arrays are never ===; the old guard
     *   was never true and mail was sent on every update regardless of change — #272).
     * - User-supplied values are HTML-escaped before interpolation into the mail body (#272).
     *
     * @param array $oldObject The previous version of the object (to check if the field changes)
     * @param array $newObject The current version of the object.
     *
     * @return array The current version of the object.
     * @throws \Exception
     *
     * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-005
     */
    public function sendMail(array $oldObject, array $newObject): array
    {
        if (isset($newObject['medewerker']) === false) {
            return $newObject;
        }

        // Skip if the medewerker field has not changed compared to the previous version.
        if (isset($oldObject['medewerker']) === true && $oldObject['medewerker'] === $newObject['medewerker']) {
            return $newObject;
        }

        $email = $newObject['medewerker'];

        // Validate that the medewerker value is a properly-formatted e-mail address before
        // using it as the To address, to prevent open-relay abuse (#272).
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            // Not a valid e-mail; skip silently to avoid leaking validation errors in the
            // object response, but do not send the message.
            return $newObject;
        }

        // HTML-escape task data before interpolation to prevent stored-HTML injection (#272).
        $taskId    = htmlspecialchars((string) ($newObject['id'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $taskTitle = htmlspecialchars((string) ($newObject['title'] ?? 'taak'), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $baseUrl   = htmlspecialchars($this->urlGenerator->getBaseUrl(), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $message = $this->mailer->createMessage();
        $message->setSubject('KISS: Er is een taak aan u toegewezen');
        $message->setTo([$email]);
        $message->setHtmlBody(
            body: "<!doctype html>
<html lang='nl'>
<body>
<p>Er is een taak (<strong>$taskTitle</strong>) aan u toegewezen. Klik
<a href='$baseUrl/apps/zaakafhandelapp/taken/$taskId'>hier</a>
om naar de taak te gaan.</p>
</body>
</html>"
        );

        $this->mailer->send($message);

        return $newObject;
    }//end sendMail()
}//end class
