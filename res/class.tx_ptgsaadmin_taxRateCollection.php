<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2008 Rainer Kuhn (kuhn@punkt.de)
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
 * Tax rate collection class of the 'ptgsaadmin' extension
 *
 * $Id: class.tx_ptgsaadmin_taxRateCollection.php,v 1.2 2008/04/21 13:30:04 ry37 Exp $
 *
 * @author  Rainer Kuhn <kuhn@punkt.de>
 * @since   2008-04-03
 */ 
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 */



/**
 * Inclusion of extension specific resources
 */
require_once t3lib_extMgm::extPath('pt_gsaadmin').'res/class.tx_ptgsaadmin_taxRate.php';// GSA tax rate class
require_once t3lib_extMgm::extPath('pt_gsaadmin').'res/class.tx_ptgsaadmin_taxRateAccessor.php';  // GSA extended tax rate accessor class

/**
 * Inclusion of external resources
 */
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_debug.php'; // debugging class with trace() function
require_once t3lib_extMgm::extPath('pt_tools').'res/abstract/class.tx_pttools_objectCollection.php'; // abstract object collection class



/**
 * Tax rate collection class 
 * TODO: this class may be moved to pt_gsashop in later versions!
 *
 * @author      Rainer Kuhn <kuhn@punkt.de>
 * @since       2008-04-03
 * @package     TYPO3
 * @subpackage  tx_ptgsaadmin
 */
class tx_ptgsaadmin_taxRateCollection extends tx_pttools_objectCollection implements IteratorAggregate, Countable {
    
    
    /***************************************************************************
     *   CONSTRUCTOR
     **************************************************************************/

     
    /**
     * Class constructor: fills the tax rate collection object
     *
     * @param   boolean     (optional) flag whether a collection of valid all tax rates in the database should be created (default: false = create empty collection)
     * @return  void     
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2008-04-03
     */
    public function __construct($createAllTaxRatesCollection=false) {
            
        trace('***** Creating new '.__CLASS__.' object. *****');
        
        // create a collection of all valid tax rate records in the database if requested by param
        if ($createAllTaxRatesCollection == true) {
            $taxRatesArr = tx_ptgsaadmin_taxRateAccessor::getInstance()->selectTaxRateRecords();
            if (is_array($taxRatesArr)) {
                foreach ($taxRatesArr as $taxDataArr) {
                    trace($taxDataArr);
                    $newTaxRate = new tx_ptgsaadmin_taxRate($taxDataArr['NUMMER']);
                    $this->addItem($newTaxRate);
                }
            }
        }
        
        trace($this);
        
    }
    
    
    
    /***************************************************************************
     *   INHERITED METHODS
     **************************************************************************/
     
    /**
     * Adds one item to the collection
     *
     * @param   tx_ptgsaadmin_taxRate     tax rate object to add
     * @return  void
     * @see     tx_pttools_objectCollection::addItem()
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2008-04-03
     */ 
    public function addItem(tx_ptgsaadmin_taxRate $itemObj) {
        
        parent::addItem($itemObj);
        
    }
    
    
    
} // end class




/*******************************************************************************
 *   TYPO3 XCLASS INCLUSION (for class extension/overriding)
 ******************************************************************************/
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsaadmin/res/class.tx_ptgsaadmin_taxRateCollection.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsaadmin/res/class.tx_ptgsaadmin_taxRateCollection.php']);
}

?>