<?php
/**
 * $Id: conf.php,v 1.4 2008/05/02 12:38:12 ry44 Exp $
 */
 
    // DO NOT REMOVE OR CHANGE THESE 3 LINES:
define('TYPO3_MOD_PATH', '../typo3conf/ext/pt_gsaadmin/mod_main/');
$BACK_PATH = '../../../../typo3/';
$MCONF['name'] = 'txptgsaadminM1';

$MCONF['access'] = 'user,group';
// $MCONF['script'] = 'index.php'; // not used for main modules

$MLANG['default']['tabs_images']['tab'] = 'moduleicon.gif';
$MLANG['default']['ll_ref'] = 'LLL:EXT:pt_gsaadmin/mod_main/locallang_mod.xml';
?>