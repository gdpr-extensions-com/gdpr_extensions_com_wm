<?php
namespace GdprExtensionsCom\GdprExtensionsComWm\Hooks;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use GuzzleHttp\Pool;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;




class DataHandlerHook
{

    public function processCmdmap_postProcess($command, $table, $id, $value, &$dataHandler, $pasteUpdate, $pasteDatamap)
    {
    }

    /**
     * @param string $table
     * @param int $uid
     * @param array $recordToDelete
     * @param bool $recordWasDeleted
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler
     */
    public function processCmdmap_deleteAction($table, $uid, $recordToDelete, $recordWasDeleted, &$dataHandler)
    {

        if($table === 'multilocations'){

            $oldRecord = BackendUtility::getRecord('multilocations', $uid, 'dashboard_api_key, pages');
            $this->sendStatusUpdate($oldRecord['dashboard_api_key'],$oldRecord['pages']);
            $this->deleteWebsiteData($oldRecord['dashboard_api_key'],$oldRecord['pages']);
        }
    }



    /**
     * @param array $fieldArray
     * @param string $table
     * @param mixed $id
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler
     */
    public function processDatamap_preProcessFieldArray(&$fieldArray, $table, $id, &$dataHandler)
    {


        if($table === 'pages'){

        }
        if($table === 'multilocations'){

            $oldRecord = BackendUtility::getRecord('multilocations', $id, 'dashboard_api_key, pages');

            if($fieldArray['dashboard_api_key'] != $oldRecord['dashboard_api_key']){

                $this->sendStatusUpdate($oldRecord['dashboard_api_key'],$oldRecord['pages']);
                $this->deleteWebsiteData($oldRecord['dashboard_api_key'],$oldRecord['pages']);

            }


        }


    }


    public function sendStatusUpdate($oldRecord,$rootPid){

        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $client = GeneralUtility::makeInstance(Client::class);


        $multilocationQB = $connectionPool->getQueryBuilderForTable('multilocations');

        $sysTempQB = $connectionPool->getQueryBuilderForTable('sys_template');

        $BaseUris = [];


        $multilocationQBResult = $multilocationQB
            ->select('*')
            ->from('multilocations')
            ->executeQuery()
            ->fetchAllAssociative();

        foreach ($multilocationQBResult as $location) {
            $apiKey = $location['dashboard_api_key'] ?? null;

            $SiteConfiguration = $sysTempQB->select('constants')
                ->from('sys_template')
                ->where(
                    $sysTempQB->expr()->eq('pid', $sysTempQB->createNamedParameter($location['pages'])),
                )
                ->setMaxResults(1)
                ->executeQuery()
                ->fetchAssociative();
            $sysTempQB->resetQueryParts();

            $constantsArray = $this->extractSecretKey($SiteConfiguration['constants']);
            $BaseURL = $constantsArray['plugin.tx_gdprextensionscomwm_websitemanager.settings.dashboardBaseUrl'];

            if ($apiKey) {

                $BaseUris[$location['pages']] = $BaseURL;
            }
        }

        $requests = function ($oldRecord) use ($BaseUris,$rootPid) {




            yield new Request(
                'POST',
                (is_null($BaseUris[$rootPid]) ? 'https://dashboard.gdpr-extensions.com/': $BaseUris[$rootPid]) .'review/api/' . $oldRecord . '/update-status.json',
                [
                    'Content-Type' => 'application/json'
                ],
                json_encode([
                    'elements' =>  [],
                    'extensions' => [],
                ]));


        };


        $pool = new Pool($client, $requests($oldRecord), [
            'concurrency' => 5,
            'fulfilled' => function ($response, $index) {},
            'rejected' => function ($reason, $index) {},
        ]);

        $promise = $pool->promise();
        $promise->wait();
    }

    protected function extractSecretKey($constantsString)
    {
        $configLines = explode("\n", $constantsString);
        $configArray = [];

        foreach ($configLines as $line) {
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $configArray[trim($key)] = trim($value);
            }
        }
        return $configArray;
    }

    protected function deleteWebsiteData($oldRecord, $rootPid){
        $schemaManager = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('multilocations')->createSchemaManager();
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $tables = ['tx_gobannerclient_domain_model_localbanner', 'tx_goclientreviews_domain_model_reviews'];

        foreach ($tables as $table) {
            if ($schemaManager->tablesExist([$table])) {
                // If tables exist, delete records
                $queryBuilder = $connectionPool->getQueryBuilderForTable($table);
                $queryBuilder->delete($table)
                    ->where(
                        $queryBuilder->expr()->eq('dashboard_api_key', $queryBuilder->createNamedParameter($oldRecord, \PDO::PARAM_STR)),
                        $queryBuilder->expr()->eq('root_pid', $queryBuilder->createNamedParameter($rootPid, \PDO::PARAM_INT))
                    )
                    ->executeStatement();
            }
        }
    }
}
