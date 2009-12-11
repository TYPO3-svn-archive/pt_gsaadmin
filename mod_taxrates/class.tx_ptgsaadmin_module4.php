<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Rainer Kuhn <kuhn@punkt.de>
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
 * Module 'Tax Rates' of the 'pt_gsaadmin' extension.
 *
 * $Id: class.tx_ptgsaadmin_module4.php,v 1.1 2008/04/08 11:24:55 ry37 Exp $
 *
 * @author  Rainer Kuhn <kuhn@punkt.de>
 * @since   2008-04-02
 */ 



/**
 * Inclusion of external PEAR resources: this requires PEAR to be installed on your server (see http://pear.php.net/) and the path to PEAR being part of your include path!
 */
require_once 'HTML/QuickForm.php';  // PEAR HTML_QuickForm: methods for creating, validating, processing HTML forms (see http://pear.php.net/manual/en/package.html.html-quickform.php). This requires the PEAR module to be installed on your server and the path to PEAR being part of your include path.

/**
 * Inclusion of GSA resources
 */
require_once t3lib_extMgm::extPath('pt_gsaadmin').'res/class.tx_ptgsaadmin_submodules.php'; //  Abstract submodules parent class for the 'pt_gsaadmin' extension
require_once t3lib_extMgm::extPath('pt_gsaadmin').'res/class.tx_ptgsaadmin_taxRate.php';  // GSA tax rate class
require_once t3lib_extMgm::extPath('pt_gsaadmin').'res/class.tx_ptgsaadmin_taxRateCollection.php';  // GSA tax rate collection class
require_once t3lib_extMgm::extPath('pt_gsaadmin').'res/class.tx_ptgsaadmin_taxRateAccessor.php';  // GSA extended tax rate accessor class

/**
 * Inclusion of external resources
 */
require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_exception.php'; // general exception class
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_debug.php'; // debugging class with trace() function
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_div.php'; // general static library class
require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_formReloadHandler.php'; // web form reload handler class


/**
 * Debugging config for development
 */
#$trace     = 1; // (int) trace options @see tx_pttools_debug::trace() [for local temporary debugging use only, please COMMENT OUT this line if finished with debugging!]
#$errStrict = 1; // (bool) set strict error reporting level for development (requires $trace to be set to 1)  [for local temporary debugging use only, please COMMENT OUT this line if finished with debugging!]



/**
 * Class for backend sub module 'Tax Rates' of the 'pt_gsaadmin' extension.
 *
 * @author      Rainer Kuhn <kuhn@punkt.de>
 * @since       2008-04-02
 * @package     TYPO3
 * @subpackage  tx_ptgsaadmin
 */
class tx_ptgsaadmin_module4 extends tx_ptgsaadmin_submodules {
    
    
    
    /***************************************************************************
     *   INHERITED METHODS from tx_ptgsaadmin_submodules
     **************************************************************************/

    /** 
     * Adds items to the ->MOD_MENU array (used for the function menu selector)
     *
     * @param       void
     * @return      void
     * @global      $GLOBALS['LANG']
     * @author      Rainer Kuhn <kuhn@punkt.de>
     * @since       2008-04-04
     */
    public function menuConfig() {
        
        $this->MOD_MENU = array(
            'jumpMenuFunction' => array(
                '1' => $this->ll('jumpMenuFunction1'),
                '2' => $this->ll('jumpMenuFunction2'),
            )
        );
        
        parent::menuConfig(true);
        
    }

    /**
     * "Controller": Calls the appropriate action and returns the module's HTML content
     *
     * @param       void
     * @return      string      the module's HTML content
     * @global      $GLOBALS['LANG']
     * @author      Rainer Kuhn <kuhn@punkt.de>
     * @since       2008-04-04
     */
    public function moduleContent() {
        
        $moduleContent = '';
        
        // execute button related actions
        if (t3lib_div::GPvar('btnNewTax')) {
            $content = $this->exec_newTaxAction();
            $moduleContent .= $this->doc->section($this->ll('actionHeader2'), $content, 0, 1);
            
        } elseif (t3lib_div::GPvar('btnEditTax')) {
            $content = $this->exec_editTaxAction();
            $moduleContent .= $this->doc->section($this->ll('actionHeader1'), $content, 0, 1);
            
        } elseif (t3lib_div::GPvar('btnDeleteTax')) {
            $content = $this->exec_deleteTaxAction();
            $moduleContent .= $this->doc->section($this->ll('actionHeader1'), $content, 0, 1);
        
        // execute jump menu related actions
        } elseif (isset($this->MOD_SETTINGS['jumpMenuFunction'])) {
            switch((string)$this->MOD_SETTINGS['jumpMenuFunction']) {
                case '1':
                    $content = $this->exec_editTaxAction();
                    $moduleContent .= $this->doc->section($this->ll('actionHeader1'), $content, 0, 1);
                    break;
                case '2':
                    $content = $this->exec_newTaxAction();
                    $moduleContent .= $this->doc->section($this->ll('actionHeader2'), $content, 0, 1);
                    break;
                default:
                    break;
            }
            
        }
        
        return $moduleContent;
        
    }
    
    
    
    /***************************************************************************
     *   BUSINESS LOGIC METHODS
     **************************************************************************/
    
    /**
     * Processes the "Edit tax rate" action and returns the resulting HTML content
     *
     * @param       void
     * @return      string      resulting HTML content to display for "Edit tax rate"
     * @author      Rainer Kuhn <kuhn@punkt.de>
     * @since       2008-04-04
     */
    protected function exec_editTaxAction() {
        
        $content ='';
        $selectorForm = $this->returnTaxSelectionForm(); // returns an already built HTML_QuickForm object
        
        // if selector form is validated: process the submitted data
        if (!t3lib_div::GPvar('btnEditTax') && $selectorForm->validate() == true) {
                
            $selectorForm->freeze();
            $taxForm = $this->returnTaxRateForm();
            
            // if tax form is validated: process the submitted data
            if ($taxForm->validate() == true) {
                $taxForm->freeze();
                $content .= $this->processEditTaxData();
                
           // if tax form is unvalidated: get selected tax rate's data and display the prefilled tax form
            } else {
                $content .= $this->processTaxSelection();
            }
            
       // if selector form is unvalidated: set default and display form
        } else {
            $content .= $selectorForm->toHtml();  // get form content to display
        }
        
        return $content;
        
    }
    
    /**
     * Processes the "New tax rate" action and returns the resulting HTML content
     *
     * @param       void
     * @return      string      resulting HTML content to display for "New tax rate"
     * @author      Rainer Kuhn <kuhn@punkt.de>
     * @since       2008-04-04
     */
    protected function exec_newTaxAction() {
        
        $content = '';
        $taxForm = $this->returnTaxRateForm(); // returns an already built HTML_QuickForm object
        
        // if not coming from "New tax rate" button and tax form is validated: process the submitted data
        if (!t3lib_div::GPvar('btnNewTax') && $taxForm->validate() == true) {
            $taxForm->freeze();
            $content .= $this->processNewTaxData(); 
            
        // if tax form is unvalidated: set default and display form
        } else { 
            $content .= $taxForm->toHtml();  // get form content to display
        }
        
        return $content;
        
    }
    
    /**
     * Processes the "Delete tax rate" action and returns the resulting HTML content
     *
     * @param       void
     * @return      string      resulting HTML content to display for "Delete tax rate"
     * @author      Rainer Kuhn <kuhn@punkt.de>
     * @since       2008-04-04
     */
    protected function exec_deleteTaxAction() {
        
        $content = '';
        
        if ($this->formReloadHandler->checkToken(t3lib_div::GPvar('__formToken')) == true) {
            $taxRateObj = $this->createTaxrateObjFromFormData(t3lib_div::GPvar('recordUid'));
            tx_ptgsaadmin_taxRateAccessor::getInstance()->deleteTaxRate($taxRateObj); // delete tax rate (if form has not been submitted repeatingly)
        }
        
        // build "confirmation form": display confirmation text plus repeat action button
        $confirmationForm = new HTML_QuickForm('confDeleteForm', 'post');
        $confirmationForm->addElement('header', 'confDelete', $this->ll('taxFormAction_confDelete'));
        $confirmationForm->addElement('submit', 'btnEditTax', $this->ll('taxForm_btnSubmit_back'), array('class'=>$this->extPrefix.'_buttonSpecial'));
        $content .= $confirmationForm->toHtml();
        
        return $content;
        
    }
    
    /**
     * Processes the "Edit tax rate" data and returns the resulting HTML content (a prefilled tax form containing the tax rate data to edit)
     *
     * @param       void
     * @return      string      HTML content of a prefilled tax form containing the tax rate data to edit
     * @author      Rainer Kuhn <kuhn@punkt.de>
     * @since       2008-04-04
     */
    protected function processTaxSelection() {
        
        $content = '';
        
        // create form defaults from tax rate object
        $taxRateDataArr = array();
        $taxRateObj = new tx_ptgsaadmin_taxRate(t3lib_div::GPvar('taxSelector')); // create selected tax rate object
        
        // set tax rate basic data fields
        $taxRateDataArr['recordUid']    = $taxRateObj->get_recordUid();
        $taxRateDataArr['taxCode'] = $taxRateObj->get_taxCode();
        $taxRateDataArr['taxRate'] = $taxRateObj->get_taxRate();
        list($year, $month, $day) = split('-', $taxRateObj->get_startDate(), 3);
        $taxRateDataArr['startDate'] = array('d'=>$day, 'm'=>$month, 'Y'=>$year);
        $taxRateDataArr['taxDescription'] = $taxRateObj->get_taxDescription();
        
        // render cost form 
        trace($taxRateDataArr);
        $taxForm = $this->returnTaxRateForm($taxRateDataArr);
        $content .= $taxForm->toHtml();  // get form content to display
        
        return $content;
        
    }
    
    /**
     * Processes the "New tax rate" form data
     *
     * @param       void
     * @return      string      HTML content to confirm the action
     * @author      Rainer Kuhn <kuhn@punkt.de>
     * @since       2008-04-04
     */
    protected function processNewTaxData() {
            
        // process data (if form has not been submitted repeatingly)
        if ($this->formReloadHandler->checkToken(t3lib_div::GPvar('__formToken')) == true) {
            $taxRateObj = $this->createTaxrateObjFromFormData();
            tx_ptgsaadmin_taxRateAccessor::getInstance()->insertTaxRate($taxRateObj);
        }
        
        // build "confirmation form": display confirmation text plus repeat action button
        $confirmationForm = new HTML_QuickForm('confInsertForm', 'post');
        $confirmationForm->addElement('header', 'confInsert', $this->ll('taxFormAction_confInsert'));
        $confirmationForm->addElement('submit', 'btnNewTax', $this->ll('jumpMenuFunction2'), array('class'=>$this->extPrefix.'_buttonSpecial'));
        $content .= $confirmationForm->toHtml();
        
        return $content;
        
    }
    
    /** TODO: adapt to mod4
     * Processes the "Edit tax rate" form data
     *
     * @param       void
     * @return      string      HTML content to confirm the action
     * @author      Rainer Kuhn <kuhn@punkt.de>
     * @since       2008-04-04
     */
    protected function processEditTaxData() {
            
        // process data (if form has not been submitted already)
        if ($this->formReloadHandler->checkToken(t3lib_div::GPvar('__formToken')) == true) {
            $taxRateObj = $this->createTaxrateObjFromFormData(t3lib_div::GPvar('recordUid'));
            tx_ptgsaadmin_taxRateAccessor::getInstance()->updateTaxRate($taxRateObj);
        }
        
        // build "confirmation form": display confirmation text plus repeat action button
        $confirmationForm = new HTML_QuickForm('confUpdateForm', 'post');
        $confirmationForm->addElement('header', 'confUpdate', $this->ll('taxFormAction_confUpdate'));
        $confirmationForm->addElement('submit', 'btnEditTax', $this->ll('taxForm_btnSubmit_back'), array('class'=>$this->extPrefix.'_buttonSpecial'));
        $content .= $confirmationForm->toHtml();
        
        return $content;
        
    }
    
    /**
     * Creates and returns an tax rate object from the tax form's data
     * @param       integer     (optional) 0 for empty tax rate built from "new tax rate" form (=default) or GSA database UID of the tax rate to update from "edit tax rate" form
     * @return      tx_ptgsaadmin_taxRate      tax rate object built from the tax form's data
     * @author      Rainer Kuhn <kuhn@punkt.de>
     * @since       2008-04-04
     */
    protected function createTaxrateObjFromFormData($editTaxRateUid=0) {
        
        $taxRateObj = new tx_ptgsaadmin_taxRate($editTaxRateUid); // create empty tax rate object or existing tax rate from database
        
        // set the tax rate object's properties
        $startDateArr = t3lib_div::GPvar('startDate', 1);
        $taxRateObj->set_recordUid($editTaxRateUid);
        $taxRateObj->set_taxCode(t3lib_div::GPvar('taxCode'));
        $taxRateObj->set_taxRate(t3lib_div::GPvar('taxRate'));        
        $taxRateObj->set_startDate(sprintf('%04s-%02s-%02s', $startDateArr['Y'], $startDateArr['m'], $startDateArr['d']));
        $taxRateObj->set_taxDescription(t3lib_div::GPvar('taxDescription'));
        
        trace($taxRateObj);
        return $taxRateObj;
        
    }
    
    /**
     * Creates and returns an already built tax rate selection form
     *
     * @param       void
     * @return      HTML_QuickForm  object of type HTML_QuickForm: an already built tax rate selector form
     * @author      Rainer Kuhn <kuhn@punkt.de>
     * @since       2008-04-04
     */
    protected function returnTaxSelectionForm() {
        
        $taxSelectorArr = array();
        $form = new HTML_QuickForm('taxSelector', 'post');
        
        // prepare quickform for multilang mode
        $form->setJsWarnings($this->ll('qf_jsWarningPref'), $this->ll('qf_jsWarningPost'));
        $form->setRequiredNote('* '.$this->ll('qf_requiredNote'));
        
        // build selectorbox array for tax rates 
        $taxRateCollection = new tx_ptgsaadmin_taxRateCollection(true);
        foreach ($taxRateCollection as $taxRateObj) {
            $taxSelectorArr[$taxRateObj->get_recordUid()] = '['.$taxRateObj->get_taxCode().'] '.$taxRateObj->get_taxDescription().
                                                            ' ('.$this->ll('selForm_optiontextSince').' '.$taxRateObj->get_startDate().')';
        }
        
        // build form
        $form->addElement('header', 'artHeader1', $this->ll('selForm_header1'));
        $form->addElement('select', 'taxSelector', $this->ll('selForm_taxSelector'), $taxSelectorArr);
        $form->addElement('submit', 'btnEdit', $this->ll('selForm_btnEdit'));
        
        #trace($form);
        return $form;
        
    }
    
    /**
     * Creates and returns an already built tax rate form
     *
     * @param       array   (optional) array (key=elementName, value=elementDefaultValue) containing the defaults for the form elements; if not set, an empty form is created
     * @return      HTML_QuickForm  object of type HTML_QuickForm: an already built tax rate form
     * @author      Rainer Kuhn <kuhn@punkt.de>
     * @since       2008-04-04
     */
    protected function returnTaxRateForm($defaultsDataArr=array()) {
        
        $buttonsArr = array();
        $form = new HTML_QuickForm('taxForm', 'post');
        $formMode = (empty($defaultsDataArr) ? 'new' : 'edit');
        
        // prepare quickform for multilang mode and reload prevention
        $form->setJsWarnings($this->ll('qf_jsWarningPref'), $this->ll('qf_jsWarningPost'));
        $form->setRequiredNote('* '.$this->ll('qf_requiredNote'));
        $form->addElement('hidden', '__formToken', $this->formReloadHandler->createToken());
        
        // set form defaults
        if (!is_array($defaultsDataArr['startDate']) || implode('', $defaultsDataArr['startDate']) == '') {
            $defaultsDataArr['startDate'] = array('d'=>'01', 'm'=>'01', 'Y'=>strval(date('Y')+1));
        }
        $form->setDefaults($defaultsDataArr);
       
        // set additional elements depending on form mode
        if ($formMode == 'edit') {
            $form->addElement('hidden', 'recordUid');
            $form->addElement('submit', 'btnDeleteTax', $this->ll('taxForm_btnSubmit_delete'), array('onclick'=>"return confirm('".$this->ll('taxForm_btnSubmit_delete_confirm')."')"));
        } 
        
        // prepare button group depending on form mode
        if ($formMode == 'edit') {
            $buttonsArr[] = HTML_QuickForm::createElement('submit', 'btnSave', $this->ll('taxForm_btnSubmit_edit'));
            $buttonsArr[] = HTML_QuickForm::createElement('reset', 'btnClear', $this->ll('taxForm_btnClear'));
        } else {
            $buttonsArr[] = HTML_QuickForm::createElement('submit', 'btnNew', $this->ll('taxForm_btnSubmit_new'));
        }
        
        // build form: create fields
        $form->addElement('header', 'header1', 
                           sprintf($this->ll('taxForm_header1'), isset($defaultsDataArr['recordUid']) ? $defaultsDataArr['recordUid'] : $this->ll('taxForm_header1_new')));
        $form->addElement('text', 'taxCode', $this->ll('taxForm_taxCode'), array('maxlength'=>'2', 'class'=>$this->extPrefix.'_inputTextPrice'));
        $form->addElement('text', 'taxRate', $this->ll('taxForm_taxRate'), array('maxlength'=>'7', 'class'=>$this->extPrefix.'_inputTextPrice'));
        $form->addElement('date', 'startDate', $this->ll('taxForm_startDate'), array('language'=>'en', 'format'=>'dmY', 'minYear'=>1980, 'maxYear'=>strval(date('Y')+2)));
        $form->addElement('header', 'header2', $this->ll('taxForm_header2'));
        $form->addElement('text', 'taxDescription', $this->ll('taxForm_taxDescription'), array('maxlength'=>'30', 'class'=>$this->extPrefix.'_inputTextShort'));
        
        // build form: add validation rules
        $form->addRule('taxCode', sprintf($this->ll('qf_ruleRequired'), $this->ll('taxForm_taxCode')), 'required', '', 'client');
        $form->addRule('taxCode', sprintf($this->ll('qf_ruleMaxlength'), $this->ll('taxForm_taxCode'), '2'), 'maxlength', 2, 'client');
        $form->addRule('taxRate', sprintf($this->ll('qf_ruleRequired'), $this->ll('taxForm_taxRate')), 'required', '', 'client');
        $form->addRule('taxRate', sprintf($this->ll('qf_ruleNumeric'), $this->ll('taxForm_taxRate')), 'numeric', '', 'server');
        $form->addRule('taxRate', sprintf($this->ll('qf_ruleMaxlength'), $this->ll('taxForm_taxRate'), '7'), 'maxlength', 7, 'client');
        
        // build form: button row
        $form->addElement('header', 'seperator', '');
        $form->addGroup($buttonsArr, 'buttonGroup', NULL, ' &nbsp;');
        
        // apply filters
        $form->applyFilter('__ALL__', 'trim');
        
        #trace($form);
        return $form;
        
    }
    
    
    
} // end class



/*******************************************************************************
 *   TYPO3 XCLASS INCLUSION (for class extension/overriding)
 ******************************************************************************/
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsaadmin/mod_taxrates/class.tx_ptgsaadmin_module4.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsaadmin/mod_taxrates/class.tx_ptgsaadmin_module4.php']);
}

?>