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
 * $Id: class.tx_ptgsaadmin_row.php,v 1.2 2008/01/18 12:36:57 ry44 Exp $
 *
 * @author  Fabrizio Branca <branca@punkt.de>
 * @since   2008-01-14
 */ 


/**
 * Inclusion of TYPO3 resources
 */
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_debug.php'; // debugging class with trace() function
require_once t3lib_extMgm::extPath('pt_tools').'res/abstract/class.tx_pttools_objectCollection.php'; // abstract object collection class

require_once t3lib_extMgm::extPath('pt_gsaadmin').'res/list/class.tx_ptgsaadmin_cell.php';




/**
 * Row class (collection of tx_ptgsaadmin_cell objects)
 * 
 * @author  Fabrizio Branca <branca@punkt.de>
 * @since   2008-01-14
 * @package     TYPO3
 */
class tx_ptgsaadmin_row extends tx_pttools_objectCollection implements IteratorAggregate, Countable {
    
    /**
     * @var string class
     */
    protected $class = '';
    
    
    /**
     * Get property value
     *
     * @param	void
     * @return 	string	property value
     * @author	Fabrizio Branca <branca@punkt.de>
     * @since	2008-01-14
     */
    public function get_class() {
        return $this->class;
    }
    
    
    
    /**
     * Set property value
     * 
     * @param	string	property value
     * @return 	void
     * @author	Fabrizio Branca	<branca@punkt.de>
     * @since 	2008-01-14
     */
    public function set_class($class) {
        $this->class = $class;
    }
    
    
    
    /**
     * Adds a cell to the row
     *
     * @param 	tx_ptgsaadmin_cell 	row
     * @return	void
     * @author	Fabrizio Branca <branca@punkt.de>
     * @since 	2008-01-14
     */
    public function addCell(tx_ptgsaadmin_cell $cell){
        parent::addItem($cell);
    }
    
	
	
	/**
     * Adds a cell to the row
     *
     * @param 	tx_ptgsaadmin_cell 	row
     * @return	void
     * @author	Fabrizio Branca <branca@punkt.de>
     * @since 	2008-01-14
     */
    public function addItem(tx_ptgsaadmin_cell $cell){
        parent::addItem($cell);
    }
    
    
    
    /**
     * Returns an arry with cell data
     *
     * @param 	void
     * @return 	array	cell data
     * @author	Fabrizio Branca <branca@punkt.de>
     * @since 	2008-01-14
     */
    public function getData() {
        /*  @var $cell tx_ptgsaadmin_cell */
        foreach ($this as $cell) {
            $cellArray[] = array('content' => $cell->get_content(),
                                 'class' => $cell->get_class(),
                                 'colspan' => $cell->get_colspan());
        }
        return $cellArray;
    }
    
} // end class



/*******************************************************************************
 *   TYPO3 XCLASS INCLUSION (for class extension/overriding)
 ******************************************************************************/
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsaadmin/res/list/class.tx_ptgsaadmin_row.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsaadmin/res/list/class.tx_ptgsaadmin_row.php']);
}

?>