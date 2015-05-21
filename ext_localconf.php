<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'Ideal.' . $_EXTKEY,
	'Pi1',
	array(
		'Subscriber' => 'new, create, confirm, update, unsubscribe',
        'Mailinglist' => 'list, new, create, edit, update, delete',
	),
	// non-cacheable actions
	array(
		'Subscriber' => 'new, create, confirm, update, unsubscribe',
        'Mailinglist' => 'list, new, create, edit, update, delete',
	)
);