<?php

namespace GdprExtensionsCom\GdprExtensionsComWm\Commands;

use GdprExtensionsCom\GdprExtensionsComWm\Utility\UpdateOwnStatus;
use GuzzleHttp\Client;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class UpdateOwnStatusTask extends \TYPO3\CMS\Scheduler\Task\AbstractTask {

    public function __construct()
    {
        parent::__construct();
    }

    public function execute()
    {
        $businessLogic = GeneralUtility::makeInstance(UpdateOwnStatus::class);
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
        $client = GeneralUtility::makeInstance(Client::class);
        $businessLogic->run($connectionPool, $siteFinder, $client);
        return true;
    }
}
