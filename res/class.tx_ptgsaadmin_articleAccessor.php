<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2007-2008 Rainer Kuhn (kuhn@punkt.de)
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
 * Database _modifying_ accessor class for articles (part of the 'pt_gsaadmin' extension)  - this is a (temporary) addition to the _readonly_ accessor tx_ptgsashop_articleAccessor
 *
 * $Id: class.tx_ptgsaadmin_articleAccessor.php,v 1.23 2009/03/26 14:33:38 ry21 Exp $
 *
 * @author  Rainer Kuhn <kuhn@punkt.de>
 * @since   2007-10-18
 */ 


/**
 * Inclusion of TYPO3 resources
 */
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_debug.php'; // debugging class with trace() function
require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_exception.php'; // general exception class
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_div.php'; // general helper library class
require_once t3lib_extMgm::extPath('pt_tools').'res/abstract/class.tx_pttools_iSingleton.php'; // interface for Singleton design pattern
require_once t3lib_extMgm::extPath('pt_gsashop').'res/class.tx_ptgsashop_scalePrice.php';// GSA Shop article scale price class
require_once t3lib_extMgm::extPath('pt_gsashop').'res/class.tx_ptgsashop_articleAccessor.php';  // GSA Shop database accessor class for articles



/**
 * Database  _modifying_ accessor class for GSA articles, based on GSA database structure - this is a (temporary) addition to the _readonly_ accessor tx_ptgsashop_articleAccessor
 * NOTICE: This class contains temporary solutions since structure and meaning of the GSA database tables is not completely investigated!!
 * TODO: Temporary GSA database specific solutions should be overhauled in a clean OOP way if the mysteries of the GSA database have cleared up!
 *
 * @author      Rainer Kuhn <kuhn@punkt.de>
 * @since       2007-10-18
 * @package     TYPO3
 * @subpackage  tx_ptgsashop
 */
class tx_ptgsaadmin_articleAccessor extends tx_ptgsashop_articleAccessor implements tx_pttools_iSingleton {
    
    /**
     * @var tx_ptgsaadmin_articleAccessor       Singleton unique instance
     */
    protected static $uniqueInstance = NULL;
    
    
    
    /***************************************************************************
     *   CONSTRUCTOR & OBJECT HANDLING METHODS
     **************************************************************************/
    
    /**
     * Returns a unique instance (Singleton) of the object. Use this method instead of the private/protected class constructor.
     *
     * @param   void
     * @return  tx_ptgsaadmin_articleAccessor      unique instance of the object (Singleton) 
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
     *   PUBLIC HANDLER METHODS (depending on GSA DB requirements etc.)    // TODO: these could be moved to tx_ptgsashop_articleAccessor?
     **************************************************************************/
     
    /**
     * Inserts a given article as a new article to the database(s)
     *
     * @param   tx_ptgsashop_baseArticle    object of type tx_ptgsashop_baseArticle, article to save
     * @return  int							uid of the inserted article record
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2007-10-18
     */
    public function insertNewArticle(tx_ptgsashop_baseArticle $articleObj) {
        
        // insert GSA database related data (article basic data, prices etc.)
        $articleUid = $this->insertArticleBasicData($articleObj);   // writes to GSA-DB.ARTIKEL
        $this->insertRebateMatrixRecord($articleUid);               // writes to GSA-DB.RAMATRIX
        foreach ($articleObj->get_scalePriceCollectionObj() as $scalePriceObj) {
            $this->insertDefaultRetailPricingData($articleUid, $scalePriceObj);  // writes to GSA-DB.VKPREIS
        }
        #$this->insertCustomerSpecificPricingData($this);   // writes to GSA-DB.KUNPREIS ??  // TODO: not implemented yet    
        #$this->insertArticleSuppliersData($this);          // writes to GSA-DB.LIEFART ??   // TODO: not implemented yet  
        
        // insert TYPO3 database related data (article relations etc.)
        #$this->insertArticleRelationData($this);          // writes to TYPO3-DB.tx_ptgsashop_artrel    // TODO: not implemented yet   
        #$$this->insertImageData($this);                   // writes to TYPO3-DB.tx_ptgsashop_artrel ?? // TODO: not implemented yet
            
        return $articleUid;
        
    }
     
    /**
     * Updates a given article in the database(s)
     *
     * @param   tx_ptgsashop_baseArticle    object of type tx_ptgsashop_baseArticle, article to save
     * @return  int     uid of the updated article record
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2007-10-22
     */
    public function updateArticle(tx_ptgsashop_baseArticle $articleObj) {
        
        $existingScalesArray = $this->selectScalePriceQuantities($articleObj->get_id());
        
        // insert GSA database related data (article basic data, prices etc.)
        $this->updateArticleBasicData($articleObj);                                       // writes to GSA-DB.ARTIKEL
        foreach ($articleObj->get_scalePriceCollectionObj() as $scaleQty=>$scalePriceObj) {
            // update/delete existing scale prices
            if (in_array($scaleQty, $existingScalesArray)) {
                if ($scalePriceObj->get_isDeleted() == 1) {
                    $this->deleteDefaultRetailPricingData($articleObj->get_id(), $scaleQty); // deletes from GSA-DB.VKPREIS
                } else {
                    $this->updateDefaultRetailPricingData($scalePriceObj); // writes to GSA-DB.VKPREIS
                }
            // insert new scale prices (if they are not marked as deleted)
            } elseif ($scalePriceObj->get_isDeleted() == 0) {
                $this->insertDefaultRetailPricingData($articleObj->get_id(), $scalePriceObj); // inserts in GSA-DB.VKPREIS
            } 
        }
        #$this->updateRebateMatrixRecord($articleUid);      // writes to GSA-DB.RAMATRIX     // TODO: not implemented yet 
        #$this->updateCustomerSpecificPricingData($this);   // writes to GSA-DB.KUNPREIS ??  // TODO: not implemented yet    
        #$this->updateArticleSuppliersData($this);          // writes to GSA-DB.LIEFART ??   // TODO: not implemented yet  
        
        // insert TYPO3 database related data (article relations etc.)
        #$this->updateArticleRelationData($this);          // writes to TYPO3-DB.tx_ptgsashop_artrel    // TODO: not implemented yet   
        #$$this->updateImageData($this);                   // writes to TYPO3-DB.tx_ptgsashop_artrel ?? // TODO: not implemented yet 
           
        return $articleObj->get_id();
        
    }
    
    /**
     * Deletes an article specified by UID from the database(s)
     *
     * @param   integer     GSA database UID of the article (ARTIKEL.NUMMER)
     * @return  void
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2007-10-22
     */
    public function deleteArticle($articleUid) {
        
        // insert TYPO3 database related data (article relations etc.)
        #$this->insertArticleRelationData($this);            // deletes from TYPO3-DB.tx_ptgsashop_artrel    // TODO: not implemented yet. ACHTUNG: Hier muessen Artikel-Relationen bei allen anderen Artikeln bereinigt werden! 
        #$$this->insertImageData($this);                     // deletes from TYPO3-DB.tx_ptgsashop_artrel ?? // TODO: not implemented yet    
        
        // delete GSA database related data (article basic data, prices etc.)
        #$this->deleteArticleSuppliersData($this);           // deletes from GSA-DB.LIEFART ??   // TODO: not implemented yet    
        #$this->deleteCustomerSpecificPricingData($this);    // deletes from GSA-DB.KUNPREIS ??  // TODO: not implemented yet    
        $this->deleteDefaultRetailPricingData($articleUid); // deletes from GSA-DB.VKPREIS
        $this->deleteRebateMatrixRecord($articleUid);       // deletes from GSA-DB.RAMATRIX
        $this->deleteArticleBasicData($articleUid);         // deletes from GSA-DB.ARTIKEL
        
    }
    
    
    
    /***************************************************************************
     *   GSA DB RELATED METHODS                      // TODO: these should be moved to tx_ptgsashop_articleAccessor
     **************************************************************************/
    
    /**
     * Inserts a new article and it's the basic data into the GSA DB table 'ARTIKEL' and returns the inserted record's UID
     * 
     * @param   tx_ptgsashop_baseArticle    object of type tx_ptgsashop_baseArticle
     * @return  integer     UID of the inserted record
     * @throws  tx_pttools_exception   if params are not valid
     * @throws  tx_pttools_exception   if the query fails/returns false
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2007-10-18
     */
    protected function insertArticleBasicData(tx_ptgsashop_baseArticle $articleObj) {
        
        if (!strlen($articleObj->get_artNo()) > 0 || !strlen($articleObj->get_match1()) > 0) {
            throw new tx_pttools_exception('Parameter error', 3, 'Article passed to '.__METHOD__.' is not valid.');
        }
        
        $table = $this->getTableName('ARTIKEL');
        $insertFieldsArr = array();  
        
        // query preparation: known fields to set  
        $insertFieldsArr['NUMMER']          = $this->getNextId($table);
        $insertFieldsArr['ARTNR']           = $articleObj->get_artNo();
        $insertFieldsArr[$table.'.MATCH']    = $articleObj->get_match1(); // MATCH is a reserved SQL word, so prefixing by table is neccessary
        $insertFieldsArr[$table.'.MATCH2']   = (strlen($articleObj->get_match2()) > 0 ? $articleObj->get_match2() : NULL);
        $insertFieldsArr['ZUSTEXT1']        = (strlen($articleObj->get_defText()) > 0 ? $articleObj->get_defText() : '');   // '' is set per default by ERP-GUI
        $insertFieldsArr['ZUSTEXT2']        = (strlen($articleObj->get_altText()) > 0 ? $articleObj->get_altText() : '');   // '' is set per default by ERP-GUI
        $insertFieldsArr['PRBRUTTO']        = ($articleObj->get_grossPriceFlag() == true ? 1 : 0);   // 0 is set per default by ERP-GUI 
        $insertFieldsArr['USTSATZ']         = (strlen($articleObj->get_taxCodeInland()) > 0 ? $articleObj->get_taxCodeInland() : '01');   // '01' is set per default by ERP-GUI
        $insertFieldsArr['FIXKOST1']        = ($articleObj->get_fixedCost1() > 0 ? $articleObj->get_fixedCost1() : NULL);
        $insertFieldsArr['FIXKOST2']        = ($articleObj->get_fixedCost2() > 0 ? $articleObj->get_fixedCost2() : NULL);
        $insertFieldsArr['ONLINEARTIKEL']   = ($articleObj->get_isOnlineArticle() == true ? 1 : 0);   // TODO: 'Online-Artikel?' could be needed in pt_gsaadmin GUI...
        $insertFieldsArr['PASSIV']          = ($articleObj->get_isPassive() == true ? 1 : 0);
        $insertFieldsArr['WEBADRESSE']      = (strlen($articleObj->get_webAddress()) > 0 ? $articleObj->get_webAddress() : NULL);
        $insertFieldsArr['FLD01']      = (strlen($articleObj->get_userField01()) > 0 ? $articleObj->get_userField01() : NULL);
        $insertFieldsArr['FLD02']      = (strlen($articleObj->get_userField02()) > 0 ? $articleObj->get_userField02() : NULL);
        $insertFieldsArr['FLD03']      = (strlen($articleObj->get_userField03()) > 0 ? $articleObj->get_userField03() : NULL);
        $insertFieldsArr['FLD04']      = (strlen($articleObj->get_userField04()) > 0 ? $articleObj->get_userField04() : NULL);
        $insertFieldsArr['FLD05']      = (strlen($articleObj->get_userField05()) > 0 ? $articleObj->get_userField05() : NULL);
        $insertFieldsArr['FLD06']      = (strlen($articleObj->get_userField06()) > 0 ? $articleObj->get_userField06() : NULL);
        $insertFieldsArr['FLD07']      = (strlen($articleObj->get_userField07()) > 0 ? $articleObj->get_userField07() : NULL);
        $insertFieldsArr['FLD08']      = (strlen($articleObj->get_userField08()) > 0 ? $articleObj->get_userField08() : NULL);
        $insertFieldsArr['EANNUMMER']  = (strlen($articleObj->get_eanNumber()) > 0 ? $articleObj->get_eanNumber() : NULL);
        $insertFieldsArr['AKTDAT']     = date('Y-m-d');
        
        // TODO: GSA DB fields set per default by ERP-GUI, currently unused by pt_gsaadmin GUI
        $insertFieldsArr['PEINHEIT']    = 1.000;
        $insertFieldsArr['LEINHEIT']    = 1.000;
        $insertFieldsArr['ZEINHEIT']    = 1.000;
        $insertFieldsArr['VEINHEIT']    = 1.000;
        $insertFieldsArr['LAGART']      = 0;
        $insertFieldsArr['BESTZEIGE']   = 0;
        $insertFieldsArr['EINHEIT']     = 'Stück';  // TODO: should be used in pt_gsaadmin GUI?
        $insertFieldsArr['EKPR01']      = 0.000;
        $insertFieldsArr['EKPR02']      = 0.000;
        $insertFieldsArr['EKPR03']      = 0.000;
        $insertFieldsArr['EKMITTEL']    = 0.000;
        $insertFieldsArr['EKLAGER']     = 0.000;
        $insertFieldsArr['USTAUSLAND']  = '00';
        $insertFieldsArr['USTEG']       = 0.000;
        $insertFieldsArr['MENGE2']      = 1.000;
        $insertFieldsArr['MENGE3']      = 1.000;
        $insertFieldsArr['MENGE4']      = 1.000;
        $insertFieldsArr['ZWSUMME']     = 0.000;
        $insertFieldsArr['MITGESRAB']   = 0;
        $insertFieldsArr['MITSKONTO']   = 0;
        $insertFieldsArr['LAGER']       = 'Hauptlager';
        $insertFieldsArr['WG']          = 'Artikel (Standard)';  // TODO: 'Warengruppe' should be used in pt_gsaadmin GUI!!
        $insertFieldsArr['SNRART']      = 0;
        $insertFieldsArr['GARANTIE']    = 0.000;
        $insertFieldsArr['GARANTIEMONATE'] = 6.000;
        $insertFieldsArr['SNRNULLEN']   = 0.000;
        $insertFieldsArr['GARANTIEE']   = 0.000;
        $insertFieldsArr['GARANTIEEMONATE'] = 6.000;
        $insertFieldsArr['EURO']        = 1;
        $insertFieldsArr['ALTTEIL']     = 0;
        $insertFieldsArr['STLIST']      = 0;
        $insertFieldsArr['PASSIV']      = 0;
        $insertFieldsArr['STLISTHAUPTPREIS'] = 0;
        $insertFieldsArr['DPOSTEN']     = 0;
        $insertFieldsArr['NOFAKT']      = 0;
        $insertFieldsArr['FAVORIT']     = 0;
        
        
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
     * Inserts a rebate matrix record for an article into the GSA DB table 'RAMATRIX' and returns the inserted record's UID
     * 
     * @param   integer     GSA database UID of the article (ARTIKEL.NUMMER)
     * @return  integer     UID of the inserted record
     * @throws  tx_pttools_exception   if params are not valid
     * @throws  tx_pttools_exception   if the query fails/returns false
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2007-10-18
     */
    protected function insertRebateMatrixRecord($gsaArticleUid) {
        
        if (!is_numeric($gsaArticleUid)) {
            throw new tx_pttools_exception('Parameter error', 3, 'First parameter for '.__METHOD__.' is not a UID');
        }
        
        $table = $this->getTableName('RAMATRIX');
        $insertFieldsArr = array();  
        
        // query preparation     
        $insertFieldsArr['NUMMER'] = $this->getNextId($table);
        $insertFieldsArr['ARTINR'] = $gsaArticleUid;     // points to ARTIKEL.NUMMER
        
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
     * Inserts a default retail price record for an article into the GSA DB table 'VKPREIS' and returns the inserted record's UID
     * 
     * Notice: GSA DB table VKPREIS fields PR99_2-PR99_5, DATUMVON2-DATUMVON5 and DATUMBIS2-DATUMBIS5 
     * are currently unaccounted for pricing data since nobody knows what they are used for :)
     * 
     * @param   integer     GSA database UID of the article (ARTIKEL.NUMMER) the scale price record is related to
     * @param   tx_ptgsashop_scalePrice     scale price object for the article to insert
     * @return  integer     UID of the inserted record
     * @throws  tx_pttools_exception   if params are not valid
     * @throws  tx_pttools_exception   if the query fails/returns false
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2007-10-18
     */
    protected function insertDefaultRetailPricingData($gsaArticleUid, tx_ptgsashop_scalePrice $scalePriceObj) {
        
        if (!is_numeric($gsaArticleUid) || $gsaArticleUid < 1) {
            throw new tx_pttools_exception('Parameter error', 3, 'First parameter for '.__METHOD__.' is not a valid UID');
        }
        if ($scalePriceObj->get_quantity() < 1) {
            throw new tx_pttools_exception('Parameter error', 3, '1st param for '.__METHOD__.' is not a valid scale price object to store in the database!');
        }
        
        $table = $this->getTableName('VKPREIS');
        $insertFieldsArr = array();  
        
        // query preparation: known fields to set
        $insertFieldsArr['NUMMER']  = $this->getNextId($table);
        $insertFieldsArr['ARTINR']  = $gsaArticleUid;     // points to ARTIKEL.NUMMER
        $insertFieldsArr['ABMENGE'] = $scalePriceObj->get_quantity();
        $insertFieldsArr['PR01']    = $scalePriceObj->get_basicRetailPriceCategory1();
        $insertFieldsArr['PR02']    = $scalePriceObj->get_basicRetailPriceCategory2();
        $insertFieldsArr['PR03']    = $scalePriceObj->get_basicRetailPriceCategory3();
        $insertFieldsArr['PR04']    = $scalePriceObj->get_basicRetailPriceCategory4();
        $insertFieldsArr['PR05']    = $scalePriceObj->get_basicRetailPriceCategory5();
        $insertFieldsArr['AKTION']  = $scalePriceObj->get_specialOfferFlag();                      // if flag isn't set, a 0 has to be written to VKPREIS.AKTION
        $insertFieldsArr['DATUMVON']= $scalePriceObj->get_specialOfferStartDate();
        $insertFieldsArr['DATUMBIS']= $scalePriceObj->get_specialOfferEndDate();
        $insertFieldsArr['PR99']    = $scalePriceObj->get_specialOfferRetailPrice();
        
        // TODO: GSA DB fields set per default by ERP-GUI, currently unused by pt_gsaadmin and tx_ptgsashop_scalePrice
        $insertFieldsArr['ART01']  = 1;  // ERP-GUI: Input field directly after "VK-Preis 1"
        $insertFieldsArr['ART02']  = 1;  // ERP-GUI: Input field directly after "VK-Preis 2"
        $insertFieldsArr['ART03']  = 1;  // ERP-GUI: Input field directly after "VK-Preis 3"
        $insertFieldsArr['ART04']  = 1;  // ERP-GUI: Input field directly after "VK-Preis 4"
        $insertFieldsArr['ART05']  = 1;  // ERP-GUI: Input field directly after "VK-Preis 5"
        $insertFieldsArr['DB01']   = 0.0000;  // ERP-GUI: Field "Aufschlag %" for "VK-Preis 1"
        $insertFieldsArr['DB02']   = 0.0000;  // ERP-GUI: Field "Aufschlag %" for "VK-Preis 2"
        $insertFieldsArr['DB03']   = 0.0000;  // ERP-GUI: Field "Aufschlag %" for "VK-Preis 3"
        $insertFieldsArr['DB04']   = 0.0000;  // ERP-GUI: Field "Aufschlag %" for "VK-Preis 4"
        $insertFieldsArr['DB05']   = 0.0000;  // ERP-GUI: Field "Aufschlag %" for "VK-Preis 5"
        
        
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
     * Updates a given article in the GSA DB table 'ARTIKEL'
     * 
     * @param   tx_ptgsashop_baseArticle    object of type tx_ptgsashop_baseArticle
     * @return  boolean     TRUE on success or FALSE on error
     * @throws  tx_pttools_exception   if the query fails/returns false
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2007-10-22
     */
    protected function updateArticleBasicData(tx_ptgsashop_baseArticle $articleObj) {
        
        $table = $this->getTableName('ARTIKEL');
        $where = 'NUMMER = '.intval($articleObj->get_id());
        $updateFieldsArr = array();  
        $noQuoteFieldsArr = array(); 
        
        // query preparation 
        $updateFieldsArr['ARTNR']           = $articleObj->get_artNo();
        $updateFieldsArr[$table.'.MATCH']   = $articleObj->get_match1();  // MATCH is a reserved SQL word, so prefixing by table is neccessary
        $updateFieldsArr[$table.'.MATCH2']  = (strlen($articleObj->get_match2()) > 0 ? $articleObj->get_match2() : NULL);
        $updateFieldsArr['ZUSTEXT1']        = (strlen($articleObj->get_defText()) > 0 ? $articleObj->get_defText() : '');   // '' is set per default by ERP-GUI
        $updateFieldsArr['ZUSTEXT2']        = (strlen($articleObj->get_altText()) > 0 ? $articleObj->get_altText() : '');   // '' is set per default by ERP-GUI
        $updateFieldsArr['PRBRUTTO']        = ($articleObj->get_grossPriceFlag() == true ? 1 : 0);   // 0 is set per default by ERP-GUI 
        $updateFieldsArr['USTSATZ']         = (strlen($articleObj->get_taxCodeInland()) > 0 ? $articleObj->get_taxCodeInland() : '01');   // '01' is set per default by ERP-GUI
        $updateFieldsArr['FIXKOST1']        = ($articleObj->get_fixedCost1() > 0 ? $articleObj->get_fixedCost1() : NULL);
        $updateFieldsArr['FIXKOST2']        = ($articleObj->get_fixedCost2() > 0 ? $articleObj->get_fixedCost2() : NULL);
        $updateFieldsArr['ONLINEARTIKEL']   = ($articleObj->get_isOnlineArticle() == true ? 1 : 0);   // TODO: 'Online-Artikel?'  could be needed in pt_gsaadmin GUI...
        $updateFieldsArr['PASSIV']          = ($articleObj->get_isPassive() == true ? 1 : 0);
        $updateFieldsArr['WEBADRESSE']      = (strlen($articleObj->get_webAddress()) > 0 ? $articleObj->get_webAddress() : NULL);
        $updateFieldsArr['FLD01']      = (strlen($articleObj->get_userField01()) > 0 ? $articleObj->get_userField01() : NULL);
        $updateFieldsArr['FLD02']      = (strlen($articleObj->get_userField02()) > 0 ? $articleObj->get_userField02() : NULL);
        $updateFieldsArr['FLD03']      = (strlen($articleObj->get_userField03()) > 0 ? $articleObj->get_userField03() : NULL);
        $updateFieldsArr['FLD04']      = (strlen($articleObj->get_userField04()) > 0 ? $articleObj->get_userField04() : NULL);
        $updateFieldsArr['FLD05']      = (strlen($articleObj->get_userField05()) > 0 ? $articleObj->get_userField05() : NULL);
        $updateFieldsArr['FLD06']      = (strlen($articleObj->get_userField06()) > 0 ? $articleObj->get_userField06() : NULL);
        $updateFieldsArr['FLD07']      = (strlen($articleObj->get_userField07()) > 0 ? $articleObj->get_userField07() : NULL);
        $updateFieldsArr['FLD08']      = (strlen($articleObj->get_userField08()) > 0 ? $articleObj->get_userField08() : NULL);
        $updateFieldsArr['EANNUMMER']  = (strlen($articleObj->get_eanNumber()) > 0 ? $articleObj->get_eanNumber() : NULL);
        $updateFieldsArr['AKTDAT']     = date('Y-m-d');
        
        // check for NULL values - this is crucial since TYPO3's exec_UPDATEquery() will quote all fields including NULL otherwise!!
        foreach ($updateFieldsArr as $key=>$value) {
            if (is_null($value)) {
                $noQuoteFieldsArr[] = $key;
                $updateFieldsArr[$key] = 'NULL';  // combined with $noQuoteFieldsArr this is a hack to force TYPO3's exec_UPDATEquery() to update NULL
            }
        }
        trace($updateFieldsArr, 0, '$updateFieldsArr ('.__METHOD__.')');
        trace($noQuoteFieldsArr, 0, '$noQuoteFieldsArr ('.__METHOD__.')');
        
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
     * Updates a default retail price record for an article in the GSA DB table 'VKPREIS'
     * 
     * @param   tx_ptgsashop_scalePrice     scale price object for the article to update
     * @return  boolean     TRUE on success or FALSE on error
     * @throws  tx_pttools_exception   if params are not valid
     * @throws  tx_pttools_exception   if the query fails/returns false
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2007-10-22
     */
    protected function updateDefaultRetailPricingData(tx_ptgsashop_scalePrice $scalePriceObj) {
        
        if ($scalePriceObj->get_articleUid() < 1 || $scalePriceObj->get_quantity() < 1) {
            throw new tx_pttools_exception('Parameter error', 3, '1st param for '.__METHOD__.' is not a valid scale price object to store in the database!');
        }
        
        $table = $this->getTableName('VKPREIS');
        $where = 'ARTINR = '.intval($scalePriceObj->get_articleUid()).' '.
                 'AND ABMENGE = '.doubleval($scalePriceObj->get_quantity());
        $updateFieldsArr = array();  
        $noQuoteFieldsArr = array(); 
        
        // query preparation
        $updateFieldsArr['PR01']    = $scalePriceObj->get_basicRetailPriceCategory1();
        $updateFieldsArr['PR02']    = $scalePriceObj->get_basicRetailPriceCategory2();
        $updateFieldsArr['PR03']    = $scalePriceObj->get_basicRetailPriceCategory3();
        $updateFieldsArr['PR04']    = $scalePriceObj->get_basicRetailPriceCategory4();
        $updateFieldsArr['PR05']    = $scalePriceObj->get_basicRetailPriceCategory5();
        $updateFieldsArr['AKTION']  = $scalePriceObj->get_specialOfferFlag();                       // if flag isn't set, a 0 has to be written to VKPREIS.AKTION
        $updateFieldsArr['DATUMVON']= $scalePriceObj->get_specialOfferStartDate();
        $updateFieldsArr['DATUMBIS']= $scalePriceObj->get_specialOfferEndDate();
        $updateFieldsArr['PR99']    = $scalePriceObj->get_specialOfferRetailPrice();
        
        
        // check for NULL values - this is crucial since TYPO3's exec_UPDATEquery() will quote all fields including NULL otherwise!!
        foreach ($updateFieldsArr as $key=>$value) {
            if (is_null($value)) {
                $noQuoteFieldsArr[] = $key;
                $updateFieldsArr[$key] = 'NULL';  // combined with $noQuoteFieldsArr this is a hack to force TYPO3's exec_UPDATEquery() to update NULL
            }
        }
        trace($updateFieldsArr, 0, '$updateFieldsArr ('.__METHOD__.')');
        trace($noQuoteFieldsArr, 0, '$noQuoteFieldsArr ('.__METHOD__.')');
        
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
     * Deletes an article record from the GSA DB table 'ARTIKEL'
     * 
     * @param   integer     GSA database UID of the article (ARTIKEL.NUMMER)
     * @return  void                    
     * @throws  tx_pttools_exception   if params are not valid
     * @throws  tx_pttools_exception   if the query fails/returns false
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2007-10-22
     */
    protected function deleteArticleBasicData($gsaArticleUid) {
        
        if (!is_numeric($gsaArticleUid)) {
            throw new tx_pttools_exception('Parameter error', 3, 'First parameter for '.__METHOD__.' is not a UID');
        }
        
        // query preparation     
        $table = $this->getTableName('ARTIKEL');
        $where = 'NUMMER = '.intval($gsaArticleUid);
        
        // exec query using TYPO3 DB API
        $res = $this->gsaDbObj->exec_DELETEquery($table, $where);
        if ($res == false) {
            throw new tx_pttools_exception('Query failed', 1, $this->gsaDbObj->sql_error());
        }
        
    }
    
    /**
     * Deletes an rebate matrix record from the GSA DB table 'RAMATRIX'
     * 
     * @param   integer     GSA database UID of the article (ARTIKEL.NUMMER)
     * @return  void                    
     * @throws  tx_pttools_exception   if params are not valid
     * @throws  tx_pttools_exception   if the query fails/returns false
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2007-10-22
     */
    protected function deleteRebateMatrixRecord($gsaArticleUid) {
        
        if (!is_numeric($gsaArticleUid)) {
            throw new tx_pttools_exception('Parameter error', 3, 'First parameter for '.__METHOD__.' is not a UID');
        }
        
        // query preparation      
        $table = $this->getTableName('RAMATRIX');
        $where = 'ARTINR = '.intval($gsaArticleUid);
        
        // exec query using TYPO3 DB API
        $res = $this->gsaDbObj->exec_DELETEquery($table, $where);
        if ($res == false) {
            throw new tx_pttools_exception('Query failed', 1, $this->gsaDbObj->sql_error());
        }
        
    }
    
    /**
     * Deletes one or all (depending on params) scale price record(s) related to an article from the GSA DB table 'VKPREIS' 
     * 
     * @param   integer     GSA database UID of the article (ARTIKEL.NUMMER)
     * @param   integer     (optional) scale price quantity to delete its record (VKPREIS.ABMENGE); if not set (default), all records related to the article (1st param) will be deleted
     * @return  void                    
     * @throws  tx_pttools_exception   if params are not valid
     * @throws  tx_pttools_exception   if the query fails/returns false
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2007-10-22
     */
    protected function deleteDefaultRetailPricingData($gsaArticleUid, $scalePriceQuantity=0) {
        
        if (!is_numeric($gsaArticleUid)) {
            throw new tx_pttools_exception('Parameter error', 3, 'First parameter for '.__METHOD__.' is not a UID');
        }
        if (!is_numeric($scalePriceQuantity)) {
            throw new tx_pttools_exception('Parameter error', 3, '2nd parameter for '.__METHOD__.' is not valid');
        }
        
        // query preparation      
        $table = $this->getTableName('VKPREIS');
        $where = 'ARTINR = '.intval($gsaArticleUid);
        if ($scalePriceQuantity > 0) {
            $where .= ' AND ABMENGE = '.intval($scalePriceQuantity);
        }
        
        // exec query using TYPO3 DB API
        $res = $this->gsaDbObj->exec_DELETEquery($table, $where);
        if ($res == false) {
            throw new tx_pttools_exception('Query failed', 1, $this->gsaDbObj->sql_error());
        }
        
    }
    
    
    
} // end class



/*******************************************************************************
 *   TYPO3 XCLASS INCLUSION (for class extension/overriding)
 ******************************************************************************/
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsaadmin/res/class.tx_ptgsaadmin_articleAccessor.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsaadmin/res/class.tx_ptgsaadmin_articleAccessor.php']);
}

?>