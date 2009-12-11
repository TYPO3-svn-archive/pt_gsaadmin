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
 * Tax rate class for the 'pt_gsaadmin' extension.
 *
 * $Id: class.tx_ptgsaadmin_taxRate.php,v 1.1 2008/04/08 11:24:55 ry37 Exp $
 *
 * @author  Rainer Kuhn <kuhn@punkt.de>
 * @since   2008-04-03
 */ 
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 * 
 */
 
/**
 * Inclusion of GSA resources
 */
require_once t3lib_extMgm::extPath('pt_gsaadmin').'res/class.tx_ptgsaadmin_taxRateAccessor.php';  // GSA extended tax rate accessor class

/**
 * Inclusion of external resources
 */
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_debug.php'; // debugging class with trace() function



/**
 * Tax rate class (based on GSA database structure)
 * TODO: this class may be moved to pt_gsashop in later versions!
 * 
 * @author      Rainer Kuhn <kuhn@punkt.de>
 * @since       2008-04-03
 * @package     TYPO3
 * @subpackage  tx_ptgsadmin
 */
class tx_ptgsaadmin_taxRate {
    
    /**
     * Properties
     */
    protected $recordUid = 0;           // (integer) tax rate record UID / GSA: BHSTEUER.NUMMER
    protected $taxCode = '';            // (string) tax code of the tax rate (only codes '00'-'19' supported by the ERP!) / GSA-DB: BHSTEUER.STEUERSATZCODE and STEUER.CODE 
    protected $taxRate = 0.00;          // (double) tax rate / GSA-DB: BHSTEUER.STEUERSATZPROZ (and STEUER.NSATZ2)
    protected $startDate = '1970-01-01';      // (string) start date of the tax rate (format YYY-MM-DD) / GSA-DB: BHSTEUER.GUELTIGABTTMMJJJJ (and STEUER.DATUM)
    protected $taxDescription = '';     // (string) description of the tax rate / GSA-DB: STEUER.BEMERKUNG
    
    
    
    /***************************************************************************
     *   CONSTRUCTOR & OBJECT HANDLING METHODS
     **************************************************************************/
     
    /**
     * Class constructor: Sets the tax rate properties. If called without params, an "empty" object with default properties will be created.
     *
     * @param   integer     (optional) UID of the tax rate record to use (GSA-DB field 'BHSTEUER.NUMMER'). If set, the object will be built from the GSA-DB.  
     * @param   string      (optional) tax code of the tax rate to use (only codes '00'-'19' supported by the ERP!). If set, the object will be built from the GSA-DB.  This setting has no effect if 1st param not empty! If both params (1st and 2nd) are not set, an "empty" object with default properties will be created.
     * @param   string      (optional) used in combination with $taxCode only: date to get the tax rate for (date string format: YYYY-MM-DD) - if not set today's date will be used internally. This setting has no effect if the 2nd param is not set.
     * @return  void   
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2008-04-03
     */
    public function __construct($recordUid=0, $taxCode='', $date='') {
        
        
        $this->recordUid = (integer)$recordUid;
        $this->taxCode = (string)$taxCode;
        
        // if $recordUid or $taxCode is given, set object properties from GSA-DB
        if ($this->recordUid > 0 || strlen($this->taxCode) > 0) {
            $this->setTaxRateData($date);
        }
        
        trace($this);
        
    }
    
    
    
    /***************************************************************************
     *   GENERAL METHODS
     **************************************************************************/
     
    /**
     * Sets the object properties using data retrieved from GSA database queries. Requires $this->recordUid or $this->taxCode to be set.
     *
     * @param   (optional) date to get the tax rate for if retrieving by tax code (date string format: YYYY-MM-DD) - if not set today's date will be used internally
     * @return  void
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2008-04-03
     */
    protected function setTaxRateData($date='') {
    
        // retrieve tax data by record UID if it is set, retrieve by tax code if record UID is not set
        if ($this->recordUid > 0) {
            $taxDataArr = tx_ptgsaadmin_taxRateAccessor::getInstance()->selectTaxDataByUid($this->recordUid);
            $this->taxCode = (string)$taxDataArr['STEUERSATZCODE'];
        } else {
            $taxDataArr = tx_ptgsaadmin_taxRateAccessor::getInstance()->selectTaxDataByCode($this->taxCode, $date);
            $this->recordUid = (integer)$taxDataArr['NUMMER'];
        }
        
        $this->taxRate   = (double)$taxDataArr['STEUERSATZPROZ'];
        $this->startDate = (string)$taxDataArr['GUELTIGABTTMMJJJJ'];
        $this->taxDescription = (string)(empty($taxDataArr['BEMERKUNG']) ? $this->taxCode : $taxDataArr['BEMERKUNG']);
        
    }
    
    
    
    /***************************************************************************
     *   PROPERTY GETTER/SETTER METHODS
     **************************************************************************/

    
    /**
     * Returns the property value
     *
     * @param   void        
     * @return  integer      property value
     * @since   2008-04-03
     */
    public function get_recordUid() {
        
        return $this->recordUid;
        
    }
    
    /**
     * Returns the property value
     *
     * @param   void        
     * @return  string      property value
     * @since   2008-04-03
     */
    public function get_taxCode() {
        
        return $this->taxCode;
        
    }
    
    /**
     * Returns the property value
     *
     * @param   void        
     * @return  double      property value
     * @since   2008-04-03
     */
    public function get_taxRate() {
        
        return $this->taxRate;
        
    }
    
    /**
     * Returns the property value
     *
     * @param   void        
     * @return  string      property value (format YYY-MM-DD)
     * @since   2008-04-03
     */
    public function get_startDate() {
        
        return $this->startDate;
        
    }
    
    /**
     * Returns the property value
     *
     * @param   void        
     * @return  string      property value
     * @since   2008-04-03
     */
    public function get_taxDescription() {
        
        return $this->taxDescription;
        
    }
    
    /**
     * Returns the property value
     *
     * @param   integer      property value       
     * @return  void
     * @since   2008-04-03
     */
    public function set_recordUid($recordUid) {
        
        $this->recordUid = (string)$recordUid;
        
    }
    
    /**
     * Returns the property value
     *
     * @param   string      property value       
     * @return  void
     * @since   2008-04-03
     */
    public function set_taxCode($taxCode) {
        
        $this->taxCode = (string)$taxCode;
        
    }
    
    /**
     * Returns the property value
     *
     * @param   double       property value   
     * @return  void
     * @since   2008-04-03
     */
    public function set_taxRate($taxRate) {
        
        $this->taxRate = (is_null($taxRate) ? NULL : (double)$taxRate);
        
    }
    
    /**
     * Returns the property value
     *
     * @param   string      property value (format YYY-MM-DD)
     * @return  void
     * @since   2008-04-03
     */
    public function set_startDate($startDate) {
        
        $this->startDate = (string)$startDate;
        
    }
    
    /**
     * Returns the property value
     *
     * @param   string      property value       
     * @return  void
     * @since   2008-04-03
     */
    public function set_taxDescription($taxDescription) {
        
        $this->taxDescription = (string)$taxDescription;
        
    }
    
    
} // end class



/*******************************************************************************
 *   TYPO3 XCLASS INCLUSION (for class extension/overriding)
 ******************************************************************************/
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsaadmin/res/class.tx_ptgsaadmin_taxRate.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsaadmin/res/class.tx_ptgsaadmin_taxRate.php']);
}

?>