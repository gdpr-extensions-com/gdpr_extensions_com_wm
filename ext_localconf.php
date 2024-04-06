<?php

use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\Scheduler;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

defined('TYPO3') || exit;

$versionInformation = GeneralUtility::makeInstance(Typo3Version::class);
// Only include page.tsconfig if TYPO3 version is below 12 so that it is not imported twice.
if ($versionInformation->getMajorVersion() < 12) {
    ExtensionManagementUtility::addPageTSConfig('
      @import "EXT:gdpr_extensions_com_wm/Configuration/page.tsconfig"
   ');
}

(static function() {

    // wizards
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        'mod {
            wizards.newContentElement.wizardItems.plugins {
                elements {
                    Websitemanager {
                        iconIdentifier = gdpr_extensions_com_wm-plugin-websitemanager
                        title = LLL:EXT:gdpr_extensions_com_wm/Resources/Private/Language/locallang_db.xlf:tx_gdpr_extensions_com_wm.name
                        description = LLL:EXT:gdpr_extensions_com_wm/Resources/Private/Language/locallang_db.xlf:tx_gdpr_extensions_com_wm.description
                        tt_content_defValues {
                            CType = list
                            list_type = gdprextensionscomwm_websitemanager
                        }
                    }
                }
                show = *
            }
       }'
    );

    // Register Scheduler Task
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\GdprExtensionsCom\GdprExtensionsComWm\Commands\SyncApiconnectsTask::class] = [
        'extension' => 'gdpr_extensions_com_wm',
        'title' => 'LLL:EXT:gdpr_extensions_com_wm/Resources/Private/Language/locallang.xlf:sync.apiconnects.title',
        'description' => 'LLL:EXT:gdpr_extensions_com_wm/Resources/Private/Language/locallang.xlf:sync.apiconnects.description',
        'additionalFields' => \GdprExtensionsCom\GdprExtensionsComWm\Commands\SyncApiconnectsTask::class,
    ];
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\GdprExtensionsCom\GdprExtensionsComWm\Commands\UpdateOwnStatusTask::class] = [
        'extension' => 'gdpr_extensions_com_wm',
        'title' => 'LLL:EXT:gdpr_extensions_com_wm/Resources/Private/Language/locallang.xlf:sync.update_own_status.title',
        'description' => 'LLL:EXT:gdpr_extensions_com_wm/Resources/Private/Language/locallang.xlf:sync.update_own_status.description',
        'additionalFields' => \GdprExtensionsCom\GdprExtensionsComWm\Commands\UpdateOwnStatusTask::class,
    ];

    // Register Hook here
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = \GdprExtensionsCom\GdprExtensionsComWm\Hooks\DataHandlerHook::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'][] = \GdprExtensionsCom\GdprExtensionsComWm\Hooks\DataHandlerHook::class;


})();

// Include Typoscript Setup
call_user_func(function () {
    $extensionKey = 'gdpr_extensions_com_wm';

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
        $extensionKey,
        'setup',
        "@import 'EXT:gdpr_extensions_com_wm/Configuration/TypoScript/setup.typoscript'"
    );
});
