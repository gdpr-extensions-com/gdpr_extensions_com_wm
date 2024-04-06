<?php

declare(strict_types=1);

namespace GdprExtensionsCom\GdprExtensionsComWm\Tests\Unit\Controller;

use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use TYPO3Fluid\Fluid\View\ViewInterface;

/**
 * Test case
 */
class ConnectionsControllerTest extends UnitTestCase
{
    /**
     * @var \GdprExtensionsCom\GdprExtensionsComWm\Controller\ConnectionsController|MockObject|AccessibleObjectInterface
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = $this->getMockBuilder($this->buildAccessibleProxy(\GdprExtensionsCom\GdprExtensionsComWm\Controller\ConnectionsController::class))
            ->onlyMethods(['redirect', 'forward', 'addFlashMessage'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function listActionFetchesAllConnectionsFromRepositoryAndAssignsThemToView(): void
    {
        $allConnections = $this->getMockBuilder(\TYPO3\CMS\Extbase\Persistence\ObjectStorage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $connectionsRepository = $this->getMockBuilder(\GdprExtensionsCom\GdprExtensionsComWm\Domain\Repository\ConnectionsRepository::class)
            ->onlyMethods(['findAll'])
            ->disableOriginalConstructor()
            ->getMock();
        $connectionsRepository->expects(self::once())->method('findAll')->will(self::returnValue($allConnections));
        $this->subject->_set('connectionsRepository', $connectionsRepository);

        $view = $this->getMockBuilder(ViewInterface::class)->getMock();
        $view->expects(self::once())->method('assign')->with('connections', $allConnections);
        $this->subject->_set('view', $view);

        $this->subject->listAction();
    }

    /**
     * @test
     */
    public function editActionAssignsTheGivenConnectionsToView(): void
    {
        $connections = new \GdprExtensionsCom\GdprExtensionsComWm\Domain\Model\Connections();

        $view = $this->getMockBuilder(ViewInterface::class)->getMock();
        $this->subject->_set('view', $view);
        $view->expects(self::once())->method('assign')->with('connections', $connections);

        $this->subject->editAction($connections);
    }

    /**
     * @test
     */
    public function updateActionUpdatesTheGivenConnectionsInConnectionsRepository(): void
    {
        $connections = new \GdprExtensionsCom\GdprExtensionsComWm\Domain\Model\Connections();

        $connectionsRepository = $this->getMockBuilder(\GdprExtensionsCom\GdprExtensionsComWm\Domain\Repository\ConnectionsRepository::class)
            ->onlyMethods(['update'])
            ->disableOriginalConstructor()
            ->getMock();

        $connectionsRepository->expects(self::once())->method('update')->with($connections);
        $this->subject->_set('connectionsRepository', $connectionsRepository);

        $this->subject->updateAction($connections);
    }
}
