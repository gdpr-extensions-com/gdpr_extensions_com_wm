<?php

namespace GdprExtensionsCom\GdprExtensionsComWm\Commands;

use GdprExtensionsCom\GdprExtensionsComWm\Utility\SyncApiconnects;
use GdprExtensionsCom\GdprExtensionsComWm\Utility\Helper;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Scheduler\Task;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

class SyncApiconnectsTask extends \TYPO3\CMS\Scheduler\Task\AbstractTask {

    public function __construct()
    {
        parent::__construct();
    }

    public function execute()
    {
        $businessLogic = GeneralUtility::makeInstance(SyncApiconnects::class);
        $rFactory = GeneralUtility::makeInstance(RequestFactory::class);
        $helper = new Helper($rFactory);
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $logger = GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);

        $businessLogic->run($helper,$connectionPool,$logger);
        return true;
    }
}
