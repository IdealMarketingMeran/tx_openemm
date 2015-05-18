<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}
if (!isset($GLOBALS['TCA']['fe_users']['ctrl']['type'])) {
    if (file_exists($GLOBALS['TCA']['fe_users']['ctrl']['dynamicConfigFile'])) {
        require_once($GLOBALS['TCA']['fe_users']['ctrl']['dynamicConfigFile']);
    }
    // no type field defined, so we define it here. This will only happen the first time the extension is installed!!
    $GLOBALS['TCA']['fe_users']['ctrl']['type'] = 'tx_extbase_type';
    $tempColumns = array();
    $tempColumns[$GLOBALS['TCA']['fe_users']['ctrl']['type']] = array(
        'exclude' => 1,
        'label' => 'LLL:EXT:openemm/Resources/Private/Language/locallang_db.xlf:tx_openemm.tx_extbase_type',
        'config' => array(
            'type' => 'select',
            'items' => array(
                array('Default', '1')
            ),
            'size' => 1,
            'maxitems' => 1,
        )
    );
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $tempColumns, 1);
}



$tmp_openemm_columns = array(
    'crdate' => array(
        'exclude' => 1,
        'label' => 'crdate',
        'config' => array(
            'type' => 'input',
            'size' => 30,
            'max' => 255,
            'readOnly' => 1,
        )
    ),
    'emm_id' => array(
        'exclude' => 0,
        'label' => 'LLL:EXT:openemm/Resources/Private/Language/locallang_db.xlf:fe_users.emm_id',
        'config' => array(
            'type' => 'input',
            'size' => 30,
            'eval' => 'trim,required',
            'readOnly' => 1,
        ),
    ),
    'emm_last_synchronisation' => array(
        'exclude' => 1,
        'label' => 'LLL:EXT:openemm/Resources/Private/Language/locallang_db.xlf:fe_users.emm_last_synchronisation',
        'config' => array(
            'type' => 'input',
            'size' => 30,
            'max' => 255,
            'readOnly' => 1,
        )
    ),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $tmp_openemm_columns);

$GLOBALS['TCA']['fe_users']['types']['Tx_openemm_Feusers']['showitem'] = $TCA['fe_users']['types']['0']['showitem'];
$GLOBALS['TCA']['fe_users']['types']['Tx_openemm_Feusers']['showitem'] .= ',--div--;LLL:EXT:openemm/Resources/Private/Language/locallang_db.xlf:taps.openemm, emm_id, emm_last_synchronisation';

$GLOBALS['TCA']['fe_users']['columns'][$TCA['fe_users']['ctrl']['type']]['config']['items'][] = array('LLL:EXT:openemm/Resources/Private/Language/locallang_db.xlf:fe_users.tx_extbase_type.Tx_openemm_Feusers', 'Tx_openemm_Feusers');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', $GLOBALS['TCA']['fe_users']['ctrl']['type'], '', 'after:' . $TCA['fe_users']['ctrl']['label']);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', '--div--;LLL:EXT:openemm/Resources/Private/Language/locallang_db.xlf:taps.openemm, emm_id, emm_last_synchronisation');
