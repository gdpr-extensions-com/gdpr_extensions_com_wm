<?php

namespace GdprExtensionsCom\GdprExtensionsComWm\Utility;

use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use Exception;

class UpdateOwnStatus
{
    public function run(ConnectionPool $connectionPool, SiteFinder $siteFinder, Client $client)
    {
        $multilocationQB = $connectionPool->getQueryBuilderForTable('multilocations');

        $sysTempQB = $connectionPool->getQueryBuilderForTable('sys_template');


        $apiKeys = [];
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
                if (!isset($apiKeys[$location['pages']])) {
                    $apiKeys[$location['pages']] = [];
                }
                $apiKeys[$location['pages']]  = array_merge($apiKeys[$location['pages']], [$apiKey]);
                $BaseUris[$location['pages']] = $BaseURL;
            }
        }

        if (empty($apiKeys)) {
            return;
        }
        $extensions = ExtensionManagementUtility::getLoadedExtensionListArray();
        $goExtensions = array_values($extensions);
        $conditions = [];
        $queryBuilder = $connectionPool->getQueryBuilderForTable('tt_content');

        foreach ($goExtensions as $extension) {
            $extensionKeyForListType = str_replace('_', '', $extension);
            $conditions[] = $queryBuilder->expr()->or(
                $queryBuilder->expr()->like('list_type', $queryBuilder->createNamedParameter($extensionKeyForListType . '%')),
                $queryBuilder->expr()->like('CType', $queryBuilder->createNamedParameter($extensionKeyForListType . '%')),

            );
        }

        $compositeCondition = call_user_func_array([$queryBuilder->expr(), 'orX'], $conditions);
        $elements =  array_map(function ($item) {
            return $item;
        },$queryBuilder
            ->select('list_type', 'pid', 'Ctype')
            ->from('tt_content')
            ->where($compositeCondition)
            ->execute()
            ->fetchAllAssociative());

        $filteredRootPages = [];

        foreach ($elements as $element){
        $tempElement = [];

            try{
                $rootLines = GeneralUtility::makeInstance(RootlineUtility::class, $element['pid'])->get();
             
            }catch(Exception $e){
                continue;
            }
            if (!empty($rootLines)) {
                foreach ($rootLines as $rootLine) {
                    if (!empty($rootLine['is_siteroot']) && $rootLine['is_siteroot']) {
                        if(!empty($element['list_type'])){
                            array_push($tempElement,$element['list_type']);
                        }else{
                            array_push($tempElement,$element['Ctype']);
                        }
            
                               // if $filteredRootPages[$rootLine['uid']] does not exist, create an empty array
                        if (!isset($filteredRootPages[$rootLine['uid']])) {
                            $filteredRootPages[$rootLine['uid']] = [];
                        }
                        // merge existing array with new element
                        $filteredRootPages[$rootLine['uid']]  = array_merge($filteredRootPages[$rootLine['uid']], $tempElement);
                    }
                }
            }
        }


        $requests = function ($apiKeys) use ($BaseUris, $filteredRootPages, $goExtensions) {
            foreach ($apiKeys as $siteIdentifier => $apiKey) {
                foreach ($apiKey as $multiLocApiKey){

                    $elements = $filteredRootPages[$siteIdentifier];
               
                    yield new Request(
                        'POST',
                        (is_null($BaseUris[$siteIdentifier]) ? 'https://dashboard.gdpr-extensions.com/': $BaseUris[$siteIdentifier]) .'review/api/' . $multiLocApiKey . '/update-status.json',
                        [
                            'Content-Type' => 'application/json'
                        ],
                        json_encode([
                            'elements' => $elements ?? [],
                            'extensions' => $goExtensions ?? []
                        ]));

                }


            }
        };


        $pool = new Pool($client, $requests($apiKeys), [
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
}
