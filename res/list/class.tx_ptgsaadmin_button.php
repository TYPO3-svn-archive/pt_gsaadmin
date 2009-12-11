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
 * List class
 *
 * $Id: class.tx_ptgsaadmin_button.php,v 1.1 2008/01/14 14:23:35 ry44 Exp $
 *
 * @author  Fabrizio Branca <branca@punkt.de>
 * @since   2008-01-14
 */ 


/**
 * Inclusion of TYPO3 resources
 */



/**
 * Button class
 * 
 * @author  Fabrizio Branca <branca@punkt.de>
 * @since   2008-01-14
 * @package     TYPO3
 */
class tx_ptgsaadmin_button {
    
    // TODO: write phpdoc comments

    /**
     * @var 	string	url
     */
    protected $url = '';
    
    /**
     * @var 	string	image
     */
    protected $image = '';
    
    /**
     * @var 	string	title
     */
    protected $title = '';
    
    /**
     * @var 	string	alt (HTML "alt"-Tag)
     */
    protected $alt = '';
    
    /**
     * @var 	string	onclick
     */
    protected $onclick = '';
    
    /**
     * @var 	string	label
     */
    protected $label = '';
    
    
    
    /**
     * Constructor
     *
     * @param 	string	(optional) url
     * @param 	string	(optional) image
     * @param 	string	(optional) title
     * @param 	string	(optional) alt
     * @param 	string	(optional) onclick
     * @param 	string	(optional) t3src
     * @param 	string	(optional) backpath
     * @param 	string	(optional) label
     * @return 	void
     * @author	Fabrizio Branca <branca@punkt.de>
     * @since	2007-01-15
     */
    public function __construct($url='', $image='', $title='', $alt='', $onclick='', $t3src='', $backpath='', $label='') {
        if (!empty($url)) $this->set_url($url);
        if (!empty($image)) $this->set_image($image);
        if (!empty($title)) $this->set_title($title);
        if (!empty($alt)) $this->set_alt($alt);
        if (!empty($onclick)) $this->set_onclick($onclick);
        if (!empty($t3src)) $this->setT3Image($t3src, $backpath);
        if (!empty($label)) $this->set_label($label);
    }
    
    
    
    /**
     * Set property value
     *
     * @param 	string 		property value
     * @return 	void
     * @author	Fabrizio Branca	<branca@punkt.de>
     * @since	2008-01-14
     */
    public function set_label($label) {
        $this->label = $label;
    }
    
    
    
    /**
     * Set property value
     *
     * @param 	string 		property value
     * @return 	void
     * @author	Fabrizio Branca	<branca@punkt.de>
     * @since	2008-01-14
     */
    public function set_onclick($onclick) {
        $this->onclick = $onclick;
    }
    
    
    
    /**
     * Set property value
     *
     * @param 	string 		property value
     * @return 	void
     * @author	Fabrizio Branca	<branca@punkt.de>
     * @since	2008-01-14
     */
    public function set_url($url) {
        $this->url = $url;
    }
    
    
    
    /**
     * Set property value
     *
     * @param 	string 		property value
     * @return 	void
     * @author	Fabrizio Branca	<branca@punkt.de>
     * @since	2008-01-14
     */
    public function set_image($image) {
        // ugly, but needed to stay compatible with t3lib_iconWorks::skinImg()
        $this->image = ' src="'.$image.'"';
    }
    
    
    
    /**
     * Set property value
     *
     * @param 	string 		property value
     * @return 	void
     * @author	Fabrizio Branca	<branca@punkt.de>
     * @since	2008-01-14
     */
    public function set_title($title) {
        $this->title = $title;
    }
    
    
    
    /**
     * Set property value
     *
     * @param 	string 		property value
     * @return 	void
     * @author	Fabrizio Branca	<branca@punkt.de>
     * @since	2008-01-14
     */
    public function set_alt($alt) {
        $this->alt = $alt;
    }
    
    
    
    /**
     * Set image using the iconWorks-Api (for icons from the backend skin)
     *
     * @param 	string 	"icon-src" 
     * @param 	string	(optional) backpath, if empty $GLOBALS['BACK_PATH'] will be taken
     * @return 	void
     * @author	Fabrizio Branca	<branca@punkt.de>
     * @since	2008-01-14
     */
    public function setT3Image($src, $backpath = '') {
        if (empty($backpath)) {
            $backpath = $GLOBALS['BACK_PATH'];
        }
        $this->image = t3lib_iconWorks::skinImg($backpath, $src);
    }
    
    
    
    /**
     * Get property value
     *
     * @param 	void
     * @return 	string 		property value
     * @author	Fabrizio Branca	<branca@punkt.de>
     * @since	2008-01-14
     */
    public function get_url() {
        return $this->url;
    }
    
    
	/**
     * Get property value
     *
     * @param 	void
     * @return 	string 		property value
     * @author	Fabrizio Branca	<branca@punkt.de>
     * @since	2008-01-14
     */
    public function get_image() {
        return $this->image;
    }
    
    
    
	/**
     * Get property value
     *
     * @param 	void
     * @return 	string 		property value
     * @author	Fabrizio Branca	<branca@punkt.de>
     * @since	2008-01-14
     */
    public function get_title() {
        return $this->title;
    }
    
    
    
    /**
     * Get property value
     *
     * @param 	void
     * @return 	string 		property value
     * @author	Fabrizio Branca	<branca@punkt.de>
     * @since	2008-01-14
     */
    public function get_alt() {
        return $this->alt;
    }
    
    
    
    /**
     * Get property value
     *
     * @param 	void
     * @return 	string 		property value
     * @author	Fabrizio Branca	<branca@punkt.de>
     * @since	2008-01-14
     */
    public function get_onclick() {
        return $this->onclick;
    }
    
    
    
    /**
     * Get property value
     *
     * @param 	void
     * @return 	string 		property value
     * @author	Fabrizio Branca	<branca@punkt.de>
     * @since	2008-01-14
     */
    public function get_label() {
        return $this->label;
    }
    
    
    
    /**
     * Render to html
     *
     * @param	void
     * @return 	string	HTML Output
     * @author	Fabrizio Branca <branca@punkt.de>
     * @since	2008-01-14
     */
    public function toHTML() {
        return '<a href="'.$this->url.'" '.($this->onclick ? 'onclick="'.$this->onclick.'"' : '').'><img '.$this->image.' '.($this->alt ? 'alt="'.$this->alt.'"' : '').' '.($this->title ? 'title="'.$this->title.'"' : '').' />'.$this->label.'</a>';
    }
    
} // end class



/*******************************************************************************
 *   TYPO3 XCLASS INCLUSION (for class extension/overriding)
 ******************************************************************************/
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsaadmin/res/list/class.tx_ptgsaadmin_button.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsaadmin/res/list/class.tx_ptgsaadmin_button.php']);
}

?>