<?php
/**
 * $Id: ext_tables.php,v 1.13 2008/06/25 11:07:40 ry44 Exp $
 */
 
if (!defined ('TYPO3_MODE')) {
    die('Access denied.');
}

if (TYPO3_MODE == 'BE') {

    require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_div.php'; // general static library class
    
    // add main module after 'File' module
    if (!isset($TBE_MODULES['txptgsaadminM1']) && is_array($TBE_MODULES)) {
        $tempTbeModules = array();
        foreach ($TBE_MODULES as $key=>$val) {
            $tempTbeModules[$key] = $val;
            if ($key == 'file') {
                $tempTbeModules['txptgsaadminM1'] = $val;
            }
        }
        $TBE_MODULES = $tempTbeModules;
    }
    
    // add main module
    t3lib_extMgm::addModule('txptgsaadminM1', '', '', t3lib_extMgm::extPath($_EXTKEY).'mod_main/');
    // add articles module
    t3lib_extMgm::addModule('txptgsaadminM1', 'txptgsaadminM2', 'top', t3lib_extMgm::extPath($_EXTKEY).'mod_articles/');
    // add dispatch cost module
    t3lib_extMgm::addModule('txptgsaadminM1', 'txptgsaadminM3', '', t3lib_extMgm::extPath($_EXTKEY).'mod_dispatch/');
    
    try {
        // add tax rates module *only* if enabled in Ext.Mgr.(!)
        $extConfArr = tx_pttools_div::returnExtConfArray('pt_gsaadmin');
        if ($extConfArr['enableTaxRatesModule'] == 1) {
            t3lib_extMgm::addModule('txptgsaadminM1', 'txptgsaadminM4', '', t3lib_extMgm::extPath($_EXTKEY).'mod_taxrates/');
        }
    } catch (tx_pttools_exception $exception) {
        // nothing to do here
    }
    
}

t3lib_extMgm::addStaticFile($_EXTKEY,'static/','GSA Shop: Admin Interface');

?>