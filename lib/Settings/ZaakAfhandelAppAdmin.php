<?php
namespace OCA\ZaakAfhandelApp\Settings;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IL10N;
use OCP\Settings\ISettings;

/**
 * Admin settings form for the ZaakAfhandelApp.
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 */
class ZaakAfhandelAppAdmin implements ISettings
{

    private IL10N $l;

    private IConfig $config;

    public function __construct(IConfig $config, IL10N $l)
    {
        $this->config = $config;
        $this->l      = $l;
    }//end __construct()

    /**
     * @return TemplateResponse
     */
    public function getForm()
    {
        $parameters = [
            'mySetting' => $this->config->getSystemValue('zaakafhandelapp_setting', true),
        ];

        // RENDER_AS_BLANK ('') is the value a settings form needs: the settings
        // page embeds the rendered output, so any wrapping layout would nest a
        // second full document inside it. 'admin' is not a valid renderAs and
        // TemplateResponse::render() silently fell back to RENDER_AS_USER.
        return new TemplateResponse(
            'zaakafhandelapp',
            'settings/admin',
            $parameters,
            TemplateResponse::RENDER_AS_BLANK
        );
    }//end getForm()

    public function getSection()
    {
        return 'zaakafhandelapp';
        // Name of the previously created section.
    }//end getSection()

    /**
     * @return integer whether the form should be rather on the top or bottom of
     * the admin section. The forms are arranged in ascending order of the
     * priority values. It is required to return a value between 0 and 100.
     *
     * E.g.: 70
     */
    public function getPriority()
    {
        return 10;
    }//end getPriority()
}//end class
