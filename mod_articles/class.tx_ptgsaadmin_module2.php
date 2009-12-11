<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007-2008 Rainer Kuhn <kuhn@punkt.de>, Fabrizio Branca <branca@punkt.de>
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
 * Module 'Articles' of the 'pt_gsaadmin' extension.
 *
 * $Id: class.tx_ptgsaadmin_module2.php,v 1.56 2008/10/21 15:15:49 ry37 Exp $
 *
 * @author  Rainer Kuhn <kuhn@punkt.de>, Fabrizio Branca <branca@punkt.de>
 * @since   2007-08-28
 */ 


/**
 * Inclusion of external PEAR resources: this requires PEAR to be installed on your server (see http://pear.php.net/) and the path to PEAR being part of your include path!
 */
require_once 'HTML/QuickForm.php';  // PEAR HTML_QuickForm: methods for creating, validating, processing HTML forms (see http://pear.php.net/manual/en/package.html.html-quickform.php). This requires the PEAR module to be installed on your server and the path to PEAR being part of your include path.



/**
 * Inclusion of TYPO3 resources
 */
require_once PATH_t3lib.'class.t3lib_parsehtml_proc.php'; // needed for RTE Rendering

require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_exception.php'; // general exception class
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_debug.php'; // debugging class with trace() function
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_div.php'; // general static library class
require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_formReloadHandler.php'; // web form reload handler class
require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_sessionStorageAdapter.php'; // session storage adapter

require_once t3lib_extMgm::extPath('pt_gsashop').'res/class.tx_ptgsashop_baseArticle.php';  // GSA shop abstract base class for articles
require_once t3lib_extMgm::extPath('pt_gsashop').'res/class.tx_ptgsashop_articleFactory.php';  // GSA shop article factory class
require_once t3lib_extMgm::extPath('pt_gsashop').'res/class.tx_ptgsashop_articleCollection.php';// GSA shop article collection class
require_once t3lib_extMgm::extPath('pt_gsashop').'res/class.tx_ptgsashop_scalePrice.php';// GSA Shop article scale price class
require_once t3lib_extMgm::extPath('pt_gsashop').'res/class.tx_ptgsashop_scalePriceCollection.php';// GSA Shop article scale price collection class
require_once t3lib_extMgm::extPath('pt_gsashop').'res/class.tx_ptgsashop_taxAccessor.php';  // GSA shop database accessor class for tax data
require_once t3lib_extMgm::extPath('pt_gsashop').'res/class.tx_ptgsashop_articleImage.php';
require_once t3lib_extMgm::extPath('pt_gsashop').'res/class.tx_ptgsashop_articleImageAccessor.php';
require_once t3lib_extMgm::extPath('pt_gsashop').'res/class.tx_ptgsashop_articleImageCollection.php';
require_once t3lib_extMgm::extPath('pt_gsashop').'res/class.tx_ptgsashop_cacheController.php'; 

require_once t3lib_extMgm::extPath('pt_gsaadmin').'res/class.tx_ptgsaadmin_submodules.php'; //  Abstract submodules parent class for the 'pt_gsaadmin' extension
require_once t3lib_extMgm::extPath('pt_gsaadmin').'res/class.tx_ptgsaadmin_articleAccessor.php'; // GSA Admin article accessor class for DB modifications
require_once t3lib_extMgm::extPath('pt_gsaadmin').'res/list/class.tx_ptgsaadmin_list.php';  
require_once t3lib_extMgm::extPath('pt_gsaadmin').'res/list/class.tx_ptgsaadmin_button.php';
require_once t3lib_extMgm::extPath('pt_gsaadmin').'tca_virtual_tables.php';

/**
 * Debugging config for development
 */
#$trace     = 1; // (int) trace options @see tx_pttools_debug::trace() [for local temporary debugging use only, please COMMENT OUT this line if finished with debugging!]
#$errStrict = 1; // (bool) set strict error reporting level for development (requires $trace to be set to 1)  [for local temporary debugging use only, please COMMENT OUT this line if finished with debugging!]



/**
 * Class for backend sub module 'Articles' of the 'pt_gsaadmin' extension.
 *
 * @author      Rainer Kuhn <kuhn@punkt.de>, Fabrizio Branca <branca@punkt.de>
 * @since       2007-08-24
 * @package     TYPO3
 * @subpackage  tx_ptgsaadmin
 */
class tx_ptgsaadmin_module2 extends tx_ptgsaadmin_submodules {
	
	
	
	/***************************************************************************
     *   STATIC variables
     **************************************************************************/
	
	/**
	 * @var int		The webaddress field is a varchar(40) in the original ERP database. If you need a longer field update your database and set this value to the length e.g. in extTables.php
	 */
	static $webAddressFieldLength = 40;
    
    
    
    /***************************************************************************
     *   INHERITED METHODS from tx_ptgsaadmin_submodules
     **************************************************************************/

    /**
     * Initializes the module
     *
     * @param       void
     * @return      void
     * @author      Rainer Kuhn <kuhn@punkt.de>
     * @since       2008-02-21
     */
    public function init() {
        
        try {
            
            // call tx_ptgsaadmin_submodules::init() first to get basic properties required below (like $this->jsArray['jquery'] and $this->extKey)
            parent::init(); 
            
            // include this module's specific jQuery code to the BE HTML code (inclusion of /typo3conf/ext/jquery/src/jquery.js has to be done before!)
            $this->jsArray['jquery_main'] = '<script type="text/javascript" src="'.$GLOBALS['BACK_PATH'].'../typo3conf/ext/'.$this->extKey.'/res/js/mod_articles.js"></script>'; 
            
        } catch (tx_pttools_exception $excObj) {
            
            $excObj->handleException();
            die($excObj->__toString());
            
        }
    }
    
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
     * @author      Rainer Kuhn <kuhn@punkt.de>, Fabrizio Branca <branca@punkt.de>
     * @since       2007-08-24
     */
    public function moduleContent() {
        
       $moduleContent = '';
            
        try {
            
            switch (t3lib_div::GPvar('action')) {
                case 'editArticle': 
                    $content = $this->exec_editOrNewArticleAction(intval(t3lib_div::GPvar('uid')));
                    $moduleContent .= $this->doc->section($this->ll('actionHeader1'), $content, 0, 1);
                    break;
                
                case 'newArticle':
                    $content = $this->exec_editOrNewArticleAction();
                    $moduleContent .= $this->doc->section($this->ll('actionHeader2'), $content, 0, 1);
                    break;
                
                case 'deleteArticle':
                    $this->exec_deleteArticleAction();
                    
                case 'search':
                case 'listArticles':
                    
                default:
                    // execute jump menu related actions
                    if ($this->MOD_SETTINGS['jumpMenuFunction'] == '2') {
                        $content = $this->exec_editOrNewArticleAction();
                        $moduleContent .= $this->doc->section($this->ll('actionHeader2'), $content, 0, 1);
                        break;
                    }
                    // default action if no jump menu related actions to perform
                    $content = $this->exec_listArticlesAction();
                    $moduleContent .= $this->doc->section($this->ll('actionHeader1'), $content, 0, 1);  
            } 
            
            $this->printJS();
            
        } catch (tx_pttools_exception $excObj) {
            $excObj->handleException();
            $moduleContent = '<i>'.$excObj->__toString().'</i>';
        }
        
        return $moduleContent;
        
    }
    
    
    
    /***************************************************************************
     *   BUSINESS LOGIC METHODS
     **************************************************************************/
    
    /**
     * Processes the "Edit article" action and returns the resulting HTML content
     *
     * @param       TODO: (Fabrizio) add comment
     * @return      string      resulting HTML content to display for "Edit article"
     * @author      Rainer Kuhn <kuhn@punkt.de>, Fabrizio Branca <branca@punkt.de>
     * @since       2007-10-12
     */
    protected function exec_editOrNewArticleAction($uid=-1) {
        
        $content = '';
        
        $articleDefaultsDataArray = ($uid >= 0 ? $this->loadArticleDefaults($uid) : array());
        $articleForm = $this->returnArticleForm($articleDefaultsDataArray);
        
        if (t3lib_div::GPvar('btnAction_saveAndNew_x')) { // "_x" is appended, because IE does not transmit the value when using <input type="image"...
            $action = 'saveAndNew';
        } elseif (t3lib_div::GPvar('btnAction_delete_x')) {
            $action = 'delete';
        } elseif (t3lib_div::GPvar('btnAction_saveAndClose_x')) {
            $action = 'saveAndClose';
        } elseif (t3lib_div::GPvar('btnAction_save_x')) {
            $action = 'save';
        } elseif (t3lib_div::GPvar('btnAction_close')) { // no "_x" because this is a link, as we do not want to process the form in this case!
            tx_pttools_div::localRedirect('index.php?action=listArticles');
        }
        
        if (!$articleForm->validate() == true) {
            $action = 'view';
        } 
        
        // "Sub-Controller"
        switch ($action) {
            case 'saveAndNew':
                $newUid = $this->processArticleData($uid);
                tx_pttools_div::localRedirect('index.php?action=newArticle');
                break;
                
            case 'delete':
                tx_pttools_div::localRedirect('index.php?action=deleteArticle&uid='.$uid);
                break;
                
            case 'saveAndClose':
                $newUid = $this->processArticleData($uid);
                tx_pttools_div::localRedirect('index.php?action=listArticles');
                break;
                
            case 'save':
                $newUid = $this->processArticleData($uid);
                tx_pttools_div::localRedirect('index.php?action=editArticle&uid='.$newUid);
                break;
            
            case 'view':
            
            default:
                $content .= $articleForm->toHTML();
        }    
        
        return $content;
    }
    
    /**
     * Processes the "Delete article" action and returns the resulting HTML content
     *
     * @param       void
     * @return      string      resulting HTML content to display for "Delete article"
     * @author      Rainer Kuhn <kuhn@punkt.de>, Fabrizio Branca <branca@punkt.de>
     * @since       2007-10-22
     */
    protected function exec_deleteArticleAction() {
        
        // delete article and its related data (if form has not been submitted already)
        tx_ptgsaadmin_articleAccessor::getInstance()->deleteArticle(t3lib_div::GPvar('uid')); 
        
        // delete from cache
        tx_ptgsashop_cacheController::getInstance()->clearCache(t3lib_div::GPvar('uid'));
        
        // HOOK: process further data (added by Fabrizio Branca 2008-01-23)
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['pt_gsaadmin']['module2_hooks']['deleteArticle'])) {
            foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['pt_gsaadmin']['module2_hooks']['deleteArticle'] as $_funcRef) {
                $_params = array('articleUid' => t3lib_div::GPvar('uid'));
                t3lib_div::callUserFunction($_funcRef, $_params, $this, '');
            }
        }
        
        // tx_pttools_div::localRedirect('index.php?action=listArticles');
        
    }
    
    /**
     * Displays the article list
     * 
     * @param       void
     * @return 		string		HTML output
     * @author      Fabrizio Branca <branca@punkt.de>
     * @since 		2007-12-11
     */
    protected function exec_listArticlesAction() {
        
        $tmp = t3lib_div::_GP('first');
        
        $searchString = trim(urldecode(t3lib_div::_GP('search_field')));
        
        if (!empty($searchString)) {
            $content .= '<h2 class="tx_ptgsaadmin_message">'.sprintf($this->ll('searchResults'), htmlspecialchars($searchString)).'</h2>';
            $content .= '[<a href="index.php">'.$this->ll('listResetSearch').'</a>]<br />';
        }
        
        $first = empty($tmp) ? 0 : intval($tmp);
        $first = max($first, 0);
        
        $amount = empty($this->conf['articlesPerPage']) ? 20 : $this->conf['articlesPerPage'];
        
        $list = new tx_ptgsaadmin_list();
        $list->set_title($this->ll('listTitle'). (!empty($searchString) ? ' ('.$this->ll('listTitleSearchAddition').' "'.htmlspecialchars($searchString).'")' : ''));
        $list->set_passParameter(array('labelcolumns' => strval(count(explode('|', $this->conf['templateArticleListViewLabel'])))));
        
        // create header row
        $tableHeadRow = new tx_ptgsaadmin_row();
        $tableHeadRow->addCell(new tx_ptgsaadmin_cell());
        
        foreach (explode('|', $this->conf['templateArticleListViewTitle']) as $part) {              
            $tableHeadRow->addCell(new tx_ptgsaadmin_cell($GLOBALS['LANG']->sL($part)));
        }
        
        
        $newArticleBtn = new tx_ptgsaadmin_button('?action=newArticle', '', $this->ll('new'), '', '', 'gfx/new_el.gif');
        $tableHeadRow->addCell(new tx_ptgsaadmin_cell($newArticleBtn->toHTML()));
        
        $list->set_tableHeadRow($tableHeadRow);
        
        // paging top
        $onlineArticlesQuantity = tx_ptgsashop_articleAccessor::getInstance()->selectOnlineArticlesQuantity($searchString);
        $span['lower'] = max($first - $amount, 1);
        $span['higher'] = min($span['lower']+$amount -1, $onlineArticlesQuantity);
        
        if ($first >= $amount) {
            $txt = '['.$span['lower']. ' - ' .$span['higher']. '] of '.$onlineArticlesQuantity;
            $pageUpBtn = new tx_ptgsaadmin_button('?first='.$span['lower'].'&search_field='.urlencode($searchString),'','','','','gfx/pilup.gif','',$txt);
            $list->set_pageUpBtn($pageUpBtn->toHTML());
        }
        
        $onlineArticlesCollection = new tx_ptgsashop_articleCollection();  
           
        if ($onlineArticlesQuantity > 0) {   
            
            $onlineArticlesArr = tx_ptgsashop_articleAccessor::getInstance()->selectOnlineArticles('ARTNR', $first.','.$amount, $searchString);
            if (is_array($onlineArticlesArr)) {
                foreach ($onlineArticlesArr as $articleDataArr) {
                    $onlineArticlesCollection->addItem(new tx_ptgsashop_article($articleDataArr['NUMMER']));
                }
            }
        
            /* @var $articleObj tx_ptgsashop_article */
            foreach ($onlineArticlesCollection as $articleObj) {
                $row = new tx_ptgsaadmin_row();
                
                // record icon
                $articleIcon = ($articleObj->get_isPassive() == true ? 'article_passive.png' : 'article.png');
                $row->addCell(new tx_ptgsaadmin_cell('<img src="../../../../'.t3lib_extMgm::extRelPath($this->extKey).'res/img/'.$articleIcon.'" width="16px" height="16px" title="id='.$articleObj->get_id().'"/>'));
                
                $rplArray = array ( '###ARTNO###' => $articleObj->get_artNo(),
                                    '###ID###' => $articleObj->get_id(),
                                    '###MATCH1###' => $articleObj->get_match1(),
                                    '###MATCH2###' => $articleObj->get_match2(),
                                    '###ALTTEXT###' => $articleObj->get_altText(),
                                    '###DESCRIPTION###' => $articleObj->get_description(),
                                  );                                  
                
                // record label                     
                $label = str_replace(array_keys($rplArray), $rplArray, $this->conf['templateArticleListViewLabel']);
                foreach (explode('|', $label) as $part) {              
                    $row->addCell(new tx_ptgsaadmin_cell($part));
                }
                
                // buttons
                $editBtn = new tx_ptgsaadmin_button('?action=editArticle&uid='.$articleObj->get_id(), '', $this->ll('edit'), '', '', 'gfx/edit2.gif');                
                $deleteBtn = new tx_ptgsaadmin_button('?action=deleteArticle&uid='.$articleObj->get_id(), '', $this->ll('delete'), '', 'return confirm(\''.addslashes($this->ll('artForm_btnSubmit_delete_confirm')).'\')', 'gfx/garbage.gif');
                
                $buttonCell = new tx_ptgsaadmin_cell($editBtn->toHTML().$deleteBtn->toHTML());
                $buttonCell->set_wrap('<div class="typo3-DBctrl">|</div>');
                
                $row->addCell($buttonCell);
                
                $list->addRow($row);
            }
    
            // paging bottom
            $span['lower'] = max($first + $amount, 1);
            $span['higher'] = min($span['lower']+$amount -1, $onlineArticlesQuantity);
            if ($span['lower']< $onlineArticlesQuantity) {
                $txt = '['.$span['lower']. ' - ' .$span['higher']. '] of '.$onlineArticlesQuantity;
                $pageDownBtn = new tx_ptgsaadmin_button('?first='.$span['lower'].'&search_field='.urlencode($searchString), '', '', '', '', 'gfx/pildown.gif', '', $txt);
                $list->set_pageDownBtn($pageDownBtn->toHTML());
            }
            
        } else {
            $row = new tx_ptgsaadmin_row();
            $row->addCell(new tx_ptgsaadmin_cell());
            $row->addCell(new tx_ptgsaadmin_cell($this->ll('noArticlesFound'), count(explode('|', $this->conf['templateArticleListViewLabel']))+1 )); // TODO: (Fabrizio) if (search) { "reset search" } else { "create new article" }  
            $list->addRow($row);
        }
        
        // TODO: (Fabrizio) make configurable
        $content .= $list->toHTML('EXT:pt_gsaadmin/res/smarty_tpl/list.tpl.html');
        
        // Search Box:
        $content .= $this->printSearchForm($searchString, $this->ll('searchString'), $this->ll('searchButtonLabel'));
                        
        return $content;
        
    }
    
    /**
     * Loads the article defaults from the database for a given uid
     *
     * @param       int			uid of the article to be loaded
     * @return      array		array of article data
     * @author      Rainer Kuhn <kuhn@punkt.de>
     * @since       2007-10-22  (based on processArticleSelection(), renamed to loadArticleDefaults() by Fabrizio Branca <branca@punkt.de> since 2007-12-11)
     */
    protected function loadArticleDefaults($uid) {
        
        // create form defaults from article object
        $articleDataArr = array();
        $articleObj = tx_ptgsashop_articleFactory::createArticle($uid); // create selected article object
        
        // set article basic data fields
        $articleDataArr['gsaArticleId'] = $articleObj->get_id();
        $articleDataArr['artNo'] = $articleObj->get_artNo();
        $articleDataArr['match1'] = $articleObj->get_match1();
        $articleDataArr['match2'] = $articleObj->get_match2();
        $articleDataArr['passiveFlag'] = $articleObj->get_isPassive();
        $articleDataArr['taxCodeInland'] = $articleObj->get_taxCodeInland();
        $articleDataArr['webAddressGroup[webAddress]'] = $articleObj->get_webAddress();
        $articleDataArr['grossPriceFlag'] = $articleObj->get_grossPriceFlag();
        $articleDataArr['defText'] = $articleObj->get_defText();
        $articleDataArr['altText'] = $articleObj->get_altText();
        $articleDataArr['fixedCost1'] = $articleObj->get_fixedCost1();
        $articleDataArr['fixedCost2'] = $articleObj->get_fixedCost2();
        $articleDataArr['eanNumber'] = $articleObj->get_eanNumber();
        for ($i=1; $i<=8; $i++) {
            $getterMethod = 'get_userField0'.strval($i);
            $articleDataArr['userField0'.strval($i)] = $articleObj->$getterMethod();  // results in e.g. $articleObj->get_userField01();
        }
        
        // set article scale prices fields
        $articleDataArr['scalePrice'] = array();
        foreach ($articleObj->get_scalePriceCollectionObj() as $scalePriceObj) {
            $scalePriceQty = $scalePriceObj->get_quantity();
            $articleDataArr['scalePrice'][$scalePriceQty] = array();
            $articleDataArr['scalePrice'][$scalePriceQty]['scalePriceQuantity'] = $scalePriceQty;
            $articleDataArr['scalePrice'][$scalePriceQty]['basicRetailPriceCategory1'] = $scalePriceObj->get_basicRetailPriceCategory1();
            $articleDataArr['scalePrice'][$scalePriceQty]['basicRetailPriceCategory2'] = $scalePriceObj->get_basicRetailPriceCategory2();
            $articleDataArr['scalePrice'][$scalePriceQty]['basicRetailPriceCategory3'] = $scalePriceObj->get_basicRetailPriceCategory3();
            $articleDataArr['scalePrice'][$scalePriceQty]['basicRetailPriceCategory4'] = $scalePriceObj->get_basicRetailPriceCategory4();
            $articleDataArr['scalePrice'][$scalePriceQty]['basicRetailPriceCategory5'] = $scalePriceObj->get_basicRetailPriceCategory5();
            $articleDataArr['scalePrice'][$scalePriceQty]['specialOfferGroup'] = array();
            $articleDataArr['scalePrice'][$scalePriceQty]['specialOfferGroup']['specialOfferFlag'] = $scalePriceObj->get_specialOfferFlag();
            $articleDataArr['scalePrice'][$scalePriceQty]['specialOfferGroup']['specialOfferRetailPrice'] = $scalePriceObj->get_specialOfferRetailPrice();
            list($year, $month, $day) = split('-', $scalePriceObj->get_specialOfferStartDate(), 3);
            $articleDataArr['scalePrice'][$scalePriceQty]['specialOfferGroup']['specialOfferStartDate'] = array('d'=>$day, 'm'=>$month, 'Y'=>$year);
            list($year, $month, $day) = split('-', $scalePriceObj->get_specialOfferEndDate(), 3);
            $articleDataArr['scalePrice'][$scalePriceQty]['specialOfferGroup']['specialOfferEndDate'] = array('d'=>$day, 'm'=>$month, 'Y'=>$year);
        }
        
        
        // article images (added by Fabrizio Branca 2007-12)
//        $imgAcc = tx_ptgsashop_articleImageAccessor::getInstance();
//        $imgPaths = $imgAcc->getPathArray($imgAcc->selectByGsaArtNummer($articleObj->get_id()));
//        $articleDataArr['image'] = implode(',', $imgPaths);
        $imageCollection = new tx_ptgsashop_articleImageCollection($articleObj->get_id());
        $articleDataArr['image'] = implode(',', $imageCollection->getPropertyArray('path') );
        $articleDataArr['image_alt'] = implode("\n", $imageCollection->getPropertyArray('alt') );
        $articleDataArr['image_title'] = implode("\n", $imageCollection->getPropertyArray('title') );

        // HOOK: process further data (added by Fabrizio Branca 2007-12)
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['pt_gsaadmin']['module2_hooks']['loadArticleDefaults'])) {
            foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['pt_gsaadmin']['module2_hooks']['loadArticleDefaults'] as $_funcRef) {
                $_params = array('articleDataArr' => &$articleDataArr,
                                 'articleObj' => $articleObj);
                t3lib_div::callUserFunction($_funcRef, $_params, $this, '');
            }
        }
                
        return $articleDataArr;
        
    }
    
    /**
     * Processes the article form data (create or update article)
     *
     * @param       integer     (optional) uid of the article to be updated
     * @return      integer		uid of the inserted article record
     * @throws		tx_pttools_exception	if form was already submitted
     * @author      Rainer Kuhn <kuhn@punkt.de>, Fabrizio Branca <branca@punkt.de>
     * @since       2007-12-10 (based on processNewArticleData()/processEditArticleData() since 2007-10-10)
     */
    protected function processArticleData($uid=-1) {
            
        // process data (if form has not been submitted already)
        if ($this->formReloadHandler->checkToken(t3lib_div::GPvar('__formToken')) == true) {
               	
            $articleObj = $this->createArticleFromFormData(($uid >= 0) ? t3lib_div::GPvar('uid') : 0);
            
            if ($uid >= 0) {
                $newUid = tx_ptgsaadmin_articleAccessor::getInstance()->updateArticle($articleObj);
            } else {
                $newUid = tx_ptgsaadmin_articleAccessor::getInstance()->insertNewArticle($articleObj);
            }
            $this->processRelatedData($newUid);
            
            // update cache & store RTE setting
            tx_ptgsashop_cacheController::getInstance()->insertArticlesIntoCacheTable($newUid);
            tx_pttools_sessionStorageAdapter::store('disableRte', intval(t3lib_div::GPvar('disableRte')));
            
        } else {
            throw new tx_pttools_exception('Form was already submitted!');
        }
        
        return $newUid;
    }
    
    /**
     * Creates and returns an article object from the article form's data
     * 
     * @param       integer     (optional) 0 for empty article built from "new article" form (=default) or GSA database UID of the article to update from "edit article" form
     * @return      tx_ptgsashop_baseArticle      article object built from the article form's data
     * @author      Rainer Kuhn <kuhn@punkt.de>
     * @throws      tx_pttools_exception    if no scale price areay has been submitted
     * @since       2007-10-10
     */
    protected function createArticleFromFormData($editArticleUid=0) {

        $articleObj = tx_ptgsashop_articleFactory::createArticle($editArticleUid); // create empty article object or existing article from database
        
        // set the article's basic data properties
        $articleObj->set_artNo(t3lib_div::GPvar('artNo'));
        $articleObj->set_match1(t3lib_div::GPvar('match1'));
        $articleObj->set_match2(t3lib_div::GPvar('match2'));
        $articleObj->set_isPassive(t3lib_div::GPvar('passiveFlag'));
        $articleObj->set_taxCodeInland(t3lib_div::GPvar('taxCodeInland'));
        $webAddressGroupArr = t3lib_div::GPvar('webAddressGroup', 1);
        $articleObj->set_webAddress($webAddressGroupArr['webAddress']);
        $articleObj->set_grossPriceFlag(t3lib_div::GPvar('grossPriceFlag'));
        $articleObj->set_fixedCost1(t3lib_div::GPvar('fixedCost1'));
        $articleObj->set_fixedCost2(t3lib_div::GPvar('fixedCost2'));
        $articleObj->set_eanNumber(t3lib_div::GPvar('eanNumber'));
        for ($i=1; $i<=8; $i++) {
            $setterMethod = 'set_userField0'.strval($i);
            $articleObj->$setterMethod(t3lib_div::GPvar('userField0'.strval($i))); // results in e.g. $articleObj->set_userField01(t3lib_div::GPvar('userField01'));
        }
        
        
        // set the article's scale prices' properties
        $scalePriceArr = t3lib_div::GPvar('scalePrice', 1);
        if (!is_array($scalePriceArr)) {
            throw new tx_pttools_exception('No scale pricing data found');
        }
        ksort($scalePriceArr);
        foreach ($scalePriceArr as $scalePriceQty=>$scalePriceArray) {
            if ($scalePriceQty > 0) {
                if (!($articleObj->get_scalePriceCollectionObj()->hasItem($scalePriceQty)) || !($articleObj->get_scalePriceCollectionObj()->getItemById($scalePriceQty) instanceof tx_ptgsashop_scalePrice)) {
                    // create new scale price object in collection if there is none so for for the given $scalePriceQty
                    $articleObj->get_scalePriceCollectionObj()->addItem(new tx_ptgsashop_scalePrice($editArticleUid, $scalePriceQty));
                }
                $scalePriceObj = $articleObj->get_scalePriceCollectionObj()->getItemById($scalePriceQty);  // $scalePriceObj is returned as object reference, so modifications will be done within $articleObj!
                $scalePriceObj->set_basicRetailPriceCategory1(strlen(trim($scalePriceArray['basicRetailPriceCategory1'])) > 0 ? $scalePriceArray['basicRetailPriceCategory1'] : NULL); // unset property if field is empty
                $scalePriceObj->set_basicRetailPriceCategory2(strlen(trim($scalePriceArray['basicRetailPriceCategory2'])) > 0 ? $scalePriceArray['basicRetailPriceCategory2'] : NULL); // unset property if field is empty
                $scalePriceObj->set_basicRetailPriceCategory3(strlen(trim($scalePriceArray['basicRetailPriceCategory3'])) > 0 ? $scalePriceArray['basicRetailPriceCategory3'] : NULL); // unset property if field is empty
                $scalePriceObj->set_basicRetailPriceCategory4(strlen(trim($scalePriceArray['basicRetailPriceCategory4'])) > 0 ? $scalePriceArray['basicRetailPriceCategory4'] : NULL); // unset property if field is empty
                $scalePriceObj->set_basicRetailPriceCategory5(strlen(trim($scalePriceArray['basicRetailPriceCategory5'])) > 0 ? $scalePriceArray['basicRetailPriceCategory5'] : NULL); // unset property if field is empty
                $specialOfferGroupArr = $scalePriceArray['specialOfferGroup'];
                if ($specialOfferGroupArr['specialOfferFlag'] == 1) { 
                    $specialOfferStartDate = sprintf('%04s-%02s-%02s', $specialOfferGroupArr['specialOfferStartDate']['Y'], $specialOfferGroupArr['specialOfferStartDate']['m'], $specialOfferGroupArr['specialOfferStartDate']['d']);
                    $specialOfferEndDate = sprintf('%04s-%02s-%02s', $specialOfferGroupArr['specialOfferEndDate']['Y'], $specialOfferGroupArr['specialOfferEndDate']['m'], $specialOfferGroupArr['specialOfferEndDate']['d']);
                    $scalePriceObj->set_specialOfferFlag($specialOfferGroupArr['specialOfferFlag']);
                    $scalePriceObj->set_specialOfferRetailPrice(strlen(trim($specialOfferGroupArr['specialOfferRetailPrice'])) > 0 ? $specialOfferGroupArr['specialOfferRetailPrice'] : NULL); // unset property if field is empty
                    $scalePriceObj->set_specialOfferStartDate($specialOfferStartDate);
                    $scalePriceObj->set_specialOfferEndDate($specialOfferEndDate);
                } else {
                    $scalePriceObj->set_specialOfferFlag(0);
                    // do not store additional specialOffer data if checkbox is not checked!
                }
                if ($scalePriceArray['quantityScaleDeleted'] == 1) { 
                    $scalePriceObj->set_isDeleted($scalePriceArray['quantityScaleDeleted']);
                }
            }
        }

        
         // process virtual table fields (added by Fabrizio Branca 2007-12)
        $tcedata = t3lib_div::GPvar('data');
        $id=0;
        $table = 'tx_ptgsaadmin_virtualarticle';
            // process deftext 
        $field = 'deftext';
        $value = stripslashes(trim($tcedata[$table][$id][$field]));
        if ($tcedata[$table][$id]['_TRANSFORM_'.$field] == 'RTE' && !empty($value)) {
            $value = $this->transformRTEContent($value, $table, $field);
        } 
        $articleObj->set_defText($value);
            // process alttext
        $field = 'alttext';
        $value = stripslashes(trim($tcedata[$table][$id][$field]));
        if ($tcedata[$table][$id]['_TRANSFORM_'.$field] == 'RTE' && !empty($value)) {
            $value = $this->transformRTEContent($value, $table, $field);
        }
        $articleObj->set_altText($value);
        
        
        // HOOK: process further data (added by Fabrizio Branca 2007-12)
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['pt_gsaadmin']['module2_hooks']['createArticleFromFormData_processData'])) {
            foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['pt_gsaadmin']['module2_hooks']['createArticleFromFormData_processData'] as $_funcRef) {
                $_params = array();
                t3lib_div::callUserFunction($_funcRef, $_params, $this, '');
            }
        }
        
        trace($articleObj);
        return $articleObj;
        
    }
    
    /**
     * Transforms RTE content to the representation for the database
     *
     * @param 	string	value
     * @param 	string	table 
     * @param 	string	field
     * @return 	string	converted value
     * @author  Fabrizio Branca <branca@punkt.de>
     * @since   2008-02-05
     */
    protected function transformRTEContent($value, $table, $field) {
        
        $tscPID = 1;
        $thePidValue = 1;
		
        $specConf['rte_transform']['parameters']['mode'] = 'ts_css';
        
        $RTEobj = &t3lib_BEfunc::RTEgetObj();
        $RTEsetup = $GLOBALS['BE_USER']->getTSConfig('RTE',t3lib_BEfunc::getPagesTSconfig($tscPID));
	    $thisConfig = t3lib_BEfunc::RTEsetup($RTEsetup['properties'],$table,$field);

		$saveData = array( $field => $value);
	 	$value = $RTEobj->transformContent('db',$saveData[$field], $table, $field, $saveData, $specConf, $thisConfig, '', $thePidValue);

	 	return $value;
        
    }
    
    
    /**
     * Processes related article data (which depends on the uid of the article and is stored in other tables)
     *
     * @param   int     article uid
     * @return  void
     * @author  Fabrizio Branca <branca@punkt.de>
     * @since   2008-01
     */
    protected function processRelatedData($editArticleUid) { 
               
        $tcedata = t3lib_div::GPvar('data');

        // article images (added by Fabrizio Branca 2007-12)
        $tcemain = t3lib_div::makeInstance('t3lib_TCEmain'); /* @var $tcemain t3lib_TCEmain */
        $tcemain->stripslashes_values = 0;
        $tcemain->start(array(), array());
        $tcemain->process_uploads($_FILES);
        
        $table = 'tx_ptgsaadmin_virtualarticle';
        $id = 0;
        $field = 'image';
        
        
        $value = trim($tcedata[$table][$id][$field],',');
        
        $savedImageCollection = new tx_ptgsashop_articleImageCollection($editArticleUid);
        
        $savedPaths = implode(',', $savedImageCollection->getPropertyArray('path') );
        
        /* 
        // TODO: (Fabrizio) clean up  
        $savedAlts = implode(',', $savedImageCollection->getPropertyArray('alt') );
        $savedTitles = implode(',', $savedImageCollection->getPropertyArray('title') );
        */
        
        $newPaths = $tcemain->checkValue_group_select_file(
	                    t3lib_div::trimExplode(',',$value), 
	                    $GLOBALS['TCA'][$table]['columns'][$field]['config'],
	                    $savedPaths,
	                    $tcemain->uploadedFileArray[$table][$id][$field],
	                    'update',
	                    $table,
	                    $id,
	                    $table.':'.$id.':'.$field
	               );
               
        $imgAcc = tx_ptgsashop_articleImageAccessor::getInstance(); 
        $imgAcc->updateImages($editArticleUid, $newPaths);    
        

        /*     
        // TODO: (Fabrizio) clean up  
        $newAlts = t3lib_div::trimExplode("\n", t3lib_div::GPvar('image_alt'));
        $newTitles = t3lib_div::trimExplode("\n", t3lib_div::GPvar('image_title'));
        
        if (!($savedPaths === $newPaths && $savedAlts === $newAlts && $savedTitles === $newTitles ) ) {
            // $a === $b : $a und $b enthalten die gleichen Schl�ssel- und Wert-Paare in der gleichen Reihenfolge
            
            foreach ($newPaths as $key => $path) {
                $tmpImageObj = new tx_ptgsashop_articleImage();
                $tmpImageObj->set_gsa_art_nummer($editArticleUid);
                $tmpImageObj->set_path($path);
                $tmpImageObj->set_alt($newAlts[$key]);
                $tmpImageObj->set_title($newTitles[$key]);
                $tmpImageObj->storeSelf();
            }
            
            /* @var imgObj tx_ptgsashop_articleImage / 
            foreach ($savedImageCollection as $imgObj) {
                $imgObj->deleteSelf();
            }
        }                       
        // $imgAcc->updateImages($editArticleUid, $res);
        */

        
        // HOOK: process further data (added by Fabrizio Branca 2007-12)
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['pt_gsaadmin']['module2_hooks']['createArticleFromFormData_processRelatedData'])) {
            foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['pt_gsaadmin']['module2_hooks']['createArticleFromFormData_processRelatedData'] as $_funcRef) {
                $_params = array('articleUid' => $editArticleUid);
                t3lib_div::callUserFunction($_funcRef, $_params, $this, '');
            }
        }
    }

    // TODO: (Fabrizio) clean up 
    /**
     * Rule for HTML_Quickform for checking if a form is valid
     * TODO: (Fabrizio) checkToken() doesn't work until now
     *
     * @param 	string	element_name
     * @param 	string	element_value
     * @return 	bool
     * @author  Fabrizio Branca <branca@punkt.de>
     * @since   ###
     */
    /*
    public function checkToken($element_name, $element_value) {
        return $this->formReloadHandler->checkToken($element_value, true);
    }
	*/
    
    
    /**
     * Creates and returns an already built article form
     *
     * @param       array   (optional) array (key=elementName, value=elementDefaultValue) containing the defaults for the form elements; if not set, an empty form is created
     * @return      HTML_QuickForm  object of type HTML_QuickForm: an already built article form
     * @author      Rainer Kuhn <kuhn@punkt.de>, Fabrizio Branca <branca@punkt.de>
     * @since       2007-10-11
     */
    protected function returnArticleForm($defaultsDataArr=array()) {
        
        // (added by Fabrizio Branca 2007-12)
        $url = 'index.php?action='.t3lib_div::GPvar('action');
        $tmp = t3lib_div::GPvar('uid');
        if (!empty($tmp)) {
            $url .= '&uid='.t3lib_div::GPvar('uid');
        }
        $form = new HTML_QuickForm('editform', 'post', $url, '', array('enctype' => "multipart/form-data")); // needs to be "editform" to be compatible with t3lib_TCEforms
        
        // prepare button group and depending on form mode (added by Fabrizio Branca 2007-12)
        $buttons = '';
        $buttons .= '<input type="image" class="c-inputButton" name="btnAction_save" value="save" '.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/savedok.gif').' title="'.$GLOBALS['LANG']->getLL('save',1).'" alt="" />';
        $buttons .= '<input type="image" class="c-inputButton" name="btnAction_saveAndClose" value="saveAndClose" '.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/saveandclosedok.gif').' title="'.$this->ll('saveandclose').'" alt="" />';
        $buttons .= '<input type="image" class="c-inputButton" name="btnAction_saveAndNew" value="saveAndNew" '.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/savedoknew.gif').' title="'.$this->ll('saveandclose').'" alt="" />';            
        $buttons .= '<a href="'.$url.'&btnAction_close=close"><img class="c-inputButton" '.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/closedok.gif').' title="'.$this->ll('close').'" alt="" /></a>';
        if (!empty($defaultsDataArr)) {
            $buttons .= '<input type="image" class="c-inputButton" name="btnAction_delete" value="delete" '.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/deletedok.gif').' title="'.$this->ll('delete').'" alt="" onclick="return confirm(\''.addslashes($this->ll('artForm_btnSubmit_delete_confirm')).'\')" />';
        }
        
        // prepare quickform for multilang mode and reload prevention
        $form->setJsWarnings($this->ll('qf_jsWarningPref'), $this->ll('qf_jsWarningPost'));
        // $form->setRequiredNote('<img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/required_h.gif').' title="Required fields" alt="* " />'.' '.$this->ll('qf_requiredNote'));
        $form->setRequiredNote('* '.$this->ll('qf_requiredNote'));
        
        // formReloadHandler integrated in HTML_Quickform as rule (TODO: (Fabrizio) - unfinished/doesn't work until now)
        #$form->registerRule('checkToken', 'callback', array(get_class(), 'checkToken'));
        #$form->addElement('hidden', '__formToken', $this->formReloadHandler->createToken());
        #$form->addRule('__formToken', 'Token', 'checkToken', '', 'client');
        $form->addElement('hidden', '__formToken', $this->formReloadHandler->createToken());
        
        
        // prepare defaults depending on whether $defaultsDataArr is given
        if (!empty($defaultsDataArr)) {
            ksort($defaultsDataArr['scalePrice']); // sort scale prices array by quantity
            $form->addElement('hidden', 'gsaArticleId');
        // prepare defaults for empty form
        } else {
            $defaultsDataArr['scalePrice'] = array();
            $defaultsDataArr['scalePrice'][1] = array(); 
            $defaultsDataArr['taxCodeInland'] = '01';
        }
        
        // set form defaults
        $defaultsDataArr['scalePrice']['NEW_QTY'] = array(); // add empty scale price
        foreach ($defaultsDataArr['scalePrice'] as $scalePriceQty=>$scalePriceArray) {
            // set special offer price date defaults for new scale prices
            if (!is_array($scalePriceArray['specialOfferGroup']['specialOfferStartDate']) || implode('', $scalePriceArray['specialOfferGroup']['specialOfferStartDate']) == '') {
                $defaultsDataArr['scalePrice'][$scalePriceQty]['specialOfferGroup']['specialOfferStartDate'] = array('d'=>'01', 'm'=>'01', 'Y'=>strval(date('Y')));
            }
            if (!is_array($scalePriceArray['specialOfferGroup']['specialOfferEndDate']) || implode('', $scalePriceArray['specialOfferGroup']['specialOfferEndDate']) == '') {
                $defaultsDataArr['scalePrice'][$scalePriceQty]['specialOfferGroup']['specialOfferEndDate'] = array('d'=>'31', 'm'=>'12', 'Y'=>strval(date('Y')));
            }
        }
        $form->setDefaults($defaultsDataArr);
        
        
        // build form: add TYPO3 form buttons
        $form->addElement('static', '', '', $buttons);
        
        // build form: article basic data
        $form->addElement('header', 'artHeader1', $this->ll('artForm_header1'));
        $form->addElement('text', 'artNo', $this->ll('artForm_artNo'), array('maxlength'=>'120', 'class'=>$this->extPrefix.'_inputTextDefault'));
        $form->addElement('text', 'match1', $this->ll('artForm_match1'), array('maxlength'=>'255', 'class'=>$this->extPrefix.'_inputTextDefault'));
        $form->addElement('text', 'match2', $this->ll('artForm_match2'), array('maxlength'=>'60', 'class'=>$this->extPrefix.'_inputTextDefault'));
        $form->addElement('advcheckbox', 'passiveFlag', $this->ll('artForm_passiveFlag'), '', '', array('0', '1'));
        $form->addElement('select', 'taxCodeInland', $this->ll('artForm_taxCodeInland'), $this->returnTaxSelectorArray());
        $webAddressFieldsArr[] = HTML_QuickForm::createElement('text', 'webAddress', '', array('maxlength'=>self::$webAddressFieldLength, 'class'=>$this->extPrefix.'_inputTextDefault'));
        $webAddressFieldsArr[] = HTML_QuickForm::createElement('static', 'webAddressInfo',  '', '['.$this->ll('artForm_webAddressInfo').']');
        $form->addGroup($webAddressFieldsArr, 'webAddressGroup', $this->ll('artForm_webAddress'), '<br />');
        $form->addElement('advcheckbox', 'grossPriceFlag', $this->ll('artForm_grossPriceFlag'), '', '', array('0', '1'));
        $form->addRule('artNo', sprintf($this->ll('qf_ruleRequired'), $this->ll('artForm_artNo')), 'required', '', 'client');
        $form->addRule('match1', sprintf($this->ll('qf_ruleRequired'), $this->ll('artForm_match1')), 'required', '', 'client');
        $form->addRule('taxCodeInland', sprintf($this->ll('qf_ruleRequired'), $this->ll('artForm_taxCodeInland')), 'required', '', 'client');
        $form->addRule('taxCodeInland', sprintf($this->ll('qf_ruleNumeric'), $this->ll('artForm_taxCodeInland')), 'numeric', '', 'client');
        
        // build form: price scales (jQuery GUI)
            // add possibly existent new scale price blocks (added by jQuery) to form 
        $postScalePriceArray = (array)t3lib_div::GPvar('scalePrice', 1);
        $mergedScalePriceArray = t3lib_div::array_merge($postScalePriceArray, $defaultsDataArr['scalePrice']);
            // prepare quantity scale element group
        $scaleFieldsArr = array();
        $scaleSelectorOptionsArr = array();
        foreach ($mergedScalePriceArray as $scalePriceQty=>$scalePriceArray) {
            if (is_integer($scalePriceQty) && $postScalePriceArray[$scalePriceQty]['quantityScaleDeleted'] != 1) {
                $scaleSelectorOptionsArr[$scalePriceQty] = $scalePriceQty;
            }
        }
        $scaleFieldsArr[] = HTML_QuickForm::createElement('select', 'quantityScale', '', $scaleSelectorOptionsArr, 
                                                           array('size'=>'5', 'id'=>'scaleSelector', 'class'=>$this->extPrefix.'_selectQuantityScale'));
        $scaleFieldsArr[] = HTML_QuickForm::createElement('button', 'quantityScaleDeleteButton', $this->ll('artForm_quantityScaleDeleteButton'),
                                                           array('id'=>'quantityScaleDeleteButton'));
        $scaleFieldsArr[] = HTML_QuickForm::createElement('text', 'quantityScaleNew', '', 
                                                           array('maxlength'=>'5', 'class'=>$this->extPrefix.'_newQuantityScale'));
        $scaleFieldsArr[] = HTML_QuickForm::createElement('button', 'quantityScaleNewButton', $this->ll('artForm_quantityScaleNewButton'),
                                                           array('id'=>'quantityScaleNewButton'));
            // add header and scale selector
        $form->addElement('header', 'artHeader2', $this->ll('artForm_header2'));
        $form->addGroup($scaleFieldsArr, 'quantityScaleGroup', $this->ll('artForm_quantityScaleGroup'), array(' ', ' / '.$this->ll('artForm_quantityScaleNew').' ', ' '));
            // add scale price blocks
        foreach ($mergedScalePriceArray as $scalePriceQty=>$scalePriceArray) {
            if ($postScalePriceArray[$scalePriceQty]['quantityScaleDeleted'] != 1) {
                $this->buildScalePriceFormBlock($form, $scalePriceQty);
            }
        }
        
        // build form: button row 
        $form->addElement('header', 'seperator', '');
        $form->addElement('static', '', '', $buttons);
        $form->addElement('static', 'requiredNote',  '', $form->getRequiredNote());
        
        
        // init tceforms (added by Fabrizio Branca 2007-12)
        $tceforms = t3lib_div::makeInstance('t3lib_TCEforms'); /* @var $tceforms t3lib_TCEforms */
        $tceforms->initDefaultBEmode();
        $tceforms->backPath = $GLOBALS['BACK_PATH'];
        $table = 'tx_ptgsaadmin_virtualarticle';
        $row = array('pid' => 0, 'uid' => 0);
        $tceforms->cachedTSconfig[$table.':'.$row['uid']] = array('disabled' => true); // to suppress warnings, see t3lib_TCEforms->setTSconfig
            // enable/disable RTE
        $tceforms->disableRTE = tx_pttools_sessionStorageAdapter::read('disableRte');
        $form->setDefaults(array('disableRte' => $tceforms->disableRTE));
        
        
        // HOOK: modify form after first section (added by Fabrizio Branca 2007-12)  # TODO: (Fabrizio) merge with completeForm Hook below (is it possible to add new elements at any arbitrary position)?
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['pt_gsaadmin']['module2_hooks']['returnArticleForm_formAfterFirstSection'])) {
            foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['pt_gsaadmin']['module2_hooks']['returnArticleForm_formAfterFirstSection'] as $_funcRef) {
                $_params = array('formObj' => $form, 
                                 'defaultsDataArr' => $defaultsDataArr,
                                 'tceformsObj' => $tceforms,
                                 'table' => $table,
                                 'row' => $row);
                t3lib_div::callUserFunction($_funcRef, $_params, $this, '');
            }
        }               


        // build form: article images (added by Fabrizio Branca 2007-12)
        $form->addElement('header', 'artHeader3', $this->ll('artForm_header7'));
            // field: image
        $row['image'] = $defaultsDataArr['image'];
        $editForm = $tceforms->getSoloField($table, $row, 'image');
        $form->addElement('static', '', $this->ll('artForm_images'), $editForm);
        
        // build form: article texts
        $form->addElement('header', 'artHeader3', $this->ll('artForm_header3'));
            // field: defText
        $row['deftext'] = $defaultsDataArr['defText'];
        $editForm = $tceforms->getSoloField($table, $row, 'deftext');
        $form->addElement('static', '', $this->ll('artForm_defText'), $editForm);
            // field: altText
        $row['alttext'] = $defaultsDataArr['altText'];
        $editForm = $tceforms->getSoloField($table, $row, 'alttext');
        $form->addElement('static', '', $this->ll('artForm_altText'), $editForm);
        
        // build form: calculation
        $form->addElement('header', 'artHeader4', $this->ll('artForm_header4'));
        $form->addElement('text', 'fixedCost1', $this->ll('artForm_fixedCost1'), array('maxlength'=>'19', 'class'=>$this->extPrefix.'_inputTextPrice'));
        $form->addElement('text', 'fixedCost2', $this->ll('artForm_fixedCost2'), array('maxlength'=>'19', 'class'=>$this->extPrefix.'_inputTextPrice'));
        $form->addRule('fixedCost1', sprintf($this->ll('qf_ruleNumeric'), $this->ll('artForm_fixedCost1')), 'numeric', '', 'client');
        $form->addRule('fixedCost2', sprintf($this->ll('qf_ruleNumeric'), $this->ll('artForm_fixedCost2')), 'numeric', '', 'client');
        
        // build form: additional fields
        $form->addElement('header', 'artHeader5', $this->ll('artForm_header5'));
        $form->addElement('text', 'userField01', $this->ll('artForm_userField01'), array('maxlength'=>'255', 'class'=>$this->extPrefix.'_inputTextDefault'));
        $form->addElement('text', 'userField02', $this->ll('artForm_userField02'), array('maxlength'=>'255', 'class'=>$this->extPrefix.'_inputTextDefault'));
        $form->addElement('text', 'userField03', $this->ll('artForm_userField03'), array('maxlength'=>'255', 'class'=>$this->extPrefix.'_inputTextDefault'));
        $form->addElement('text', 'userField04', $this->ll('artForm_userField04'), array('maxlength'=>'255', 'class'=>$this->extPrefix.'_inputTextDefault'));
        $form->addElement('text', 'userField05', $this->ll('artForm_userField05'), array('maxlength'=>'255', 'class'=>$this->extPrefix.'_inputTextDefault'));
        $form->addElement('text', 'userField06', $this->ll('artForm_userField06'), array('maxlength'=>'255', 'class'=>$this->extPrefix.'_inputTextDefault'));
        $form->addElement('text', 'userField07', $this->ll('artForm_userField07'), array('maxlength'=>'255', 'class'=>$this->extPrefix.'_inputTextDefault'));
        $form->addElement('text', 'userField08', $this->ll('artForm_userField08'), array('maxlength'=>'255', 'class'=>$this->extPrefix.'_inputTextDefault'));
        
        // build form: barcode
        $form->addElement('header', 'artHeader6', $this->ll('artForm_header6'));
        $form->addElement('text', 'eanNumber', $this->ll('artForm_eanNumber'), array('maxlength'=>'40', 'class'=>$this->extPrefix.'_inputTextDefault'));
        
        // build form: button row & RTE checkbox (added by Fabrizio Branca 2007-12)
        $form->addElement('header', 'seperator', '');
        $form->addElement('static', '', '', $buttons);
        $form->addElement('advcheckbox', 'disableRte', $this->ll('artForm_disableRte'), '', '', array('0', '1'));
        
        // apply filters
        $form->applyFilter('__ALL__', 'trim');
        
        // HOOK: modify complete form (added by Fabrizio Branca 2007-12)
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['pt_gsaadmin']['module2_hooks']['returnArticleForm_completeForm'])) {
            foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['pt_gsaadmin']['module2_hooks']['returnArticleForm_completeForm'] as $_funcRef) {
                $_params = array('formObj' => $form, 
                                 'defaultsDataArr' => $defaultsDataArr,
                                 'tceformsObj' => $tceforms,
                                 'table' => $table,
                                 'row' => $row);
                t3lib_div::callUserFunction($_funcRef, $_params, $this, '');
            }
        } 

        
        // finish tceforms (place under the last used tceform field!)  (added by Fabrizio Branca 2007-12)
        $this->jsArray['tceforms_printNeededJSFunctions_top'] = $tceforms->printNeededJSFunctions_top();
        $form->addElement('static', '', '', $tceforms->printNeededJSFunctions());
        $form->setAttribute('onsubmit', "TBE_EDITOR.checkSubmit(1); ".$form->getAttribute('onsubmit'));
        
        #trace($form);
        return $form;
        
    }
    
    /**
     * Creates and returns an selectorbox array for tax codes
     *
     * @param       void
     * @return      array  selectorbox array for tax codes (key=tax code, value=tax code description)
     * @author      Rainer Kuhn <kuhn@punkt.de>
     * @since       2008-02-19 (based on code from returnArticleForm() since 2007-10-11)
     */
    protected function returnTaxSelectorArray() {
        
        $taxSelectorArr = array();
        $taxCodeArr = tx_ptgsashop_taxAccessor::getInstance()->selectTaxCodes();
        
        if (is_array($taxCodeArr)) {
            foreach ($taxCodeArr as $taxCode=>$taxDataArr) {
                $selectorOption = $taxCode.': ';
                if (!empty($taxDataArr['taxNote'])) {
                    $selectorOption .= $taxDataArr['taxNote'].' ';
                }
                $selectorOption .= '('.(string)$taxDataArr['taxRate'].'%)';
                $taxSelectorArr[$taxCode] = $selectorOption;
            }
        }
        
        return $taxSelectorArr;
        
    }
    
    
    /**
     * Creates an article form scale price elements block and adds it to the article form
     *
     * @param       HTML_QuickForm  article form object (passed by reference)
     * @param       integer     scale price quantity to create it's form block for
     * @return      void
     * @author      Rainer Kuhn <kuhn@punkt.de>
     * @since       2008-02-28
     */
    protected function buildScalePriceFormBlock(HTML_QuickForm $form, $scalePriceQty) {
            
        $specialOfferFieldsArr = array();
        $specialOfferFieldsArr[] = HTML_QuickForm::createElement('advcheckbox', 'specialOfferFlag', '', '', 
                                                                 array('class'=>$this->extPrefix.'_scalePriceElem'), array('0', '1'));
        $specialOfferFieldsArr[] = HTML_QuickForm::createElement('text', 'specialOfferRetailPrice', '', 
                                                                 array('maxlength'=>'19', 'class'=>$this->extPrefix.'_scalePriceElem'.' '.$this->extPrefix.'_inputTextPrice'));
        $specialOfferFieldsArr[] = HTML_QuickForm::createElement('date', 'specialOfferStartDate', '', 
                                                                 array('language'=>'en', 'format'=>'dmY', 'minYear'=>strval(date('Y')-2), 'maxYear'=>strval(date('Y')+2)), 
                                                                 array('class'=>$this->extPrefix.'_scalePriceElem'));
        $specialOfferFieldsArr[] = HTML_QuickForm::createElement('date', 'specialOfferEndDate', '', 
                                                                 array('language'=>'en', 'format'=>'dmY', 'minYear'=>strval(date('Y')-2), 'maxYear'=>strval(date('Y')+2)), 
                                                                 array('class'=>$this->extPrefix.'_scalePriceElem'));
        
        $form->addElement('text', 'scalePrice['.$scalePriceQty.'][scalePriceQuantity]', $this->ll('artForm_scalePriceQuantity'), 
                          array('maxlength'=>'19', 'readonly'=>'readonly', 'value'=>$scalePriceQty, 
                                'id'=>'scalePrice_'.$scalePriceQty, 'class'=>$this->extPrefix.'_scalePriceElem'.' '.$this->extPrefix.'_scalePriceQty'.' '.$this->extPrefix.'_inputTextPrice'));
                                 // id 'scalePrice_[n]' and classes 'tx_ptgsaadmin_scalePriceElem' & 'tx_ptgsaadmin_scalePriceQty' are used for jQuery manipulations
        $form->addElement('text', 'scalePrice['.$scalePriceQty.'][basicRetailPriceCategory1]', $this->ll('artForm_basicRetailPriceCategory1'), 
                          array('maxlength'=>'19', 'class'=>$this->extPrefix.'_scalePriceElem'.' '.$this->extPrefix.'_inputTextPrice'));
        $form->addElement('text', 'scalePrice['.$scalePriceQty.'][basicRetailPriceCategory2]', $this->ll('artForm_basicRetailPriceCategory2'), 
                          array('maxlength'=>'19', 'class'=>$this->extPrefix.'_scalePriceElem'.' '.$this->extPrefix.'_inputTextPrice'));
        $form->addElement('text', 'scalePrice['.$scalePriceQty.'][basicRetailPriceCategory3]', $this->ll('artForm_basicRetailPriceCategory3'), 
                          array('maxlength'=>'19', 'class'=>$this->extPrefix.'_scalePriceElem'.' '.$this->extPrefix.'_inputTextPrice'));
        $form->addElement('text', 'scalePrice['.$scalePriceQty.'][basicRetailPriceCategory4]', $this->ll('artForm_basicRetailPriceCategory4'), 
                          array('maxlength'=>'19', 'class'=>$this->extPrefix.'_scalePriceElem'.' '.$this->extPrefix.'_inputTextPrice'));
        $form->addElement('text', 'scalePrice['.$scalePriceQty.'][basicRetailPriceCategory5]', $this->ll('artForm_basicRetailPriceCategory5'), 
                          array('maxlength'=>'19', 'class'=>$this->extPrefix.'_scalePriceElem'.' '.$this->extPrefix.'_inputTextPrice'));
        $form->addGroup($specialOfferFieldsArr, 'scalePrice['.$scalePriceQty.'][specialOfferGroup]', $this->ll('artForm_specialOfferGroup'), 
                        array(' '.$this->ll('artForm_specialOfferRetailPrice').': ', '<br />'.$this->ll('artForm_specialOfferStartDate').' ', ' '.$this->ll('artForm_specialOfferEndDate').' '));
        $form->addElement('hidden', 'scalePrice['.$scalePriceQty.'][quantityScaleDeleted]', '0', array('id'=>'quantityScaleDeleted_'.$scalePriceQty));
                        
        $form->addRule('scalePrice['.$scalePriceQty.'][scalePriceQuantity]', sprintf($this->ll('qf_ruleRequiredScale'), $this->ll('artForm_scalePriceQuantity'), $scalePriceQty), 'required', '', 'server');
        if ($scalePriceQty != 'NEW_QTY') {
            $form->addRule('scalePrice['.$scalePriceQty.'][scalePriceQuantity]', sprintf($this->ll('qf_ruleNumeric'), $this->ll('artForm_scalePriceQuantity')), 'numeric', '', 'server');
            $form->addRule('scalePrice['.$scalePriceQty.'][basicRetailPriceCategory1]', sprintf($this->ll('qf_ruleRequiredScale'), $this->ll('artForm_basicRetailPriceCategory1'), $scalePriceQty), 'required', '', 'server');
        }
        $form->addRule('scalePrice['.$scalePriceQty.'][basicRetailPriceCategory1]', sprintf($this->ll('qf_ruleNumeric'), $this->ll('artForm_basicRetailPriceCategory1')), 'numeric', '', 'server');
        $form->addRule('scalePrice['.$scalePriceQty.'][basicRetailPriceCategory2]', sprintf($this->ll('qf_ruleNumeric'), $this->ll('artForm_basicRetailPriceCategory2')), 'numeric', '', 'server');
        $form->addRule('scalePrice['.$scalePriceQty.'][basicRetailPriceCategory3]', sprintf($this->ll('qf_ruleNumeric'), $this->ll('artForm_basicRetailPriceCategory3')), 'numeric', '', 'server');
        $form->addRule('scalePrice['.$scalePriceQty.'][basicRetailPriceCategory4]', sprintf($this->ll('qf_ruleNumeric'), $this->ll('artForm_basicRetailPriceCategory4')), 'numeric', '', 'server');
        $form->addRule('scalePrice['.$scalePriceQty.'][[basicRetailPriceCategory5]', sprintf($this->ll('qf_ruleNumeric'), $this->ll('artForm_basicRetailPriceCategory5')), 'numeric', '', 'server');
        $form->addGroupRule('scalePrice['.$scalePriceQty.'][specialOfferGroup]', array(
            'specialOfferRetailPrice' => array(
                array(sprintf($this->ll('qf_ruleNumeric'), ($this->ll('artForm_specialOfferGroup').'/'.$this->ll('artForm_specialOfferRetailPrice'))),  'numeric', '', 'server')
            )  
        ));
            
    }
    
    
    
} // end class



/*******************************************************************************
 *   TYPO3 XCLASS INCLUSION (for class extension/overriding)
 ******************************************************************************/
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsaadmin/mod_articles/class.tx_ptgsaadmin_module2.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsaadmin/mod_articles/class.tx_ptgsaadmin_module2.php']);
}

?>