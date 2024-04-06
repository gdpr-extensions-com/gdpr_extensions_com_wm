<?php

namespace GdprExtensionsCom\GdprExtensionsComWm\Utility;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

class SyncApiconnects
{

    public function run(Helper $helper, ConnectionPool $connectionPool, Logger $logManager)
    {

            try {

                    $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
                    $typoscript = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
                    $apiModule = $typoscript['tt_content.']['gdpr_extensions_com_wm.']['module.'];

                    foreach ($apiModule as $k => $v) {
                            $instance = GeneralUtility::makeInstance($v);
                            $instance->run();
                    }
                
            } catch (\Exception $exception) {
                print_r($exception->getMessage());
                $logManager->error(
                    $exception->getMessage(),
                    [
                        'code' => $exception->getCode(),
                        'file' => $exception->getFile(),
                        'line' => $exception->getLine(),
                        'trace' => $exception->getTrace(),
                    ]
                );
            }
        }
}
