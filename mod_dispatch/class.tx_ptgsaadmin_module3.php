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
 * Module 'Dispatch Cost' of the 'pt_gsaadmin' extension.
 *
 * $Id: class.tx_ptgsaadmin_module3.php,v 1.12 2008/06/23 15:35:55 ry37 Exp $
 *
 * @author  Rainer Kuhn <kuhn@punkt.de>
 * @since   2007-10-29
 */ 



/**
 * Inclusion of external PEAR resources: this requires PEAR to be installed on your server (see http://pear.php.net/) and the path to PEAR being part of your include path!
 */
error_reporting(E_ALL && !E_DEPRECATED);
require_once 'HTML/QuickForm.php';  // PEAR HTML_QuickForm: methods for creating, validating, processing HTML forms (see http://pear.php.net/manual/en/package.html.html-quickform.php). This requires the PEAR module to be installed on your server and the path to PEAR being part of your include path.

/**
 * Inclusion of TYPO3 resources
 */
require_once t3lib_extMgm::extPath('pt_gsaadmin').'res/class.tx_ptgsaadmin_submodules.php'; //  Abstract submodules parent class for the 'pt_gsaadmin' extension
require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_exception.php'; // general exception class
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_debug.php'; // debugging class with trace() function
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_div.php'; // general static library class
require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_formReloadHandler.php'; // web form reload handler class
require_once t3lib_extMgm::extPath('pt_gsashop').'res/class.tx_ptgsashop_dispatchCost.php';  // GSA shop dispatch cost class
require_once t3lib_extMgm::extPath('pt_gsashop').'res/class.tx_ptgsashop_dispatchCostAccessor.php';  // GSA shop dispatch cost accessor class
require_once t3lib_extMgm::extPath('pt_gsashop').'res/class.tx_ptgsashop_dispatchCostCollection.php';  // GSA shop dispatch cost collection class

/**
 * Debugging config for development
 */
#$trace     = 1; // (int) trace options @see tx_pttools_debug::trace() [for local temporary debugging use only, please COMMENT OUT this line if finished with debugging!]
#$errStrict = 1; // (bool) set strict error reporting level for development (requires $trace to be set to 1)  [for local temporary debugging use only, please COMMENT OUT this line if finished with debugging!]



/**
 * Class for backend sub module 'Dispatch Cost' of the 'pt_gsaadmin' extension.
 *
 * @author      Rainer Kuhn <kuhn@punkt.de>
 * @since       2007-10-29
 * @package     TYPO3
 * @subpackage  tx_ptgsaadmin
 */
class tx_ptgsaadmin_module3 extends tx_ptgsaadmin_submodules {
    
    
    
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
     * @since       2007-08-24
     */
    public function menuConfig() {
        
        $this->MOD_MENU = array(
            'jumpMenuFunction' => array(
                '1' => $this->ll('jumpMenuFunction1'),
                '2' => $this->ll('jumpMenuFunction2'),
                #'3' => $this->ll('jumpMenuFunction3'),  // TODO: not implemented yet
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
     * @since       2007-10-29
     */
    public function moduleContent() {
        
        $moduleContent = '';
        
        // execute button related actions
        if (t3lib_div::GPvar('btnNewCost')) {
            $content = $this->exec_newCostAction();
            $moduleContent .= $this->doc->section($this->ll('actionHeader2'), $content, 0, 1);
            
        } elseif (t3lib_div::GPvar('btnEditCost')) {
            $content = $this->exec_editCostAction();
            $moduleContent .= $this->doc->section($this->ll('actionHeader1'), $content, 0, 1);
            
        } elseif (t3lib_div::GPvar('btnDeleteCost')) {
            $content = $this->exec_deleteCostAction();
            $moduleContent .= $this->doc->section($this->ll('actionHeader1'), $content, 0, 1);
        
        // execute jump menu related actions
        } elseif (isset($this->MOD_SETTINGS['jumpMenuFunction'])) {
            switch((string)$this->MOD_SETTINGS['jumpMenuFunction']) {
                case '1':
                    $content = $this->exec_editCostAction();
                    $moduleContent .= $this->doc->section($this->ll('actionHeader1'), $content, 0, 1);
                    break;
                case '2':
                    $content = $this->exec_newCostAction();
                    $moduleContent .= $this->doc->section($this->ll('actionHeader2'), $content, 0, 1);
                    break;
//                # TODO: not implemented yet
//                case '3':
//                    $content = $this->exec_labelCostComponentsAction();
//                    $moduleContent .= $this->doc->section($this->ll('actionHeader3'), $content, 0, 1);
//                    break;
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
     * Processes the "Edit dispatch cost" action and returns the resulting HTML content
     *
     * @param       void
     * @return      string      resulting HTML content to display for "Edit dispatch cost"
     * @author      Rainer Kuhn <kuhn@punkt.de>
     * @since       2007-10-29
     */
    protected function exec_editCostAction() {
        
        $content ='';
        $selectorForm = $this->returnCostSelectionForm(); // returns an already built HTML_QuickForm object
        
        // if selector form is validated: process the submitted data
        if (!t3lib_div::GPvar('btnEditCost') && $selectorForm->validate() == true) {
            
            $selectorForm->freeze();
            $costForm = $this->returnCostForm();
            
            // if cost form is validated: process the submitted data
            if ($costForm->validate() == true) {
                $costForm->freeze();
                $content .= $this->processEditCostData();
                
           // if cost form is unvalidated: get selected dispatch cost's data and display the prefilled cost form
            } else {
                $content .= $this->processCostSelection();
            }
            
       // if selector form is unvalidated: set default and display form
        } else {
            $content .= $selectorForm->toHtml();  // get form content to display
        }
        
        return $content;
        
    }
    
    /**
     * Processes the "New dispatch cost" action and returns the resulting HTML content
     *
     * @param       void
     * @return      string      resulting HTML content to display for "New dispatch cost"
     * @author      Rainer Kuhn <kuhn@punkt.de>
     * @since       2007-10-29
     */
    protected function exec_newCostAction() {
        
        $content = '';
        $costForm = $this->returnCostForm(); // returns an already built HTML_QuickForm object
        
        // if not coming from "New dispatch cost" button and cost form is validated: process the submitted data
        if (!t3lib_div::GPvar('btnNewCost') && $costForm->validate() == true) {
            $costForm->freeze();
            $content .= $this->processNewCostData(); 
            
        // if cost form is unvalidated: set default and display form
        } else { 
            $content .= $costForm->toHtml();  // get form content to display
        }
        
        return $content;
        
    }
    
    /**
     * Processes the "Delete dispatch cost" action and returns the resulting HTML content
     *
     * @param       void
     * @return      string      resulting HTML content to display for "Delete dispatch cost"
     * @author      Rainer Kuhn <kuhn@punkt.de>
     * @since       2007-10-29
     */
    protected function exec_deleteCostAction() {
        
        $content = '';
        
        if ($this->formReloadHandler->checkToken(t3lib_div::GPvar('__formToken')) == true) {
            tx_ptgsashop_dispatchCostAccessor::getInstance()->deleteDispatchCostRecord(t3lib_div::GPvar('gsaCostId')); // delete dispatch cost (if form has not been submitted already)
        }
        
        // build "confirmation form": display confirmation text plus repeat action button
        $confirmationForm = new HTML_QuickForm('confDeleteForm', 'post');
        $confirmationForm->addElement('header', 'confDelete', $this->ll('costFormAction_confDelete'));
        $confirmationForm->addElement('submit', 'btnEditCost', $this->ll('costForm_btnSubmit_back'), array('class'=>$this->extPrefix.'_buttonSpecial'));
        $content .= $confirmationForm->toHtml();
        
        return $content;
        
    }
    
    /**
     * Processes the "Label cost components" action and returns the resulting HTML content
     *
     * @param       void
     * @return      string      resulting HTML content to display for "Label cost components"
     * @author      Rainer Kuhn <kuhn@punkt.de>
     * @since       2007-10-29
     */
    protected function exec_labelCostComponentsAction() {
        
        $content ='';
        
        // TODO: implemenent the "Label cost components" action 
        $content .= '
            <div align="center"><strong>TODO: STILL TO IMPLEMENT!</strong></div>
            <br />
            A function to name/label the 4 dispatch cost type components has to be implemented according to the ERP (incl. multilingualism...).
            These names given here should appear as labels in the new/edit forms, too (instead of "cost component 1" etc.).
            <br /><br />';
        
        return $content;
        
    }
    
    /**
     * Processes the "Edit dispatch cost" data and returns the resulting HTML content (a prefilled cost form containing the dispatch cost data to edit)
     *
     * @param       void
     * @return      string      HTML content of a prefilled cost form containing the dispatch cost data to edit
     * @author      Rainer Kuhn <kuhn@punkt.de>
     * @since       2007-10-29
     */
    protected function processCostSelection() {
        
        $content = '';
        
        // create form defaults from dispatch cost object
        $dispatchcostDataArr = array();
        $dispatchcostObj = new tx_ptgsashop_dispatchCost('', t3lib_div::GPvar('costSelector')); // create selected dispatch cost object
        
        // set dispatch cost basic data fields
        $dispatchcostDataArr['gsaCostId']    = $dispatchcostObj->get_costUid();
        $dispatchcostDataArr['costTypeName'] = $dispatchcostObj->get_costTypeName();
        for ($i=1; $i<=4; $i++) {
            $getterMethodComp = 'get_costComp'.strval($i);
            $getterMethodAllowance = 'get_allowanceComp'.strval($i);
            $dispatchcostDataArr['component'.strval($i).'Group[costComp'.strval($i).']']      = $dispatchcostObj->$getterMethodComp();  // results in e.g. $dispatchcostObj->get_costComp1();
            $dispatchcostDataArr['component'.strval($i).'Group[allowanceComp'.strval($i).']'] = $dispatchcostObj->$getterMethodAllowance();  // results in e.g. $dispatchcostObj->get_allowanceComp1();
        }
        
        // render cost form 
        trace($dispatchcostDataArr);
        $costForm = $this->returnCostForm($dispatchcostDataArr);
        $content .= $costForm->toHtml();  // get form content to display
        
        return $content;
        
    }
    
    /**
     * Processes the "New dispatch cost" form data
     *
     * @param       void
     * @return      string      HTML content to confirm the action
     * @author      Rainer Kuhn <kuhn@punkt.de>
     * @since       2007-10-29
     */
    protected function processNewCostData() {
            
        // process data (if form has not been submitted already)
        if ($this->formReloadHandler->checkToken(t3lib_div::GPvar('__formToken')) == true) {
            $dispatchcostObj = $this->createDispatchcostObjFromFormData();
            tx_ptgsashop_dispatchCostAccessor::getInstance()->insertDispatchCostRecord($dispatchcostObj);
        }
        
        // build "confirmation form": display confirmation text plus repeat action button
        $confirmationForm = new HTML_QuickForm('confInsertForm', 'post');
        $confirmationForm->addElement('header', 'confInsert', $this->ll('costFormAction_confInsert'));
        $confirmationForm->addElement('submit', 'btnNewCost', $this->ll('jumpMenuFunction2'), array('class'=>$this->extPrefix.'_buttonSpecial'));
        $content .= $confirmationForm->toHtml();
        
        return $content;
        
    }
    
    /**
     * Processes the "Edit dispatch cost" form data
     *
     * @param       void
     * @return      string      HTML content to confirm the action
     * @author      Rainer Kuhn <kuhn@punkt.de>
     * @since       2007-10-29
     */
    protected function processEditCostData() {
            
        // process data (if form has not been submitted already)
        if ($this->formReloadHandler->checkToken(t3lib_div::GPvar('__formToken')) == true) {
            $dispatchcostObj = $this->createDispatchcostObjFromFormData(t3lib_div::GPvar('gsaCostId'));
            tx_ptgsashop_dispatchCostAccessor::getInstance()->updateDispatchCostRecord($dispatchcostObj);
        }
        
        // build "confirmation form": display confirmation text plus repeat action button
        $confirmationForm = new HTML_QuickForm('confUpdateForm', 'post');
        $confirmationForm->addElement('header', 'confUpdate', $this->ll('costFormAction_confUpdate'));
        $confirmationForm->addElement('submit', 'btnEditCost', $this->ll('costForm_btnSubmit_back'), array('class'=>$this->extPrefix.'_buttonSpecial'));
        $content .= $confirmationForm->toHtml();
        
        return $content;
        
    }
    
    /**
     * Creates and returns an dispatch cost object from the cost form's data
     * @param       integer     (optional) 0 for empty dispatch cost type built from "new dispatch cost" form (=default) or GSA database UID of the dispatch cost to update from "edit dispatch cost" form
     * @return      tx_ptgsashop_dispatchCost      dispatch cost object built from the cost form's data
     * @author      Rainer Kuhn <kuhn@punkt.de>
     * @since       2007-10-29
     */
    protected function createDispatchcostObjFromFormData($editDispatchCostTypeUid=0) {
        
        $dispatchcostObj = new tx_ptgsashop_dispatchCost('', $editDispatchCostTypeUid); // create empty dispatch cost object or existing dispatch cost from database
        
        // set the dispatch cost object's properties
        $dispatchcostObj->set_costTypeName(t3lib_div::GPvar('costTypeName'));
        $component1GroupArr = t3lib_div::GPvar('component1Group', 1);
        $component2GroupArr = t3lib_div::GPvar('component2Group', 1);
        $component3GroupArr = t3lib_div::GPvar('component3Group', 1);
        $component4GroupArr = t3lib_div::GPvar('component4Group', 1);
        for ($i=1; $i<=4; $i++) {
            $componentGroupArr     = t3lib_div::GPvar('component'.strval($i).'Group', 1);
            $setterMethodComp      = 'set_costComp'.strval($i);
            $setterMethodAllowance = 'set_allowanceComp'.strval($i);
            $compValue      = (strlen(trim($componentGroupArr['costComp'.strval($i)])) > 0 ? $componentGroupArr['costComp'.strval($i)] : NULL); // // unset property if field is empty
            $allowanceValue = (strlen(trim($componentGroupArr['allowanceComp'.strval($i)])) > 0 ? $componentGroupArr['allowanceComp'.strval($i)] : NULL); // // unset property if field is empty
            $dispatchcostObj->$setterMethodComp($compValue); // results in e.g. $dispatchcostObj->set_costComp1($componentGroupArr['costComp1']);
            $dispatchcostObj->$setterMethodAllowance($allowanceValue); // results in e.g. $dispatchcostObj->set_allowanceComp1($componentGroupArr['allowanceComp1']);
        }
        trace($dispatchcostObj);
        return $dispatchcostObj;
        
    }
    
    /**
     * Creates and returns an already built dispatch cost selection form
     *
     * @param       void
     * @return      HTML_QuickForm  object of type HTML_QuickForm: an already built dispatch cost selector form
     * @author      Rainer Kuhn <kuhn@punkt.de>
     * @since       2007-10-29
     */
    protected function returnCostSelectionForm() {
        
        $costSelectorArr = array();
        $form = new HTML_QuickForm('costSelector', 'post');
        
        // prepare quickform for multilang mode
        $form->setJsWarnings($this->ll('qf_jsWarningPref'), $this->ll('qf_jsWarningPost'));
        $form->setRequiredNote('* '.$this->ll('qf_requiredNote'));
        
        // build selectorbox array for dispatch costs 
        $dispatchCostCollection = new tx_ptgsashop_dispatchCostCollection(true);
        foreach ($dispatchCostCollection as $dispatchCostObj) {
            $costSelectorArr[$dispatchCostObj->get_costUid()] = $dispatchCostObj->get_costTypeName().' ['.$dispatchCostObj->get_costUid().']';
        }
        ksort($costSelectorArr);
        
        // build form
        $form->addElement('header', 'artHeader1', $this->ll('selForm_header1'));
        $form->addElement('select', 'costSelector', $this->ll('selForm_costSelector'), $costSelectorArr);
        $form->addElement('submit', 'btnEdit', $this->ll('selForm_btnEdit'));
        
        #trace($form);
        return $form;
        
    }
    
    /**
     * Creates and returns an already built cost form
     *
     * @param       array   (optional) array (key=elementName, value=elementDefaultValue) containing the defaults for the form elements; if not set, an empty form is created
     * @return      HTML_QuickForm  object of type HTML_QuickForm: an already built cost form
     * @author      Rainer Kuhn <kuhn@punkt.de>
     * @since       2007-10-29
     */
    protected function returnCostForm($defaultsDataArr=array()) {
        
        $buttonsArr = array();
        $component1FieldsArr = array();
        $component2FieldsArr = array();
        $component3FieldsArr = array();
        $component4FieldsArr = array();
        $form = new HTML_QuickForm('costForm', 'post');
        $formMode = (empty($defaultsDataArr) ? 'new' : 'edit');
        
        // prepare quickform for multilang mode and reload prevention
        $form->setJsWarnings($this->ll('qf_jsWarningPref'), $this->ll('qf_jsWarningPost'));
        $form->setRequiredNote('* '.$this->ll('qf_requiredNote'));
        $form->addElement('hidden', '__formToken', $this->formReloadHandler->createToken());
        
        // set defaults depending on form mode
        if ($formMode == 'edit') {
            $form->setDefaults($defaultsDataArr);
            $form->addElement('hidden', 'gsaCostId');
            $form->addElement('submit', 'btnDeleteCost', $this->ll('costForm_btnSubmit_delete'), array('onclick'=>"return confirm('".$this->ll('costForm_btnSubmit_delete_confirm')."')"));
        } 
        
        // prepare button group depending on form mode
        if ($formMode == 'edit') {
            $buttonsArr[] = HTML_QuickForm::createElement('submit', 'btnSave', $this->ll('costForm_btnSubmit_edit'));
            $buttonsArr[] = HTML_QuickForm::createElement('reset', 'btnClear', $this->ll('costForm_btnClear'));
        } else {
            $buttonsArr[] = HTML_QuickForm::createElement('submit', 'btnNew', $this->ll('costForm_btnSubmit_new'));
        }
        
        // build form: create fields
        $form->addElement('header', 'header1', $this->ll('costForm_header1'));
        $form->addElement('text', 'costTypeName', $this->ll('costForm_costTypeName'), array('maxlength'=>'30', 'class'=>$this->extPrefix.'_inputTextShort'));
        $component1FieldsArr[] = HTML_QuickForm::createElement('text', 'costComp1', '', array('maxlength'=>'19', 'class'=>$this->extPrefix.'_inputTextPrice'));
        $component1FieldsArr[] = HTML_QuickForm::createElement('text', 'allowanceComp1', '', array('maxlength'=>'19', 'class'=>$this->extPrefix.'_inputTextPrice'));
        $form->addGroup($component1FieldsArr, 'component1Group', $this->ll('costForm_costComp1'), ' '.$this->ll('costForm_allowance').': ');
        $component2FieldsArr[] = HTML_QuickForm::createElement('text', 'costComp2', '', array('maxlength'=>'19', 'class'=>$this->extPrefix.'_inputTextPrice'));
        $component2FieldsArr[] = HTML_QuickForm::createElement('text', 'allowanceComp2', '', array('maxlength'=>'19', 'class'=>$this->extPrefix.'_inputTextPrice'));
        $form->addGroup($component2FieldsArr, 'component2Group', $this->ll('costForm_costComp2'), ' '.$this->ll('costForm_allowance').': ');
        $component3FieldsArr[] = HTML_QuickForm::createElement('text', 'costComp3', '', array('maxlength'=>'19', 'class'=>$this->extPrefix.'_inputTextPrice'));
        $component3FieldsArr[] = HTML_QuickForm::createElement('text', 'allowanceComp3', '', array('maxlength'=>'19', 'class'=>$this->extPrefix.'_inputTextPrice'));
        $form->addGroup($component3FieldsArr, 'component3Group', $this->ll('costForm_costComp3'), ' '.$this->ll('costForm_allowance').': ');
        $component4FieldsArr[] = HTML_QuickForm::createElement('text', 'costComp4', '', array('maxlength'=>'19', 'class'=>$this->extPrefix.'_inputTextPrice'));
        $component4FieldsArr[] = HTML_QuickForm::createElement('text', 'allowanceComp4', '', array('maxlength'=>'19', 'class'=>$this->extPrefix.'_inputTextPrice'));
        $form->addGroup($component4FieldsArr, 'component4Group', $this->ll('costForm_costComp4'), ' '.$this->ll('costForm_allowance').': ');
        
        // build form: add validation rules
        $form->addRule('costTypeName', sprintf($this->ll('qf_ruleRequired'), $this->ll('costForm_costTypeName')), 'required', '', 'client');
        $form->addRule('costTypeName', sprintf($this->ll('qf_ruleMaxlength'), $this->ll('costForm_costTypeName'), '30'), 'maxlength', 30, 'client');
        # $form->addRule('component1Group', sprintf($this->ll('qf_ruleRequired'), $this->ll('costForm_costComp1')), 'required', '', 'client'); // disabled due to required check on delete action (ry37 8.4.08)
        $form->addGroupRule('component1Group', array(
            'costComp1' => array(
                # array(sprintf($this->ll('qf_ruleRequired'), $this->ll('costForm_costComp1')), 'required', '', 'client'), // disabled due to required check on delete action (ry37 8.4.08)
                array(sprintf($this->ll('qf_ruleNumeric'), $this->ll('costForm_costComp1')), 'numeric', '', 'client'),
            ),  
            'allowanceComp1' => array(
                array(sprintf($this->ll('qf_ruleNumeric'), ($this->ll('costForm_costComp1').'/'.$this->ll('costForm_allowance'))), 'numeric', '', 'client'),
            ) 
        ));
        $form->addGroupRule('component2Group', array(
            'costComp2' => array(
                array(sprintf($this->ll('qf_ruleNumeric'), $this->ll('costForm_costComp2')), 'numeric', '', 'client'),
            ),  
            'allowanceComp2' => array(
                array(sprintf($this->ll('qf_ruleNumeric'), ($this->ll('costForm_costComp2').'/'.$this->ll('costForm_allowance'))), 'numeric', '', 'client'),
            ) 
        ));
        $form->addGroupRule('component3Group', array(
            'costComp3' => array(
                array(sprintf($this->ll('qf_ruleNumeric'), $this->ll('costForm_costComp3')), 'numeric', '', 'client'),
            ),  
            'allowanceComp3' => array(
                array(sprintf($this->ll('qf_ruleNumeric'), ($this->ll('costForm_costComp3').'/'.$this->ll('costForm_allowance'))), 'numeric', '', 'client'),
            ) 
        ));
        $form->addGroupRule('component4Group', array(
            'costComp4' => array(
                array(sprintf($this->ll('qf_ruleNumeric'), $this->ll('costForm_costComp4')), 'numeric', '', 'client'),
            ),  
            'allowanceComp4' => array(
                array(sprintf($this->ll('qf_ruleNumeric'), ($this->ll('costForm_costComp4').'/'.$this->ll('costForm_allowance'))), 'numeric', '', 'client'),
            ) 
        ));
        
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
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsaadmin/mod_dispatch/class.tx_ptgsaadmin_module3.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsaadmin/mod_dispatch/class.tx_ptgsaadmin_module3.php']);
}

?>