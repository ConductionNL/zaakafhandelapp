<?php


namespace OCA\ZaakAfhandelApp\Dashboard;

use OCP\Dashboard\IWidget;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Util;

use OCA\ZaakAfhandelApp\AppInfo\Application;

class OrganisatiesWidget implements IWidget
{
    public function __construct(
        private IL10N $l10n,
        private IURLGenerator $url
    ) {
    }//end __construct()

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return 'zaakAfhandelApp_organisaties_widget';
    }//end getId()

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return $this->l10n->t('Organisaties zoeken');
    }//end getTitle()

    /**
     * @inheritDoc
     */
    public function getOrder(): int
    {
        return 10;
    }//end getOrder()

    /**
     * @inheritDoc
     */
    public function getIconClass(): string
    {
        return 'icon-zaken-widget';
    }//end getIconClass()

    /**
     * @inheritDoc
     */
    public function getUrl(): ?string
    {
        return null;
    }//end getUrl()

    /**
     * @inheritDoc
     */
    public function load(): void
    {
        Util::addScript(Application::APP_ID, Application::APP_ID.'-organisatiesWidget');
        Util::addStyle(Application::APP_ID, 'dashboardWidgets');
        Util::addStyle(Application::APP_ID, 'icons');
    }//end load()
}//end class
