<?
use Bitrix\Main\Localization\Loc;

AddEventHandler('main', 'OnBuildGlobalMenu', 'OnBuildGlobalMenuHandlerSmartSeo');
function OnBuildGlobalMenuHandlerSmartSeo(&$arGlobalMenu, &$arModuleMenu){
	if(!defined('ASPRO_SMARTSEO_MENU_INCLUDED')){
		define('ASPRO_SMARTSEO_MENU_INCLUDED', true);

		IncludeModuleLangFile(__FILE__);
		$moduleID = 'aspro.smartseo';

		$GLOBALS['APPLICATION']->SetAdditionalCss("/bitrix/css/".$moduleID."/menu.css");

		if($GLOBALS['APPLICATION']->GetGroupRight($moduleID) >= 'R'){
			$arMenu = array(
                'menu_id' => 'global_menu_aspro_smartseo',
                'text' => Loc::getMessage('ASPRO_SMARTSEO__MENU__ROOT_TEXT'),
                'title' => Loc::getMessage('ASPRO_SMARTSEO__MENU__ROOT_TITLE'),
                'sort' => 1000,
                'items_id' => 'global_menu_aspro_smartseo_items',
                'icon' => 'menu_smartseo_main',
                'items' => array(
                    array(
                        'text' => Loc::getMessage('ASPRO_SMARTSEO__MENU__FILTER_RULES_TEXT'),
                        'title' => Loc::getMessage('ASPRO_SMARTSEO__MENU__FILTER_RULES_TITLE'),
                        'sort' => 10,
                        'url' => '/bitrix/admin/'.$moduleID.'_smartseo.php?route=filter_rules/list&lang='.urlencode(LANGUAGE_ID),
                        'more_url' => array(
                            '/bitrix/admin/'.$moduleID.'_smartseo.php?route=filter_rule_detail/detail&lang='.urlencode(LANGUAGE_ID),
                            '/bitrix/admin/'.$moduleID.'_smartseo.php?route=filter_section/detail&lang='.urlencode(LANGUAGE_ID),
                        ),
                        'icon' => '',
                        'page_icon' => '',
                        'items_id' => 'menu_aspro_smartseo_rule_filter',
                    ),
                    array(
                        'text' => Loc::getMessage('ASPRO_SMARTSEO__MENU__NOINDEX_RULES_TEXT'),
                        'title' => Loc::getMessage('ASPRO_SMARTSEO__MENU__NOINDEX_RULES_TITLE'),
                        'sort' => 10,
                        'url' => '/bitrix/admin/'.$moduleID . '_smartseo.php?route=noindex_rules/list&lang='.urlencode(LANGUAGE_ID),
                        'more_url' => [
                            '/bitrix/admin/'.$moduleID . '_smartseo.php?route=noindex_rule_detail/detail&lang='.urlencode(LANGUAGE_ID),
                        ],
                        'icon' => '',
                        'page_icon' => '',
                        'items_id' => 'menu_aspro_smartseo_noindex_rules',
                    ),
                    array(
                        'text' => Loc::getMessage('ASPRO_SMARTSEO__MENU__SEOTEXT_TEXT'),
                        'title' => Loc::getMessage('ASPRO_SMARTSEO__MENU__SEOTEXT_TITLE'),
                        'sort' => 10,
                        'url' => '/bitrix/admin/'.$moduleID.'_smartseo.php?route=seo_text/list&lang='.urlencode(LANGUAGE_ID),
                        'more_url' => array(
                            '/bitrix/admin/'.$moduleID.'_smartseo.php?route=seo_text_section/detail&lang='.urlencode(LANGUAGE_ID),
                            '/bitrix/admin/'.$moduleID.'_smartseo.php?route=seo_text_element/detail&lang='.urlencode(LANGUAGE_ID),
                        ),
                        'icon' => '',
                        'page_icon' => '',
                        'items_id' => 'menu_aspro_smartseo_seo_text',
                    ),
                    array(
                        'text' => Loc::getMessage('ASPRO_SMARTSEO__MENU__SITEMAP_TEXT'),
                        'title' => Loc::getMessage('ASPRO_SMARTSEO__MENU__SITEMAP_TITLE'),
                        'sort' => 10,
                        'url' => '/bitrix/admin/'.$moduleID.'_smartseo.php?route=sitemap/list&lang='.urlencode(LANGUAGE_ID),
                        'more_url' => array(
                            '/bitrix/admin/'.$moduleID.'_smartseo.php?route=sitemap_detail/detail&lang='.urlencode(LANGUAGE_ID),
                        ),
                        'icon' => '',
                        'page_icon' => '',
                        'items_id' => 'menu_aspro_smartseo_sitemap',
                    ),
                    array(
                        'text' => Loc::getMessage('ASPRO_SMARTSEO__MENU__SETTINGS_TEXT'),
                        'title' => Loc::getMessage('ASPRO_SMARTSEO__MENU__SETTINGS_TITLE'),
                        'sort' => 10,
                        'icon' => '',
                        'page_icon' => '',
                        'items_id' => 'menu_aspro_smartseo_settings',
                        'items' => array(
                            array(
                                'text' => Loc::getMessage('ASPRO_SMARTSEO__MENU__SITE_SETTINGS_TEXT'),
                                'title' => Loc::getMessage('ASPRO_SMARTSEO__MENU__SITE_SETTINGS_TITLE'),
                                'sort' => 10,
                                'url' => '/bitrix/admin/'.$moduleID.'_smartseo.php?route=setting/sites&lang='.urlencode(LANGUAGE_ID),
                                'more_url' => array(),
                                'icon' => '',
                                'page_icon' => '',
                                'items_id' => 'menu_aspro_smartseo_site_settings',
                            ),
                            array(
                                'text' => Loc::getMessage('ASPRO_SMARTSEO__MENU__GENERAL_SETTINGS_TEXT'),
                                'title' => Loc::getMessage('ASPRO_SMARTSEO__MENU__GENERAL_SETTINGS_TITLE'),
                                'sort' => 10,
                                'url' => '/bitrix/admin/'.$moduleID.'_smartseo.php?route=setting/general&lang='.urlencode(LANGUAGE_ID),
                                'more_url' => array(),
                                'icon' => '',
                                'page_icon' => '',
                                'items_id' => 'menu_aspro_smartseo_general_settings',
                            ),
                        )
                    ),
                    array(
                        'text' => Loc::getMessage('ASPRO_SMARTSEO__MENU__MODULE_UPDATE'),
                        'title' => Loc::getMessage('ASPRO_SMARTSEO__MENU__MODULE_UPDATE'),
                        'sort' => 10,
                        'url' => '/bitrix/admin/'.$moduleID.'_update.php?lang='.urlencode(LANGUAGE_ID),
                        'more_url' => array(),
                        'icon' => '',
                        'page_icon' => '',
                        'items_id' => 'menu_aspro_smartseo_update',
                    ),
                ),
            );
            
            

			if(!isset($arGlobalMenu['global_menu_aspro'])){
				$arGlobalMenu['global_menu_aspro'] = array(
					'menu_id' => 'global_menu_aspro',
					'text' => Loc::getMessage('ASPRO_SMARTSEO_GLOBAL_ASPRO_MENU_TEXT'),
					'title' => Loc::getMessage('ASPRO_SMARTSEO_GLOBAL_ASPRO_MENU_TITLE'),
					'sort' => 1000,
					'items_id' => 'global_menu_aspro_items',
				);
			}

			$arGlobalMenu['global_menu_aspro']['items'][$moduleID] = $arMenu;
		}
	}
}
?>