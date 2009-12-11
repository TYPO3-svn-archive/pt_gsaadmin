<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2008 Fabrizio Branca <branca@punkt.de>
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
 * List class
 *
 * $Id: class.tx_ptgsaadmin_list.php,v 1.4 2008/02/26 15:02:24 ry37 Exp $
 *
 * @author  Fabrizio Branca <branca@punkt.de>
 * @since   2008-01-14
 */ 


/**
 * Inclusion of TYPO3 resources
 */ 
require_once t3lib_extMgm::extPath('pt_gsaadmin').'res/list/class.tx_ptgsaadmin_row.php';

require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_debug.php'; // debugging class with trace() function
require_once t3lib_extMgm::extPath('pt_tools').'res/abstract/class.tx_pttools_objectCollection.php'; // abstract object collection class





/**
 * List class (collection of tx_ptgsaadmin_row objects)
 * 
 * TODO: später mal in pt_tools auslagern?
 * 
 * @author  Fabrizio Branca <branca@punkt.de>
 * @since   2008-01-14
 * @package     TYPO3
 */
class tx_ptgsaadmin_list extends tx_pttools_objectCollection implements IteratorAggregate, Countable {
    
    /**
     * @var 	string		title of the list
     */
    protected $title = '';
    
    /**
     * @var 	tx_ptgsaadmin_row	row containing the cells for the header cells
     */
    protected $tableHeadRow;
    
    /**
     * @var 	string		page up button
     */
    protected $pageUpBtn = '';
    
    /**
     * @var 	string		page down button
     */
    protected $pageDownBtn = '';
    
    /**
     * @var 	array		paramater to pass to the smarty template
     */
    protected $passParameter = array();
    
    
    /**
     * Set property value
     *
     * @param 	string	property value
     * @return	void
     * @author	Fabrizio Branca <branca@punkt.de>
     * @since 	2008-01-14
     */
    public function set_title($title) {
        $this->title = $title;
    }
    
    
    
	/**
     * Set property value
     *
     * @param 	string	property value
     * @return	void
     * @author	Fabrizio Branca <branca@punkt.de>
     * @since 	2008-01-14
     */
    public function set_pageUpBtn($pageUpBtn) {
        $this->pageUpBtn = $pageUpBtn;
    }
    
    
    
    /**
     * Set property value
     *
     * @param 	string	property value
     * @return	void
     * @author	Fabrizio Branca <branca@punkt.de>
     * @since 	2008-01-14
     */
    public function set_pageDownBtn($pageUpBtn) {
        $this->pageDownBtn = $pageUpBtn;
    }
    
    
    
    /**
     * Set property value
     *
     * @param 	tx_ptgsaadmin_row	property value
     * @return	void
     * @author	Fabrizio Branca <branca@punkt.de>
     * @since 	2008-01-14
     */
    public function set_tableHeadRow(tx_ptgsaadmin_row $tabelHeadRow) {
        $this->tableHeadRow = $tabelHeadRow;
    }
    
    
    
    /**
     * Set property value
     *
     * @param 	tx_ptgsaadmin_row	property value
     * @return	void
     * @author	Fabrizio Branca <branca@punkt.de>
     * @since 	2008-01-14
     */
    public function set_passParameter(array $passParameter) {
        $this->passParameter = $passParameter;
    }
    
    
    
    
	/**
     * Get property value
     *
     * @param 	void
     * @return	string	property value
     * @author	Fabrizio Branca <branca@punkt.de>
     * @since 	2008-01-14
     */
    public function get_title() {
        return $this->title;
    }
    
    
    
	/**
     * Get property value
     *
     * @param 	void
     * @return	string	property value
     * @author	Fabrizio Branca <branca@punkt.de>
     * @since 	2008-01-14
     */
    public function get_pageUpBtn() {
        return $this->pageUpBtn;
    }
    
    
    
    /**
     * Get property value
     *
     * @param 	void
     * @return	string	property value
     * @author	Fabrizio Branca <branca@punkt.de>
     * @since 	2008-01-14
     */
    public function get_pageDownBtn() {
        return $this->pageDownBtn;
    }
    
    
    
	/**
     * Get property value
     *
     * @param 	void
     * @return	tx_ptgsaadmin_row	property value
     * @author	Fabrizio Branca <branca@punkt.de>
     * @since 	2008-01-14
     */
    public function get_tableHeadRow() {
        return $this->tableHeadRow;
    }
    
    
    
	/**
     * Get property value
     *
     * @param 	void
     * @return	tx_ptgsaadmin_row	property value
     * @author	Fabrizio Branca <branca@punkt.de>
     * @since 	2008-01-14
     */
    public function get_passParameter() {
        return $this->passParameter;
    }
    
    
    
    /**
     * Get row and cell data in an array
     *
     * @return 	array row/cell data
     * @author	Fabrizio Branca <branca@punkt.de>
     * @since	2008-01-14
     */
    public function getData() {
        $rowArray = array();
        
        /* @var $row tx_ptgsaadmin_row */
        foreach ($this as $row) {
            $rowArray[] = array('content' => $row->getData(),
                                'class' => $row->get_class());
        }
        return $rowArray;
    }
    
    
    
    /**
     * Renders the list
     *
     * @param 	string	path to the smarty template
     * @return 	string	HTML Output
     * @author  Fabrizio Branca <branca@punkt.de>
     * @since	2008-01-14
     */
    public function toHTML($smartyTemplate) {
        $rowArray = $this->getData();
        
        $smarty = tx_smarty::smarty(array('compile_dir' => PATH_site.'typo3temp/smarty/templates_c'));  
        
        $smarty->assign('title', $this->get_title());
        $smarty->assign('tableHeadRow', $this->get_tableHeadRow()->getData());
        $smarty->assign('pageUpBtn', $this->get_pageUpBtn());
        $smarty->assign('pageDownBtn', $this->get_pageDownBtn());
        $smarty->assign('rows', $this->getData());
        $smarty->assign('passParameter', $this->get_passParameter());
            
        return $content = $smarty->fetch('file:'.t3lib_div::getFileAbsFileName($smartyTemplate));
    }
    
    
    
    /**
     * Adds a row to the list
     *
     * @param 	tx_ptgsaadmin_row 	row
     * @return	void
     * @author	Fabrizio Branca <branca@punkt.de>
     * @since 	2008-01-14
     */
    public function addRow(tx_ptgsaadmin_row $row){
        parent::addItem($row);
    }
    
    
    
    /**
     * Adds a row to the list
     *
     * @param 	tx_ptgsaadmin_row 	row
     * @return	void
     * @author	Fabrizio Branca <branca@punkt.de>
     * @since 	2008-01-14
     */
    public function addItem(tx_ptgsaadmin_row $row){
        parent::addItem($row);
    }
        
    
    
} // end class



/*******************************************************************************
 *   TYPO3 XCLASS INCLUSION (for class extension/overriding)
 ******************************************************************************/
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsaadmin/res/list/class.tx_ptgsaadmin_list.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsaadmin/res/list/class.tx_ptgsaadmin_list.php']);
}

?>