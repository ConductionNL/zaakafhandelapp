<?php

/**
 * Unit tests for the appstore-metadata specification.
 *
 * @category Tests
 * @package  OCA\ZaakAfhandelApp\Tests\Unit
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

namespace OCA\ZaakAfhandelApp\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Executes openspec/specs/appstore-metadata/spec.md.
 *
 * The listing is the one artefact users read before installing, and it is the
 * easiest place in the repo for a claim to outlive the code behind it. Every
 * scenario in that spec is a statement about two static files — appinfo/info.xml
 * and README.md — so each is asserted directly here.
 *
 * These are deliberately NOT Playwright specs. Nothing in this spec is a
 * browser-observable flow: there is no UI in which "the description mentions no
 * gateway" can be clicked. gate-19 (e2e-coverage) is satisfied by an
 * `@e2e exclude` on the spec that points at this file, and the exclusion is
 * only honest because these tests exist and run.
 *
 * The forbidden-term assertions carry the reason in the failure message on
 * purpose: a future reader who reintroduces "gateway" into the description
 * should learn WHY it is banned from the failure, not have to find this spec.
 *
 * @spec openspec/specs/appstore-metadata/spec.md
 */
class AppstoreMetadataTest extends TestCase
{

    /**
     * Repository root, derived from this file's location.
     *
     * @var string
     */
    private string $root;

    /**
     * Parsed appinfo/info.xml.
     *
     * @var \SimpleXMLElement
     */
    private \SimpleXMLElement $info;

    /**
     * Contents of README.md.
     *
     * @var string
     */
    private string $readme;


    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 2);

        $infoPath = $this->root.'/appinfo/info.xml';
        $this->assertFileExists($infoPath, 'appinfo/info.xml is the appstore listing itself');

        $xml = simplexml_load_file($infoPath);
        $this->assertNotFalse($xml, 'appinfo/info.xml must be well-formed XML');
        $this->info = $xml;

        $readmePath = $this->root.'/README.md';
        $this->assertFileExists($readmePath);
        $this->readme = (string) file_get_contents($readmePath);
    }//end setUp()


    /**
     * Language of a listing element, defaulting to English.
     *
     * Nextcloud's info.xsd declares a PLAIN `lang` attribute, not `xml:lang` —
     * appinfo/info.xml uses `<summary lang="nl">`. Reading only the
     * XML-namespaced attribute reports every Dutch variant as missing, which is
     * how the first version of this test "found" a defect that was its own.
     * Both spellings are accepted so the test measures the file rather than a
     * guess about it.
     *
     * @param \SimpleXMLElement $element A <summary> or <description> element.
     *
     * @return string The language code ('en' when unqualified).
     */
    private function languageOf(\SimpleXMLElement $element): string
    {
        $lang = (string) ($element->attributes('xml', true)->lang ?? '');
        if ($lang === '') {
            $lang = (string) ($element->attributes()->lang ?? '');
        }

        return $lang === '' ? 'en' : $lang;
    }//end languageOf()


    /**
     * Return the text of every `<summary>` and `<description>`, keyed for
     * readable failures.
     *
     * @return array<string, string>
     */
    private function listingText(): array
    {
        $out = [];
        foreach (['summary', 'description'] as $tag) {
            $index = 0;
            foreach ($this->info->{$tag} as $element) {
                $out[$tag.'['.$this->languageOf($element).']#'.$index] = (string) $element;
                $index++;
            }
        }

        return $out;
    }//end listingText()


    /**
     * Scenario: Description matches the shipped feature set.
     *
     * The positive half of this scenario ("every feature claim maps to an
     * implemented surface") is a human judgement and is not asserted here.
     * The negative half is exact and is: five terms name products and
     * architectures this app is not, and each one has been in this listing
     * before.
     *
     * @return void
     */
    public function testListingDoesNotClaimGatewayOrCatalogiFunctionality(): void
    {
        $forbidden = ['gateway', 'service bus', 'cloud event', 'opencatalogi', 'federated catalogi'];

        foreach ($this->listingText() as $where => $text) {
            foreach ($forbidden as $term) {
                $this->assertStringNotContainsStringIgnoringCase(
                    $term,
                    $text,
                    sprintf(
                        '%s claims "%s". This app is case handling on OpenRegister, not a gateway '
                        .'or catalogue product (appstore-metadata REQ-META-001).',
                        $where,
                        $term
                    )
                );
            }
        }
    }//end testListingDoesNotClaimGatewayOrCatalogiFunctionality()


    /**
     * Scenario: Description matches the shipped feature set — the summary is a
     * complete, untruncated sentence.
     *
     * @return void
     */
    public function testSummaryIsAnUntruncatedSentence(): void
    {
        foreach ($this->info->summary as $summary) {
            $text = trim((string) $summary);

            $this->assertNotSame('', $text, 'summary must not be empty');
            $this->assertStringEndsWith(
                '.',
                $text,
                'summary must be a complete sentence, not a truncated fragment: '.$text
            );
            $this->assertStringNotContainsString('…', $text, 'summary must not be elided');
            $this->assertStringNotContainsString('...', $text, 'summary must not be elided');
        }
    }//end testSummaryIsAnUntruncatedSentence()


    /**
     * Scenario: Dutch variants present.
     *
     * @return void
     */
    public function testDutchVariantsArePresentForSummaryAndDescription(): void
    {
        foreach (['summary', 'description'] as $tag) {
            $languages = [];
            foreach ($this->info->{$tag} as $element) {
                $languages[] = $this->languageOf($element);
            }

            $this->assertContains(
                'en',
                $languages,
                sprintf('<%s> must have an English (unqualified) variant; got %s', $tag, implode(',', $languages))
            );
            $this->assertContains(
                'nl',
                $languages,
                sprintf(
                    '<%s lang="nl"> is missing. This is a Dutch municipal product; the Dutch listing is '
                    .'the one most of its users read (appstore-metadata REQ-META-001).',
                    $tag
                )
            );
        }
    }//end testDutchVariantsArePresentForSummaryAndDescription()


    /**
     * Scenario: Dutch variants present — and are not empty stand-ins.
     *
     * @return void
     */
    public function testDutchVariantsCarryRealText(): void
    {
        foreach (['summary', 'description'] as $tag) {
            foreach ($this->info->{$tag} as $element) {
                if ($this->languageOf($element) !== 'nl') {
                    continue;
                }

                $this->assertGreaterThan(
                    20,
                    strlen(trim((string) $element)),
                    sprintf('<%s lang="nl"> is present but too short to carry the product claims', $tag)
                );
            }
        }
    }//end testDutchVariantsCarryRealText()


    /**
     * Scenario: Cron claim absent.
     *
     * The GIVEN is asserted rather than assumed: the listing may only stay
     * silent about cron for as long as there genuinely is no background job.
     * If someone adds one, this fails and the listing has to be revisited —
     * which is the correct outcome, and the opposite of what a bare
     * "no cron mentioned" assertion would do.
     *
     * @return void
     */
    public function testListingDoesNotRequireCronWhileNoBackgroundJobExists(): void
    {
        $libFiles = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->root.'/lib', \FilesystemIterator::SKIP_DOTS)
        );

        $backgroundJobs = [];
        foreach ($libFiles as $file) {
            if ($file->isFile() === false || $file->getExtension() !== 'php') {
                continue;
            }

            $source = (string) file_get_contents($file->getPathname());
            if (preg_match('/\bOCP\\\\BackgroundJob\b|\bextends\s+(Time|Queued)dJob\b|\bextends\s+Job\b/', $source) === 1) {
                $backgroundJobs[] = $file->getPathname();
            }
        }

        $this->assertSame(
            [],
            $backgroundJobs,
            'lib/ now contains background jobs. The appstore listing states no cron requirement — '
            .'revisit appinfo/info.xml and appstore-metadata REQ-META-002 before adding one.'
        );

        foreach ($this->listingText() as $where => $text) {
            $this->assertDoesNotMatchRegularExpression(
                '/\b(system\s+)?cron\b/i',
                $text,
                sprintf('%s claims a cron requirement the app does not have (REQ-META-002)', $where)
            );
        }
    }//end testListingDoesNotRequireCronWhileNoBackgroundJobExists()


    /**
     * Scenario: Elasticsearch removed from promise surface.
     *
     * Search belongs to OpenRegister, the foundation — it is not
     * re-implemented per app. Pre-existing elastic CONFIG KEYS may remain
     * (app-configuration spec); what is banned is marketing it as a feature,
     * so only the listing and the README are inspected, not lib/.
     *
     * @return void
     */
    public function testElasticsearchIsNotPresentedAsASearchBackend(): void
    {
        foreach ($this->listingText() as $where => $text) {
            $this->assertStringNotContainsStringIgnoringCase(
                'elasticsearch',
                $text,
                sprintf('%s presents Elasticsearch as a search backend (REQ-META-003)', $where)
            );
        }

        $this->assertStringNotContainsStringIgnoringCase(
            'elasticsearch',
            $this->readme,
            'README.md presents Elasticsearch as a search backend. Search is provided by '
            .'OpenRegister (REQ-META-003).'
        );
    }//end testElasticsearchIsNotPresentedAsASearchBackend()


    /**
     * Scenario: Revert promise removed.
     *
     * Only the audit history READ exists (zgw-client-interaction REQ-004).
     * A revert claim in the README promises a write path that is not there.
     *
     * @return void
     */
    public function testReadmeDoesNotPromiseAuditRevert(): void
    {
        $this->assertDoesNotMatchRegularExpression(
            '/\brevert(ing|s|ed)?\b/i',
            $this->readme,
            'README.md promises a revert capability. Only the audit history read exists '
            .'(zgw-client-interaction REQ-004 / appstore-metadata REQ-META-004).'
        );
    }//end testReadmeDoesNotPromiseAuditRevert()


    /**
     * Scenario: Diagram nodes backed by code.
     *
     * Asserts the negative half — the three nodes named in REQ-META-004 as
     * having no backing code. "Every node corresponds to an existing
     * integration" in general is not machine-checkable; these three are, and
     * they are the ones that were actually wrong.
     *
     * @return void
     */
    public function testReadmeDiagramHasNoUnbackedNodes(): void
    {
        foreach (['Elasticsearch', 'Cron', 'Nextcloud Activity'] as $node) {
            $this->assertStringNotContainsString(
                $node,
                $this->readme,
                sprintf(
                    'README.md still shows a "%s" node. No code backs it (appstore-metadata REQ-META-004).',
                    $node
                )
            );
        }
    }//end testReadmeDiagramHasNoUnbackedNodes()
}//end class
