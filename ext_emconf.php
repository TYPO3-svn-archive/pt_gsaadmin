<?php

########################################################################
# Extension Manager/Repository config file for ext: "pt_gsaadmin"
#
# Auto generated 10-12-2009 17:53
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'GSA Admin',
	'description' => 'TYPO3 backend interfaces for administrating GSA Shop data (and data of some related GSA extensions) for GSA usage as a TYPO3 standalone version without the ERP system.',
	'category' => 'General Shop Applications',
	'author' => 'Rainer Kuhn, Fabrizio Branca',
	'author_email' => 't3extensions@punkt.de',
	'shy' => '',
	'dependencies' => 'cms,smarty,jquery,pt_tools,pt_gsasocket,pt_gsashop',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => 'mod_main,mod_articles,mod_dispatch,mod_taxrates',
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => 'punkt.de GmbH',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'version' => '0.1.1dev',
	'constraints' => array(
		'depends' => array(
			'cms' => '',
			'smarty' => '1.2.0-',
			'jquery' => '1.2.2-',
			'pt_tools' => '1.0.0-',
			'pt_gsasocket' => '1.0.0-',
			'pt_gsashop' => '1.0.0-',
			'php' => '5.1.0-0.0.0',
			'typo3' => '4.1.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
			'PEAR HTML_QuickForm (THIS IS JUST A HINT, please ignore if your server is correctly configured)' => '',
		),
	),
	'_md5_values_when_last_written' => 'a:60:{s:10:".cvsignore";s:4:"130f";s:8:".project";s:4:"0b1a";s:9:"ChangeLog";s:4:"1e94";s:10:"README.txt";s:4:"d87d";s:21:"ext_conf_template.txt";s:4:"00ea";s:12:"ext_icon.gif";s:4:"4546";s:14:"ext_tables.php";s:4:"e65e";s:14:"ext_tables.sql";s:4:"922c";s:22:"tca_virtual_tables.php";s:4:"d94d";s:14:"doc/DevDoc.txt";s:4:"9b41";s:23:"doc/DevDoc_Internal.txt";s:4:"bac8";s:44:"doc/class.tx_EXTKEY_ptgsaadmin_hooks.php.txt";s:4:"14f3";s:14:"doc/manual.sxw";s:4:"7894";s:19:"doc/wizard_form.dat";s:4:"2109";s:20:"doc/wizard_form.html";s:4:"56a6";s:44:"mod_articles/class.tx_ptgsaadmin_module2.php";s:4:"1c6c";s:22:"mod_articles/clear.gif";s:4:"cc11";s:21:"mod_articles/conf.php";s:4:"704c";s:22:"mod_articles/index.php";s:4:"43fa";s:26:"mod_articles/locallang.xml";s:4:"688e";s:30:"mod_articles/locallang_mod.xml";s:4:"4601";s:27:"mod_articles/moduleicon.gif";s:4:"37be";s:44:"mod_dispatch/class.tx_ptgsaadmin_module3.php";s:4:"b669";s:22:"mod_dispatch/clear.gif";s:4:"cc11";s:21:"mod_dispatch/conf.php";s:4:"9ac7";s:22:"mod_dispatch/index.php";s:4:"39be";s:26:"mod_dispatch/locallang.xml";s:4:"e34a";s:30:"mod_dispatch/locallang_mod.xml";s:4:"508d";s:27:"mod_dispatch/moduleicon.gif";s:4:"4eb0";s:18:"mod_main/clear.gif";s:4:"cc11";s:17:"mod_main/conf.php";s:4:"6000";s:26:"mod_main/locallang_mod.xml";s:4:"3b33";s:23:"mod_main/moduleicon.gif";s:4:"5837";s:44:"mod_taxrates/class.tx_ptgsaadmin_module4.php";s:4:"767e";s:22:"mod_taxrates/clear.gif";s:4:"cc11";s:21:"mod_taxrates/conf.php";s:4:"a4a3";s:22:"mod_taxrates/index.php";s:4:"5032";s:26:"mod_taxrates/locallang.xml";s:4:"d9e5";s:30:"mod_taxrates/locallang_mod.xml";s:4:"3b98";s:27:"mod_taxrates/moduleicon.gif";s:4:"4bb2";s:43:"res/class.tx_ptgsaadmin_articleAccessor.php";s:4:"5ed1";s:38:"res/class.tx_ptgsaadmin_submodules.php";s:4:"116d";s:35:"res/class.tx_ptgsaadmin_taxRate.php";s:4:"f982";s:43:"res/class.tx_ptgsaadmin_taxRateAccessor.php";s:4:"3bae";s:45:"res/class.tx_ptgsaadmin_taxRateCollection.php";s:4:"4da8";s:22:"res/css/submodules.css";s:4:"af72";s:19:"res/img/article.png";s:4:"f854";s:27:"res/img/article_passive.png";s:4:"4549";s:22:"res/js/mod_articles.js";s:4:"95dd";s:39:"res/list/class.tx_ptgsaadmin_button.php";s:4:"8e0f";s:37:"res/list/class.tx_ptgsaadmin_cell.php";s:4:"b61a";s:37:"res/list/class.tx_ptgsaadmin_list.php";s:4:"19ac";s:36:"res/list/class.tx_ptgsaadmin_row.php";s:4:"88c5";s:24:"res/smarty_cfg/dummy.txt";s:4:"d41d";s:28:"res/smarty_tpl/list.tpl.html";s:4:"066c";s:36:"res/smarty_tpl/mod_articles.tpl.html";s:4:"e9b0";s:20:"static/constants.txt";s:4:"0463";s:16:"static/setup.txt";s:4:"d174";s:17:".cache/.dataModel";s:4:"de84";s:21:".cache/.wsdlDataModel";s:4:"3c57";}',
	'suggests' => array(
	),
);

?>