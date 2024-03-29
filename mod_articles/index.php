<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Rainer Kuhn <kuhn@punkt.de>
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
 * index.php for the backend sub module 'Articles' of the 'pt_gsaadmin' extension
 *
 * $Id: index.php,v 1.7 2008/05/02 12:38:12 ry44 Exp $
 *
 * @author  Rainer Kuhn <kuhn@punkt.de>
 * @since   2007-08-24
 */ 



/**
 * Default module initialization (according to TYPO3 API in 'EXAMPLE PROTOTYPE' in t3lib_SCbase)
 */
unset($MCONF);
require_once('conf.php');
require_once($BACK_PATH.'init.php');
require_once($BACK_PATH.'template.php');
$LANG->includeLLFile('EXT:pt_gsaadmin/mod_articles/locallang.xml');
require_once(PATH_t3lib.'class.t3lib_scbase.php'); // parent class for 'ScriptClasses' in backend modules
require_once(PATH_t3lib.'/error/class.t3lib_error_exception.php'); // Typo3 exection class not automatically included
$BE_USER->modAccess($MCONF, 1); // this checks permissions and exits if the users has no permission for entry



/**
 * Module class inclusion
 */
require_once t3lib_extMgm::extPath('pt_gsaadmin').'mod_articles/class.tx_ptgsaadmin_module2.php';



/**
 * Default module finalization (according to TYPO3 API in 'EXAMPLE PROTOTYPE' in t3lib_SCbase)
 */
// make instance of the backend module script class and initialize it
$SOBE = t3lib_div::makeInstance('tx_ptgsaadmin_module2'); /* @var $SOBE tx_ptgsaadmin_module2 */
$SOBE->init();
// check for include files (after init() the internal array $SOBE->include_once may hold filenames to include)
foreach($SOBE->include_once as $INC_FILE) {
    include_once($INC_FILE);
}

// call main() method (this should spark the creation of the module output) and output the accumulated content
$SOBE->main();
$SOBE->printContent();

?>