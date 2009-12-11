<?php
/** 
 * @author  Fabrizio Branca <branca@punkt.de>
 * @since   2007-12
 *
 * $Id: tca_virtual_tables.php,v 1.3 2008/07/18 09:21:14 ry44 Exp $
 */ 
$GLOBALS['TCA']['tx_ptgsaadmin_virtualarticle'] = array(
	'ctrl' => array(
		'title' => 'LLL:EXT:pt_gsashop/locallang_db.xml:tx_ptgsashop_article_images',
		'label' => 'title',
		'tstamp' => 'tstamp',
		'delete' => 'deleted',
		'default_sortby' => 'ORDER BY uid',
		'enablecolumns' => array(
			'disabled' => 'hidden',
		),
		// 'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'res/virtual_tca.php'
		/**
		 * No dynamic config file needed, because additional infos are already merged in this array (see below).
		 * This file will only be included be the backend module. 
		 * TYPO3 does not know anything about this table (and won't miss it) 		
		 */
	),
	'interface' => array(
        'showRecordFieldList' => 'image,deftext,alttext'
    ),
    'columns' => array(
        'image' => array(
            'label' => 'LLL:EXT:pt_gsashop/locallang_db.xml:tx_ptgsashop_article_images',
            'config' => array(
                'type' => 'group',
                'internal_type' => 'file',
                'allowed' => $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'],
                'max_size' => 10000,
                'uploadfolder' => 'uploads/pics',
                'show_thumbs' => 1,
                'size' => 5,
                'minitems' => 0,
                'maxitems' => 10,
            )
        ),
		'deftext' => array(
			'label' => 'Deftext',
			'l10n_mode' => $l10n_mode,
			'config' => array(
				'type' => 'text',
				'cols' => '48',
				'rows' => '5',
			),
			'defaultExtras' => 'richtext[*]'
		),
		'alttext' => array(
			'label' => 'Alttext',
			'l10n_mode' => $l10n_mode,
			'config' => array(
				'type' => 'text',
				'cols' => '48',
				'rows' => '5',
			),
			'defaultExtras' => 'richtext[*]'
		),
    ),
    
    'types' => array(
        '0' => array('showitem' => 'image,deftext;;;richtext[*]:rte_transform[mode=ts_css],alttext;;;richtext[*]:rte_transform[mode=ts_css]'),
    ),
);

?>