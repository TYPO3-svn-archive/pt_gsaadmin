<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007-2008 Rainer Kuhn <kuhn@punkt.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/** 
 * Abstract submodules parent class for the 'pt_gsaadmin' extension
 *
 * $Id: class.tx_ptgsaadmin_submodules.php,v 1.16 2008/10/16 13:03:42 ry37 Exp $
 *
 * @author  Rainer Kuhn <kuhn@punkt.de>
 * @since   2007-11-02 (general methods completely "outsourced" to tx_pttools_beSubmodule on 2008-02-06)
 */ 


/**
 * Inclusion of TYPO3 resources
 */
require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_exception.php'; // general exception class
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_debug.php'; // debugging class with trace() function
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_div.php'; 
require_once t3lib_extMgm::extPath('pt_tools').'res/abstract/class.tx_pttools_beSubmodule.php'; // abstract backend submodule parent class



/**
 * Abstract submodules parent class for the 'pt_gsaadmin' extension
 *
 * @author      Rainer Kuhn <kuhn@punkt.de>
 * @since       2007-11-02 (general methods completely "outsourced" to tx_pttools_beSubmodule on 2008-02-06)
 * @package     TYPO3
 * @subpackage  tx_ptgsaadmin
 */
abstract class tx_ptgsaadmin_submodules extends tx_pttools_beSubmodule {
    
    
    /***************************************************************************
     *   INHERITED METHODS
     **************************************************************************/
     
    /**
     * Initializes the module
     *
     * @param       void
     * @return      void
     * @throws      tx_pttools_exception    if no TS configuration could be found
     * @author      Rainer Kuhn <kuhn@punkt.de>
     * @since       2008-02-06
     */
    public function init() {
        
        try {            
            // $this->setDebugCookie();
            
            // set parent class properties
            $this->extKey = 'pt_gsaadmin';
            $this->extPrefix = 'tx_ptgsaadmin';  // extension prefix (for CSS classes, session keys etc.)
            $this->cssRelPath = '../res/css/submodules.css';  // path to the CSS file to use for this module (relative path from the module's index.php file)
            $extConfArray = tx_pttools_div::returnExtConfArray($this->extKey);
            $this->conf = tx_pttools_div::typoscriptRegistry('config.'.$this->extPrefix.'.', $extConfArray['tsConfigurationPid']);
            
            if (empty($extConfArray['tsConfigurationPid']) || empty($this->conf)) {
                throw new tx_pttools_exception('No TS configuration found. Please set your TS configuration PID in Ext.Mgr. of '.$this->extKey.' and include the static TS templates of pt_gsashop and '.$this->extKey.' there.');
            }
            
            // jQuery integration for BE
            $this->jsArray['jquery'] = '<script type="text/javascript" src="'.$GLOBALS['BACK_PATH'].'../typo3conf/ext/jquery/uncompressed_src/jquery.js"></script>';
            
            parent::init(); // calls tx_pttools_beSubmodule::init()
            
        } catch (tx_pttools_exception $excObj) {
            
            $excObj->handleException();
            die($excObj->__toString());
            
        }
        
    }
    
    
    
} // end class



/*******************************************************************************
 *   TYPO3 XCLASS INCLUSION (for class extension/overriding)
 ******************************************************************************/
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsaadmin/res/class.tx_ptgsaadmin_submodules.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsaadmin/res/class.tx_ptgsaadmin_submodules.php']);
}

?>