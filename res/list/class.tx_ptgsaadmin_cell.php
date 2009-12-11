<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2007 Rainer Kuhn (kuhn@punkt.de)
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
 * Cell class
 *
 * $Id: class.tx_ptgsaadmin_cell.php,v 1.2 2008/01/18 12:36:57 ry44 Exp $
 *
 * @author  Fabrizio Branca <branca@punkt.de>
 * @since   2008-01-14
 */ 


/**
 * Inclusion of TYPO3 resources
 */



/**
 * Cell class
 * 
 * @author  Fabrizio Branca <branca@punkt.de>
 * @since   2008-01-14
 * @package     TYPO3
 */
class tx_ptgsaadmin_cell {
    
    /**
     * @var 	string	content
     */
    protected $content = '';
    
    /**
     * @var 	string	class
     */
    protected $class = '';
    
    /**
     * @var 	string	wrap
     */
    protected $wrap = '';
    
    /**
     * @var 	int		colspan
     */
    protected $colspan = 1;
    
    
    
	/**
     * Constructor
     *
     * @param	string	(optional) content
     * @return 	void
     * @author	Fabrizio Branca <branca@punkt.de>
     * @since	2008-01-14
     */
    public function __construct($content = '', $colspan = 1) {
        $this->set_content($content);
        $this->set_colspan($colspan);
    }
    
    
    
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
     * Get property value
     *
     * @param	void
     * @return 	string	property value
     * @author	Fabrizio Branca <branca@punkt.de>
     * @since	2008-01-14
     */
    public function get_wrap() {
        return $this->wrap;
    }
    
    
    
    /**
     * Get property value
     *
     * @param	void
     * @return 	string	property value
     * @author	Fabrizio Branca <branca@punkt.de>
     * @since	2008-01-14
     */
    public function get_content() {
        if (!empty($this->wrap)) {
            $this->content = str_replace('|', $this->content, $this->wrap);
        }
        return $this->content;
    }    


	
	/**
     * Get property value
     *
     * @param	void
     * @return 	string	property value
     * @author	Fabrizio Branca <branca@punkt.de>
     * @since	2008-01-14
     */
    public function get_colspan() {
        return $this->colspan;
    }
    
    
    
	/**
     * Set property value
     *
     * @param 	tx_ptgsaadmin_row	property value
     * @return	void
     * @author	Fabrizio Branca <branca@punkt.de>
     * @since 	2008-01-14
     */
    public function set_content($content) {
        $this->content = $content;
    }
    
    
    
    /**
     * Set property value
     *
     * @param 	tx_ptgsaadmin_row	property value
     * @return	void
     * @author	Fabrizio Branca <branca@punkt.de>
     * @since 	2008-01-14
     */
    public function set_class($class) {
        $this->class = $class;
    }
    
    
    
    /**
     * Set property value
     *
     * @param 	tx_ptgsaadmin_row	property value
     * @return	void
     * @author	Fabrizio Branca <branca@punkt.de>
     * @since 	2008-01-14
     */
    public function set_wrap($wrap) {
        $this->wrap = $wrap;
    }
    
    
    
    /**
     * Set property value
     *
     * @param 	tx_ptgsaadmin_row	property value
     * @return	void
     * @author	Fabrizio Branca <branca@punkt.de>
     * @since 	2008-01-14
     */
    public function set_colspan($colspan) {
        $this->colspan = $colspan;
    }
        
    
} // end class



/*******************************************************************************
 *   TYPO3 XCLASS INCLUSION (for class extension/overriding)
 ******************************************************************************/
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsaadmin/res/list/class.tx_ptgsaadmin_cell.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsaadmin/res/list/class.tx_ptgsaadmin_cell.php']);
}

?>