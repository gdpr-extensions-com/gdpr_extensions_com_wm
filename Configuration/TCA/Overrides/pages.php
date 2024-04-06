<?php
defined('TYPO3_MODE') || die();

$tmp_go_locations_columns = [
     'multi_locations' => [
        'exclude' => true,
        'label' => 'Location Manager',
        'displayCond' => 'FIELD:is_siteroot:REQ:TRUE',
        'config' => [
            'type' => 'inline',
            'foreign_table' => 'multilocations',
            'foreign_field' => 'pages',
            'maxitems' => 10,
            'foreign_sortby' => 'sorting',
            'appearance' => [
                'useSortable' => 1,
                'collapseAll' => 1,
                'levelLinksPosition' => 'top',
                'showSynchronizationLink' => 1,
                'showPossibleLocalizationRecords' => 1,
                'showAllLocalizationLink' => 1
                ],
            ],
        ],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages',$tmp_go_locations_columns);

/**
 * fileds to paegs table
 */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
	'pages',
	'
        --div--;LLL:EXT:gdpr_extensions_com_wm/Resources/Private/Language/locallang_db.xlf:pages.div.location_manager, multi_locations
    ',
	'',
	'after:--palette--;;access'
);
