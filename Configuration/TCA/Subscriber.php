<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$GLOBALS['TCA']['tx_openemm_domain_model_subscriber'] = array(
    'ctrl' => $GLOBALS['TCA']['tx_openemm_domain_model_subscriber']['ctrl'],
    'interface' => array(
        'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, customer_id, confirmed, name, email, mailing_lists, unsubscribt, parameters',
    ),
    'types' => array(
        '1' => array('showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, customer_id, confirmed, name, email, mailing_lists, unsubscribt, parameters, '),
    ),
    'palettes' => array(
        '1' => array('showitem' => ''),
    ),
    'columns' => array(
        'sys_language_uid' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.language',
            'config' => array(
                'type' => 'select',
                'foreign_table' => 'sys_language',
                'foreign_table_where' => 'ORDER BY sys_language.title',
                'items' => array(
                    array('LLL:EXT:lang/locallang_general.xlf:LGL.allLanguages', -1),
                    array('LLL:EXT:lang/locallang_general.xlf:LGL.default_value', 0)
                ),
            ),
        ),
        'l10n_parent' => array(
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.l18n_parent',
            'config' => array(
                'type' => 'select',
                'items' => array(
                    array('', 0),
                ),
                'foreign_table' => 'tx_openemm_domain_model_subscriber',
                'foreign_table_where' => 'AND tx_openemm_domain_model_subscriber.pid=###CURRENT_PID### AND tx_openemm_domain_model_subscriber.sys_language_uid IN (-1,0)',
            ),
        ),
        'l10n_diffsource' => array(
            'config' => array(
                'type' => 'passthrough',
            ),
        ),
        't3ver_label' => array(
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.versionLabel',
            'config' => array(
                'type' => 'input',
                'size' => 30,
                'max' => 255,
            )
        ),
        'customer_id' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:openemm/Resources/Private/Language/locallang_db.xlf:tx_openemm_domain_model_subscriber.customer_id',
            'config' => array(
                'type' => 'input',
                'size' => 4,
                'eval' => 'int'
            )
        ),
        'confirmed' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:openemm/Resources/Private/Language/locallang_db.xlf:tx_openemm_domain_model_subscriber.confirmed',
            'config' => array(
                'type' => 'check',
                'default' => 0
            )
        ),
        'name' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:openemm/Resources/Private/Language/locallang_db.xlf:tx_openemm_domain_model_subscriber.name',
            'config' => array(
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ),
        ),
        'email' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:openemm/Resources/Private/Language/locallang_db.xlf:tx_openemm_domain_model_subscriber.email',
            'config' => array(
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ),
        ),
        'mailing_lists' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:openemm/Resources/Private/Language/locallang_db.xlf:tx_openemm_domain_model_subscriber.mailing_lists',
            'config' => array(
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ),
        ),
        'unsubscribt' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:openemm/Resources/Private/Language/locallang_db.xlf:tx_openemm_domain_model_subscriber.unsubscribt',
            'config' => array(
                'type' => 'check',
                'default' => 0
            )
        ),
        'parameters' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:openemm/Resources/Private/Language/locallang_db.xlf:tx_openemm_domain_model_subscriber.parameters',
            'config' => array(
                'type' => 'text',
                'cols' => 40,
                'rows' => 15,
                'eval' => 'trim'
            )
        ),
    ),
);
