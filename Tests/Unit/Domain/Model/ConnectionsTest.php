<?php

declare(strict_types=1);

namespace GdprExtensionsCom\GdprExtensionsComWm\Tests\Unit\Domain\Model;

use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case
 */
class ConnectionsTest extends UnitTestCase
{
    /**
     * @var \GdprExtensionsCom\GdprExtensionsComWm\Domain\Model\Connections|MockObject|AccessibleObjectInterface
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->getAccessibleMock(
            \GdprExtensionsCom\GdprExtensionsComWm\Domain\Model\Connections::class,
            ['dummy']
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getApitokenReturnsInitialValueForString|null(): void
    {
    }

    /**
     * @test
     */
    public function setApitokenForString|nullSetsApitoken(): void
    {
    }
}
