<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

$tempColumns = array(
	'tx_feuserfriends_view' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:feuser_friends/locallang_db.xml:tt_content.tx_feuserfriends_view',
		'config' => array(
			'type' => 'select',
			'items' => array(
				array('LLL:EXT:feuser_friends/locallang_db.xml:tt_content.tx_feuserfriends_view.I.0', '0', t3lib_extMgm::extRelPath('feuser_friends').'selicon_tt_content_tx_feuserfriends_view_0.gif'),
				array('LLL:EXT:feuser_friends/locallang_db.xml:tt_content.tx_feuserfriends_view.I.1', '1', t3lib_extMgm::extRelPath('feuser_friends').'selicon_tt_content_tx_feuserfriends_view_1.gif'),
				array('LLL:EXT:feuser_friends/locallang_db.xml:tt_content.tx_feuserfriends_view.I.2', '2', t3lib_extMgm::extRelPath('feuser_friends').'selicon_tt_content_tx_feuserfriends_view_2.gif'),
				array('LLL:EXT:feuser_friends/locallang_db.xml:tt_content.tx_feuserfriends_view.I.3', '3', t3lib_extMgm::extRelPath('feuser_friends').'selicon_tt_content_tx_feuserfriends_view_3.gif'),
				array('LLL:EXT:feuser_friends/locallang_db.xml:tt_content.tx_feuserfriends_view.I.4', '4', t3lib_extMgm::extRelPath('feuser_friends').'selicon_tt_content_tx_feuserfriends_view_4.gif'),
			),
			'size' => 1,
			'maxitems' => 1,
		)
	),
);
t3lib_div::loadTCA('tt_content');
t3lib_extMgm::addTCAcolumns('tt_content', $tempColumns,1);
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1'] = 'layout,select_key';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1'] = 'tx_feuserfriends_view;;;;1-1-1';



t3lib_extMgm::addPlugin(array(
	'LLL:EXT:feuser_friends/locallang_db.xml:tt_content.list_type_pi1',
	$_EXTKEY . '_pi1',
	t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
),'list_type');


if (TYPO3_MODE == 'BE') {
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_feuserfriends_pi1_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_feuserfriends_pi1_wizicon.php';
}



t3lib_extMgm::addStaticFile($_EXTKEY,'static/', 'Frontend-user friends');



t3lib_extMgm::allowTableOnStandardPages('tx_feuserfriends_friends');
$TCA['tx_feuserfriends_friends'] = array(
	'ctrl' => array(
		'title'     => 'LLL:EXT:feuser_friends/locallang_db.xml:tx_feuserfriends_friends',
		'label'     => 'uid',
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY crdate',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
		),
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_feuserfriends_friends.gif',
	),
	'interface' => array(
		'showRecordFieldList' => 'hidden,user_from,user_to,invitation,accept'
	),
	'columns' => array(
		'hidden' => array(
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array(
				'type'    => 'check',
				'default' => '0'
			)
		),
		'user_from' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:feuser_friends/locallang_db.xml:tx_feuserfriends_friends.user_from',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'fe_users',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'user_to' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:feuser_friends/locallang_db.xml:tx_feuserfriends_friends.user_to',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'fe_users',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'invitation' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:feuser_friends/locallang_db.xml:tx_feuserfriends_friends.invitation',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),
		'accept' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:feuser_friends/locallang_db.xml:tx_feuserfriends_friends.accept',
			'config' => array(
				'type' => 'check',
			)
		),
	),
	'types' => array(
		'0' => array('showitem' => 'hidden;;1;;1-1-1, user_from, user_to, invitation, accept')
	),
	'palettes' => array(
		'1' => array('showitem' => '')
	)
);



t3lib_extMgm::allowTableOnStandardPages('tx_feuserfriends_message');
$TCA['tx_feuserfriends_message'] = array(
	'ctrl' => array(
		'title'     => 'LLL:EXT:feuser_friends/locallang_db.xml:tx_feuserfriends_message',
		'label'     => 'uid',
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY crdate',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
		),
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_feuserfriends_message.gif',
	),
	'interface' => array(
		'showRecordFieldList' => 'hidden,user_from,user_to,message'
	),
	'columns' => array(
		'hidden' => array(
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array(
				'type'    => 'check',
				'default' => '0'
			)
		),
		'user_from' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:feuser_friends/locallang_db.xml:tx_feuserfriends_message.user_from',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'fe_users',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'user_to' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:feuser_friends/locallang_db.xml:tx_feuserfriends_message.user_to',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'fe_users',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'message' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:feuser_friends/locallang_db.xml:tx_feuserfriends_message.message',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),
	),
	'types' => array(
		'0' => array('showitem' => 'hidden;;1;;1-1-1, user_from, user_to, message')
	),
	'palettes' => array(
		'1' => array('showitem' => '')
	)
);
?>