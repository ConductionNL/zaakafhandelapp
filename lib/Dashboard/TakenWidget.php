<?php


namespace OCA\ZaakAfhandelApp\Dashboard;

use OCP\Dashboard\IWidget;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Util;

use OCA\ZaakAfhandelApp\AppInfo\Application;

class TakenWidget implements IWidget
{

    public function __construct(
        private IL10N $l10n,
        private IURLGenerator $url
    ) {}

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return 'zaakAfhandelApp_taak_widget';
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return $this->l10n->t('Uw taken');
    }

    /**
     * @inheritDoc
     */
    public function getOrder(): int
    {
        return 10;
    }

    /**
     * @inheritDoc
     */
    public function getIconClass(): string
    {
        return 'icon-zaken-widget';
    }

    /**
     * @inheritDoc
     */
    public function getUrl(): ?string
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function load(): void
    {
        Util::addScript(Application::APP_ID, Application::APP_ID . '-takenWidget');
        Util::addStyle(Application::APP_ID, 'dashboardWidgets');
    }
}
