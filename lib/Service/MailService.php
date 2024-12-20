<?php

namespace OCA\ZaakAfhandelApp\Service;

use OCP\IURLGenerator;
use OCP\Mail\IMailer;

/**
 * Service class for sending e-mails
 *
 * This service sends e-mails when an 'medewerker' field is filled on an object.
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
	}

	/**
	 * Sends an e-mail when a task is connected to an employee.
	 *
	 * @param array $oldObject The previous version of the object (to check if the field changes)
	 * @param array $newObject The current version of the object.
	 *
	 * @return array The current version of the object.
	 * @throws \Exception
	 */
	public function sendMail(array $oldObject, array $newObject): array
	{
		if(isset($newObject['medewerker']) === false) {
			return $newObject;
		} else if (isset($oldObject['medewerker']) === true && $oldObject === $newObject) {
			return $newObject;
		}

		$email = $newObject['medewerker'];

		$message = $this->mailer->createMessage();
		$message->setSubject('KISS: Er is een taak aan u toegewezen');
		$message->setTo([$email]);
		$message->setHtmlBody(body: "
				<!doctype html>
				<html lang='nl'>
					<body>
						Er is een taak aan u toegewezen. Klik
						<a href='".$this->urlGenerator->getBaseUrl()."/apps/zaakafhandelapp/taken/{$newObject["id"]}'>
							hier
						</a>
						om naar de taak te gaan.
					</body>
				</html>"
		);

		$this->mailer->send($message);

		return $newObject;
	}

}
