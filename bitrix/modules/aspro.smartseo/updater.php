<?
// aspro.smartseo 1.0.5 updater
// changed files

// module:
// /admin/update.php - update
// /admin/update_include.php - update
// /admin/views/filter_condition/_form_condition.php - update
// /admin/views/filter_rules/list.php - update
// /admin/views/filter_section/detail.php - update
// /admin/views/noindex_rule/list.php - update
// /admin/views/seo_text/detail_element/_form.php - update
// /admin/views/seo_text/detail_element/partial/condition_control.php - update
// /admin/views/seo_text/list.php - update
// /admin/views/settings/general.php - update
// /classes/admin/Helper.php - update
// /classes/admin/controllers/FilterRulesController.php - update
// /classes/admin/controllers/FilterSectionController.php - update
// /classes/admin/controllers/FilterTagController.php - update
// /classes/admin/controllers/FilterUrlController.php - update
// /classes/admin/controllers/NoindexConditionController.php - update
// /classes/admin/controllers/NoindexRulesController.php - update
// /classes/admin/grids/FilterRuleConditionGrid.php - update
// /classes/admin/grids/FilterRuleUrlGrid.php - update
// /classes/admin/grids/NoindexRuleConditionGrid.php - update
// /classes/admin/settings/SettingSmartseo.php - update
// /classes/admin/ui/FilterRulesAdminUI.php - update
// /classes/engines/SearchEngine.php - update
// /classes/general/Smartseo.php - update
// /classes/general/SmartseoEngine.php - update
// /classes/general/SmartseoTools.php - update
// /lang/en/admin/update.php - update
// /lang/en/admin/views/settings/general.php - update
// /lang/ru/admin/update.php - update
// /lang/ru/admin/views/settings/general.php - update
// /lib/condition/ConditionQuery.php - update
// /lib/condition/ConditionResult.php - update
// /lib/condition/ConditionResultHandler.php - update
// /lib/condition/bxcond/catalog_cond.php - update
// /lib/condition/controls/IblockPropertyBuildControls.php - update
// /lib/condition/entities/iblock/Builder.php - update
// /lib/condition/entities/iblock2/Builder.php - update
// /lib/generator/handlers/PropertyUrlHandler.php - update
// /lib/models/smartseofilterconditionurl.php - update
// /lib/models/smartseofiltertag.php - update
// /lib/models/smartseonoindexconditiontable.php - update
// /lib/models/smartseosetting.php - update
// /lib/template/entity/FilterRuleConditionProperty.php - update
// /lib/template/entity/FilterRuleUrl.php - update
// /lib/template/entity/SeoTextElementProperties.php - update
// /lib/thematics.php - update
// /tools/get_property_values.php - update

// css:
// /style.css - update
// /style.min.css - update

// js:

// components:
// /smartseo.tags/class.php - update
// /smartseo.tags/templates/.default/script.js - update
// /smartseo.tags/templates/.default/script.min.js - update

// wizard:


// template:
// 

// public:
// 



use \Bitrix\Main\Config\Option;

require_once __DIR__ . '/functions.php';

define('PARTNER_NAME', 'aspro');
define('MODULE_NAME', 'aspro.smartseo');
define('MODULE_NAME_SHORT', 'smartseo');
define('TEMPLATE_NAME', 'aspro_smartseo');
define('MODULE_PATH', '/bitrix/modules/' . MODULE_NAME);
define('COMPONENT_PATH', '/bitrix/components/' . PARTNER_NAME);
define('ADMIN_JS_PATH', '/bitrix/js/' . MODULE_NAME);
define('ADMIN_CSS_PATH', '/bitrix/css/' . MODULE_NAME);
define('TEMPLATE_PATH', '/bitrix/templates/' . TEMPLATE_NAME);
define('UPDATER_SELF_TEMPLATE_PATH', 'install/wizards/' . PARTNER_NAME . '/' . MODULE_NAME_SHORT . '/site/templates/' . TEMPLATE_NAME);
define('UPDATER_SITE_TEMPLATE_PATH', 'templates/' . TEMPLATE_NAME);
define('CURRENT_VERSION', GetCurVersion($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . MODULE_NAME . '/install/version.php'));
define('NEW_VERSION', GetCurVersion(__DIR__ . '/install/version.php'));

UpdaterLog('START UPDATE ' . CURRENT_VERSION . ' -> ' . NEW_VERSION . PHP_EOL);


// remove old bak files
RemoveOldBakFiles();

// create bak files
foreach ([
	// module
	MODULE_PATH.'/admin/update.php',
	MODULE_PATH.'/admin/update_include.php',
	MODULE_PATH.'/admin/views/filter_condition/_form_condition.php',
	MODULE_PATH.'/admin/views/filter_rules/list.php',
	MODULE_PATH.'/admin/views/filter_section/detail.php',
	MODULE_PATH.'/admin/views/noindex_rule/list.php',
	MODULE_PATH.'/admin/views/seo_text/detail_element/_form.php',
	MODULE_PATH.'/admin/views/seo_text/detail_element/partial/condition_control.php',
	MODULE_PATH.'/admin/views/seo_text/list.php',
	MODULE_PATH.'/admin/views/settings/general.php',
	MODULE_PATH.'/classes/admin/Helper.php',
	MODULE_PATH.'/classes/admin/controllers/FilterRulesController.php',
	MODULE_PATH.'/classes/admin/controllers/FilterSectionController.php',
	MODULE_PATH.'/classes/admin/controllers/FilterTagController.php',
	MODULE_PATH.'/classes/admin/controllers/FilterUrlController.php',
	MODULE_PATH.'/classes/admin/controllers/NoindexConditionController.php',
	MODULE_PATH.'/classes/admin/controllers/NoindexRulesController.php',
	MODULE_PATH.'/classes/admin/grids/FilterRuleConditionGrid.php',
	MODULE_PATH.'/classes/admin/grids/FilterRuleUrlGrid.php',
	MODULE_PATH.'/classes/admin/grids/NoindexRuleConditionGrid.php',
	MODULE_PATH.'/classes/admin/settings/SettingSmartseo.php',
	MODULE_PATH.'/classes/admin/ui/FilterRulesAdminUI.php',
	MODULE_PATH.'/classes/engines/SearchEngine.php',
	MODULE_PATH.'/classes/general/Smartseo.php',
	MODULE_PATH.'/classes/general/SmartseoEngine.php',
	MODULE_PATH.'/classes/general/SmartseoTools.php',
	MODULE_PATH.'/lang/en/admin/update.php',
	MODULE_PATH.'/lang/en/admin/views/settings/general.php',
	MODULE_PATH.'/lang/ru/admin/update.php',
	MODULE_PATH.'/lang/ru/admin/views/settings/general.php',
	MODULE_PATH.'/lib/condition/ConditionQuery.php',
	MODULE_PATH.'/lib/condition/ConditionResult.php',
	MODULE_PATH.'/lib/condition/ConditionResultHandler.php',
	MODULE_PATH.'/lib/condition/bxcond/catalog_cond.php',
	MODULE_PATH.'/lib/condition/controls/IblockPropertyBuildControls.php',
	MODULE_PATH.'/lib/condition/entities/iblock/Builder.php',
	MODULE_PATH.'/lib/condition/entities/iblock2/Builder.php',
	MODULE_PATH.'/lib/generator/handlers/PropertyUrlHandler.php',
	MODULE_PATH.'/lib/models/smartseofilterconditionurl.php',
	MODULE_PATH.'/lib/models/smartseofiltertag.php',
	MODULE_PATH.'/lib/models/smartseonoindexconditiontable.php',
	MODULE_PATH.'/lib/models/smartseosetting.php',
	MODULE_PATH.'/lib/template/entity/FilterRuleConditionProperty.php',
	MODULE_PATH.'/lib/template/entity/FilterRuleUrl.php',
	MODULE_PATH.'/lib/template/entity/SeoTextElementProperties.php',
	MODULE_PATH.'/lib/thematics.php',
	MODULE_PATH.'/tools/get_property_values.php',

	// css
	ADMIN_CSS_PATH .'/style.css',
	ADMIN_CSS_PATH .'/style.min.css',

	// js
	//ADMIN_JS_PATH .'/notice.js',

	// components,
	COMPONENT_PATH.'/smartseo.tags/class.php',
	COMPONENT_PATH.'/smartseo.tags/templates/.default/script.js',
	COMPONENT_PATH.'/smartseo.tags/templates/.default/script.min.js',

	// template

] as $file) {
	CreateBakFile($_SERVER['DOCUMENT_ROOT'] . $file);
}

//UpdaterLog('TEMPLATE_PATH ' . TEMPLATE_PATH);

// update module
//$updater->CopyFiles('install', 'modules/' . MODULE_NAME . '/install');

// update admin section images
//$updater->CopyFiles('install/images', 'images/'.MODULE_NAME);

// update admin section gadget
// $updater->CopyFiles('install/gadgets', 'gadgets');

// update admin page
// $updater->CopyFiles('install/admin', 'admin');

// update admin js
//$updater->CopyFiles('install/js', 'js/'.MODULE_NAME.'/');

// update admin css
//$updater->CopyFiles('install/css', 'css/' . MODULE_NAME . '/');
CopyDirFiles(__DIR__ . '/install/css', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/css/'.MODULE_NAME.'/', true, true);

// update admin tools
// $updater->CopyFiles('install/tools', 'tools/'.MODULE_NAME.'/');

// for actual theme.less
// $updater->CopyFiles('css', 'modules/'.MODULE_NAME.'/css');

// update wizard
//$updater->CopyFiles('install/wizards', 'wizards');

// update components
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/bitrix/components/' . PARTNER_NAME . '/')) {
	// $updater->CopyFiles('install/components', 'components');
	CopyDirFiles(__DIR__ . '/install/components', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/components/', true, true);
}


// current SITEs
// $arSites = GetSites();

// current IBLOCK_IDs
// $arIblocks = GetIBlocks();


// if (IsModuleInstalled(MODULE_NAME)) {
// 	UnRegisterModuleDependences("iblock", "OnAfterIBlockElementDelete", MODULE_NAME, "CMaxCache", "DoIBlockElementAfterDelete");
// }

// is composite enabled
// $compositeMode = IsCompositeEnabled();

// clear all sites cache in some components and dirs (include composite cache)
// ClearAllSitesCacheDirs([
// 	'html_pages',
// 	'cache/js',
// 	'cache/css'
// ]);

// ClearAllSitesCacheComponents([
// 	// PARTNER_NAME.':catalog.delivery.max',
// 	// 'bitrix:catalog.element',
// ]);

// if ($compositeMode) {
// 	$arHTMLCacheOptions = GetCompositeOptions();
// 	EnableComposite($compositeMode === 'AUTO_COMPOSITE', $arHTMLCacheOptions);
// }

UpdaterLog('FINISH UPDATE ' . CURRENT_VERSION . ' -> ' . NEW_VERSION . PHP_EOL);
