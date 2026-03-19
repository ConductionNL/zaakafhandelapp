<?php
namespace OCA\ZaakAfhandelApp\Sections;

use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Settings\IIconSection;

class ZaakAfhandelAppAdmin implements IIconSection
{

    private IL10N $l;

    private IURLGenerator $urlGenerator;

    public function __construct(IL10N $l, IURLGenerator $urlGenerator)
    {
        $this->l            = $l;
        $this->urlGenerator = $urlGenerator;
    }//end __construct()

    public function getIcon(): string
    {
        return $this->urlGenerator->imagePath('core', 'actions/settings-dark.svg');
    }//end getIcon()

    public function getID(): string
    {
        return 'zaakafhandelapp';
    }//end getID()

    public function getName(): string
    {
        return $this->l->t('Zaak Afhandelapp');
    }//end getName()

    public function getPriority(): int
    {
        return 97;
    }//end getPriority()
}//end class
