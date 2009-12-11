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
 * Database _modifying_ accessor class for tax rates (part of the 'pt_gsaadmin' extension)  - this is a (temporary?) addition to the _readonly_ accessor class tx_ptgsashop_taxAccessor
 *
 * $Id: class.tx_ptgsaadmin_taxRateAccessor.php,v 1.2 2008/11/18 16:46:35 ry37 Exp $
 *
 * @author  Rainer Kuhn <kuhn@punkt.de>
 * @since   2008-04-07
 */ 


/**
 * Inclusion of TYPO3 resources
 */
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_debug.php'; // debugging class with trace() function
require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_exception.php'; // general exception class
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_div.php'; // general helper library class
require_once t3lib_extMgm::extPath('pt_tools').'res/abstract/class.tx_pttools_iSingleton.php'; // interface for Singleton design pattern
require_once t3lib_extMgm::extPath('pt_gsashop').'res/class.tx_ptgsashop_taxAccessor.php';  // GSA shop database accessor class for tax data



/**
 * Database  _modifying_ accessor class for GSA tax rates, based on GSA database structure - this is a (temporary) addition to the _readonly_ accessor tx_ptgsashop_taxAccessor
 *
 * @author      Rainer Kuhn <kuhn@punkt.de>
 * @since       2008-04-07
 * @package     TYPO3
 * @subpackage  tx_ptgsashop
 */
class tx_ptgsaadmin_taxRateAccessor extends tx_ptgsashop_taxAccessor implements tx_pttools_iSingleton {
    
    /**
     * @var tx_ptgsaadmin_taxRateAccessor       Singleton unique instance
     */
    protected static $uniqueInstance = NULL;
    
    
    
    /***************************************************************************
     *   CONSTRUCTOR & OBJECT HANDLING METHODS
     **************************************************************************/
    
    /**
     * Returns a unique instance (Singleton) of the object. Use this method instead of the private/protected class constructor.
     *
     * @param   void
     * @return  tx_ptgsaadmin_taxRateAccessor      unique instance of the object (Singleton) 
     * @global     
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2008-02-08
     */
    public static function getInstance() {
        
        if (self::$uniqueInstance === NULL) {
            $className = __CLASS__;
            self::$uniqueInstance = new $className;
        }
        return self::$uniqueInstance;
        
    }
    
    
    
    /***************************************************************************
     *   PUBLIC HANDLER METHODS (depending on GSA DB requirements etc.)    // TODO: these could be moved to tx_ptgsashop_taxAccessor?
     **************************************************************************/
     
    /**
     * Inserts a given tax rate as a new tax rate to the GSA database
     *
     * @param   tx_ptgsaadmin_taxRate       object of type tx_ptgsaadmin_taxRate, tax rate to save
     * @return  int							uid of the inserted tax rate record
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2008-04-07
     */
    public function insertTaxRate(tx_ptgsaadmin_taxRate $taxRateObj) {
        
        // modify GSA-DB table BHSTEUER
        $taxRateUid = $this->insertTaxRateData($taxRateObj);
        
        // modify GSA-DB table STEUER (if existent)
        if ($this->oldTaxTableExists() == true) {
            $taxCodeQueryResult = $this->selectTaxDataOld($taxRateObj->get_taxCode()); // check for a record with given tax code in table STEUER
            // insert description record if none is found for this tax code
            if (empty($taxCodeQueryResult)) {
                $this->insertTaxRateDescription($taxRateObj->get_taxCode(), $taxRateObj->get_taxDescription());
            // update description record if one is found for this tax code and if a description has been set
            } elseif (strlen($taxRateObj->get_taxDescription()) > 0) {
                $this->updateTaxRateDescription($taxRateObj->get_taxCode(), $taxRateObj->get_taxDescription());
            }
        }
        
        return $taxRateUid;
        
    }
     
    /**
     * Updates a tax rate in the GSA database
     *
     * @param   tx_ptgsaadmin_taxRate    object of type tx_ptgsaadmin_taxRate, tax rate to save
     * @return  int     uid of the updated tax rate record
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2008-04-07
     */
    public function updateTaxRate(tx_ptgsaadmin_taxRate $taxRateObj) {
        
        // modify GSA-DB table BHSTEUER
        $this->updateTaxRateData($taxRateObj);
        
        // modify GSA-DB table STEUER (if existent)
        if ($this->oldTaxTableExists() == true) {
            $taxCodeQueryResult = $this->selectTaxDataOld($taxRateObj->get_taxCode()); // check for a record with given tax code in table STEUER
            // insert description record if none is found for this tax code
            if (empty($taxCodeQueryResult)) {
                $this->insertTaxRateDescription($taxRateObj->get_taxCode(), $taxRateObj->get_taxDescription());
            // update description record if one is found for this tax code
            } else {
                $this->updateTaxRateDescription($taxRateObj->get_taxCode(), $taxRateObj->get_taxDescription());
            }
        }           
        
        return $taxRateObj->get_recordUid();
        
    }
    
    /**
     * Deletes a tax rate from the GSA database
     *
     * @param   tx_ptgsaadmin_taxRate    object of type tx_ptgsaadmin_taxRate, tax rate to delete
     * @return  void
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2008-04-07
     */
    public function deleteTaxRate($taxRateObj) {
          
        // modify GSA-DB table BHSTEUER
        $this->deleteTaxRateData($taxRateObj->get_recordUid());
        
        // modify GSA-DB table STEUER (if existent): delete tax code description from table STEUER *only* if there are no more records with this tax code in table BHSTEUER
        if ($this->oldTaxTableExists() == true) {
            $recordsWithGivenTaxCodeArr = $this->selectTaxRateRecords($taxRateObj->get_taxCode()); // check for remaining records with given tax code in table BHSTEUER
            if (empty($recordsWithGivenTaxCodeArr)) {
                $this->deleteTaxRateDescription($taxRateObj->get_taxCode());
            }
        }           
        
    }
    
    
    
    /***************************************************************************
     *   GSA DB RELATED METHODS                      // TODO: these may be moved to tx_ptgsashop_taxAccessor some time
     **************************************************************************/
    
    /**
     * Inserts a new tax rate and it's the basic data into the GSA DB table 'BHSTEUER' and returns the inserted record's UID
     * 
     * @param   tx_ptgsaadmin_taxRate    object of type tx_ptgsaadmin_taxRate
     * @return  integer     UID of the inserted record
     * @throws  tx_pttools_exception   if params are not valid
     * @throws  tx_pttools_exception   if the query fails/returns false
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2008-04-07
     */
    protected function insertTaxRateData(tx_ptgsaadmin_taxRate $taxRateObj) {
        
        $tmpTaxRate = $taxRateObj->get_taxRate();
        if (!strlen($taxRateObj->get_taxCode()) > 0 || empty($tmpTaxRate)) {
            throw new tx_pttools_exception('Parameter error', 3, 'Tax rate object passed to '.__METHOD__.' is not valid.');
        }
        
        $table = $this->getTableName('BHSTEUER');
        $insertFieldsArr = array();  
        
        // query preparation: fields to set  
        $insertFieldsArr['NUMMER']              = $this->getNextId($table);
        $insertFieldsArr['STEUERSATZCODE']      = $taxRateObj->get_taxCode();
        $insertFieldsArr['STEUERSATZPROZ']      = $taxRateObj->get_taxRate();
        $insertFieldsArr['GUELTIGABTTMMJJJJ']   = $taxRateObj->get_startDate();
        
        // unset NULL values - this is crucial since TYPO3's exec_INSERTquery() will quote all fields including NULL otherwise!!
        foreach ($insertFieldsArr as $key=>$value) {
            if (is_null($value)) {
                unset($insertFieldsArr[$key]);
            }
        }
        trace($insertFieldsArr, 0, '$insertFieldsArr ('.__METHOD__.')');
        
        // if enabled, do charset conversion of all non-binary string data 
        if ($this->charsetConvEnabled == 1) {
            $insertFieldsArr = tx_pttools_div::iconvArray($insertFieldsArr, $this->siteCharset, $this->gsaCharset);
        }
        
        // exec query using TYPO3 DB API
        $res = $this->gsaDbObj->exec_INSERTquery($table, $insertFieldsArr);
        if ($res == false) {
            throw new tx_pttools_exception('Query failed', 1, $this->gsaDbObj->sql_error());
        }
        $lastInsertedId = $insertFieldsArr['NUMMER'];
        
        trace($lastInsertedId); 
        return $lastInsertedId;
        
    }
    
    /**
     * Inserts a new tax code and it's description into the GSA DB table 'STEUER'
     * 
     * @param   string      tax code to update (only codes '00'-'19' supported by the ERP!)
     * @param   string      description to update
     * @return  boolean                 TRUE on success or FALSE on error
     * @throws  tx_pttools_exception    if params are not valid
     * @throws  tx_pttools_exception    if the query fails/returns false
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2008-04-07
     */
    protected function insertTaxRateDescription($taxCode, $description) {
        
        if (!strlen($taxCode) > 0) {
            throw new tx_pttools_exception('Parameter error', 3, 'Tax code passed to '.__METHOD__.' is not valid.');
        }
        
        $table = $this->getTableName('STEUER');
        $insertFieldsArr = array();  
        
        // query preparation: fields to set  
        $insertFieldsArr['CODE']        = $taxCode;
        $insertFieldsArr['BEMERKUNG']   = $description;
        
        // unset NULL values - this is crucial since TYPO3's exec_INSERTquery() will quote all fields including NULL otherwise!!
        foreach ($insertFieldsArr as $key=>$value) {
            if (is_null($value)) {
                unset($insertFieldsArr[$key]);
            }
        }
        trace($insertFieldsArr, 0, '$insertFieldsArr ('.__METHOD__.')');
        
        // if enabled, do charset conversion of all non-binary string data 
        if ($this->charsetConvEnabled == 1) {
            $insertFieldsArr = tx_pttools_div::iconvArray($insertFieldsArr, $this->siteCharset, $this->gsaCharset);
        }
        
        // exec query using TYPO3 DB API
        $res = $this->gsaDbObj->exec_INSERTquery($table, $insertFieldsArr);
        if ($res == false) {
            throw new tx_pttools_exception('Query failed', 1, $this->gsaDbObj->sql_error());
        }
        
        trace($res); 
        return $res;
        
    }
        
    /**
     * Updates a given tax rate in the GSA DB table 'BHSTEUER'
     * 
     * @param   tx_ptgsaadmin_taxRate    object of type tx_ptgsaadmin_taxRate
     * @return  boolean     TRUE on success or FALSE on error
     * @throws  tx_pttools_exception   if the query fails/returns false
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2008-04-07
     */
    protected function updateTaxRateData(tx_ptgsaadmin_taxRate $taxRateObj) {
        
        $tmpTaxRate = $taxRateObj->get_taxRate();
        if (!strlen($taxRateObj->get_taxCode()) > 0 || empty($tmpTaxRate)) {
            throw new tx_pttools_exception('Parameter error', 3, 'Tax rate object passed to '.__METHOD__.' is not valid.');
        }
        
        $table = $this->getTableName('BHSTEUER');
        $where = 'NUMMER = '.intval($taxRateObj->get_recordUid());
        $updateFieldsArr = array();  
        $noQuoteFieldsArr = array(); 
        
        // query preparation 
        $updateFieldsArr['STEUERSATZCODE']      = $taxRateObj->get_taxCode();
        $updateFieldsArr['STEUERSATZPROZ']      = $taxRateObj->get_taxRate();
        $updateFieldsArr['GUELTIGABTTMMJJJJ']   = $taxRateObj->get_startDate();
        
        // check for NULL values - this is crucial since TYPO3's exec_UPDATEquery() will quote all fields including NULL otherwise!!
        foreach ($updateFieldsArr as $key=>$value) {
            if (is_null($value)) {
                $noQuoteFieldsArr[] = $key;
                $updateFieldsArr[$key] = 'NULL';  // combined with $noQuoteFieldsArr this is a hack to force TYPO3's exec_UPDATEquery() to update NULL
            }
        }
        
        // if enabled, do charset conversion of all non-binary string data 
        if ($this->charsetConvEnabled == 1) {
            $updateFieldsArr = tx_pttools_div::iconvArray($updateFieldsArr, $this->siteCharset, $this->gsaCharset);
        }
        
        // exec query using TYPO3 DB API
        $res = $this->gsaDbObj->exec_UPDATEquery($table, $where, $updateFieldsArr, $noQuoteFieldsArr);
        if ($res == false) {
            throw new tx_pttools_exception('Query failed', 1, $this->gsaDbObj->sql_error());
        }
        
        trace($res); 
        return $res;
        
    }
    
    /**
     * Updates the description of a given tax code in the GSA DB table 'STEUER'
     * 
     * @param   string      tax code to update (only codes '00'-'19' supported by the ERP!)
     * @param   string      description to update
     * @return  boolean     TRUE on success or FALSE on error
     * @throws  tx_pttools_exception   if the query fails/returns false
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2008-04-07
     */
    protected function updateTaxRateDescription($taxCode, $description) {
        
        if (!strlen($taxCode) > 0) {
            throw new tx_pttools_exception('Parameter error', 3, 'Tax code passed to '.__METHOD__.' is not valid.');
        }
        
        $table = $this->getTableName('STEUER');
        $where = 'CODE LIKE '.$this->gsaDbObj->fullQuoteStr($taxCode, $table);
        $updateFieldsArr = array();  
        $noQuoteFieldsArr = array(); 
        
        // query preparation 
        $updateFieldsArr['BEMERKUNG'] = $description;
        
        // check for NULL values - this is crucial since TYPO3's exec_UPDATEquery() will quote all fields including NULL otherwise!!
        foreach ($updateFieldsArr as $key=>$value) {
            if (is_null($value)) {
                $noQuoteFieldsArr[] = $key;
                $updateFieldsArr[$key] = 'NULL';  // combined with $noQuoteFieldsArr this is a hack to force TYPO3's exec_UPDATEquery() to update NULL
            }
        }
        
        // if enabled, do charset conversion of all non-binary string data 
        if ($this->charsetConvEnabled == 1) {
            $updateFieldsArr = tx_pttools_div::iconvArray($updateFieldsArr, $this->siteCharset, $this->gsaCharset);
        }
        
        // exec query using TYPO3 DB API
        $res = $this->gsaDbObj->exec_UPDATEquery($table, $where, $updateFieldsArr, $noQuoteFieldsArr);
        if ($res == false) {
            throw new tx_pttools_exception('Query failed', 1, $this->gsaDbObj->sql_error());
        }
        
        trace($res); 
        return $res;
        
    }
    
    /**
     * Deletes a tax rate record from the GSA DB table 'BHSTEUER'
     * 
     * @param   integer     GSA database UID of the tax rate (BHSTEUER.NUMMER)
     * @return  void                    
     * @throws  tx_pttools_exception   if params are not valid
     * @throws  tx_pttools_exception   if the query fails/returns false
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2008-04-07
     */
    protected function deleteTaxRateData($taxRateUid) {
        
        if (!is_numeric($taxRateUid)) {
            throw new tx_pttools_exception('Parameter error', 3, 'First parameter for '.__METHOD__.' is not a UID');
        }
        
        // query preparation     
        $table = $this->getTableName('BHSTEUER');
        $where = 'NUMMER = '.intval($taxRateUid);
        
        // exec query using TYPO3 DB API
        $res = $this->gsaDbObj->exec_DELETEquery($table, $where);
        if ($res == false) {
            throw new tx_pttools_exception('Query failed', 1, $this->gsaDbObj->sql_error());
        }
        
        trace($res);
        
    }
    
    /**
     * Deletes a tax description record from the GSA DB table 'STEUER'
     * 
     * @param   string      tax code to delete (only codes '00'-'19' supported by the ERP!)
     * @return  void                    
     * @throws  tx_pttools_exception   if params are not valid
     * @throws  tx_pttools_exception   if the query fails/returns false
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2008-04-07
     */
    protected function deleteTaxRateDescription($taxCode) {
    
        if (!strlen($taxCode) > 0) {
            throw new tx_pttools_exception('Parameter error', 3, 'Tax code passed to '.__METHOD__.' is not valid.');
        }
        
        // query preparation      
        $table = $this->getTableName('STEUER');
        $where = 'CODE LIKE '.$this->gsaDbObj->fullQuoteStr($taxCode, $table);
        
        // exec query using TYPO3 DB API
        $res = $this->gsaDbObj->exec_DELETEquery($table, $where);
        if ($res == false) {
            throw new tx_pttools_exception('Query failed', 1, $this->gsaDbObj->sql_error());
        }
        
        trace($res);
        
    }
    
    
    
} // end class



/*******************************************************************************
 *   TYPO3 XCLASS INCLUSION (for class extension/overriding)
 ******************************************************************************/
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsaadmin/res/class.tx_ptgsaadmin_taxRateAccessor.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsaadmin/res/class.tx_ptgsaadmin_taxRateAccessor.php']);
}

?>