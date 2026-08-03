<?php

/**
 * Unit tests for ObjectQueryService's faceting delegation.
 *
 * @category Tests
 * @package  OCA\ZaakAfhandelApp\Tests\Unit\Service
 *
 * @author    Conduction Development Team <info@conduction.nl>
 * @copyright 2024 Conduction B.V.
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * @link https://github.com/ConductionNL/zaakafhandelapp
 */

declare(strict_types=1);

namespace OCA\ZaakAfhandelApp\Tests\Unit\Service;

use OCA\OpenRegister\Service\ObjectService as OpenRegisterObjectService;
use OCA\ZaakAfhandelApp\Service\ObjectMapperService;
use OCA\ZaakAfhandelApp\Service\ObjectQueryService;
use OCA\ZaakAfhandelApp\Service\RequestParamsParser;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * Locks the OpenRegister faceting call in ObjectQueryService::getFacets().
 *
 * getFacets() used to call `$mapper->getAggregations()`, a method that exists
 * nowhere in OpenRegister. The call sat behind an
 * `instanceof \OCA\OpenRegister\Service\ObjectService` guard, and that guard is
 * satisfied in production — so the only environment in which the branch was
 * skipped was one without OpenRegister installed. Every real faceted query
 * therefore raised an uncaught Error, while a suite running without the app
 * looked green.
 *
 * The first test below is the one that matters: it asserts the method name
 * getFacets() depends on is actually declared on the OpenRegister class, so a
 * renamed or invented method is caught by name rather than by waiting for a
 * runtime fatal.
 */
class ObjectQueryServiceTest extends TestCase
{
    /**
     * The method getFacets() calls must exist on the OpenRegister service.
     *
     * This resolves against the real OpenRegister class when the app is loaded
     * and against tests/Stubs/Service/ObjectService.php otherwise. Asserting
     * the negative too keeps the stub honest: if someone re-adds the phantom
     * getAggregations() to the stub to make a mock pass, this fails.
     *
     * @return void
     */
    public function testOpenRegisterDeclaresTheFacetingMethodWeCall(): void
    {
        $this->assertTrue(
            method_exists(OpenRegisterObjectService::class, 'getFacetsForObjects'),
            'OpenRegister ObjectService must declare getFacetsForObjects() — ObjectQueryService::getFacets() calls it.'
        );

        $this->assertFalse(
            method_exists(OpenRegisterObjectService::class, 'getAggregations'),
            'getAggregations() does not exist in OpenRegister. If this ever passes, the stub has drifted away from the real API and is hiding a live fatal.'
        );
    }

    /**
     * getFacets() delegates to getFacetsForObjects() and returns its result.
     *
     * @return void
     */
    public function testGetFacetsDelegatesToOpenRegister(): void
    {
        $filters = ['zaaktype' => 'abc'];
        $facets  = ['status' => ['open' => 3]];

        $mapper = $this->getMockBuilder(OpenRegisterObjectService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getFacetsForObjects'])
            ->getMockForAbstractClass();

        $mapper->expects($this->once())
            ->method('getFacetsForObjects')
            ->with($filters)
            ->willReturn($facets);

        $mapperService = $this->createMock(ObjectMapperService::class);
        $mapperService->method('getMapper')->willReturn($mapper);

        $service = new ObjectQueryService($mapperService, $this->createMock(RequestParamsParser::class));

        $this->assertSame($facets, $service->getFacets('zaak', $filters));
    }

    /**
     * A mapper that is not an OpenRegister service yields no facets.
     *
     * Negative control for the test above: without this, the delegation test
     * would still pass if getFacets() were changed to always return [].
     *
     * @return void
     */
    public function testGetFacetsReturnsEmptyForNonOpenRegisterMapper(): void
    {
        $mapperService = $this->createMock(ObjectMapperService::class);
        $mapperService->method('getMapper')->willReturn(new \stdClass());

        $service = new ObjectQueryService($mapperService, $this->createMock(RequestParamsParser::class));

        $this->assertSame([], $service->getFacets('zaak', ['a' => 'b']));
    }

    /**
     * getFacets() must keep the signature getResultArrayForRequest() calls it with.
     *
     * @return void
     */
    public function testGetFacetsSignatureIsStable(): void
    {
        $method = new ReflectionMethod(ObjectQueryService::class, 'getFacets');

        $this->assertSame('array', (string) $method->getReturnType());
        $this->assertSame(2, $method->getNumberOfParameters());
    }
}
