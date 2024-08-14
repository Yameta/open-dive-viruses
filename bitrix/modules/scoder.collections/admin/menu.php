<?
use Bitrix\Main\Loader; 
use Bitrix\Main\Config\Option;

IncludeModuleLangFile(__FILE__);

$module_rights = $APPLICATION->GetGroupRight("scoder.collections");

if($module_rights > "D")
{
	$arItems = Array(
		Array(
			"text" => GetMessage("SCODER_COLLECTIONS_MENU_ITEMS"),
			"url" => "scoder_collections_item.php?lang=" . LANG,
			"title" => GetMessage("SCODER_COLLECTIONS_MENU_ITEMS_TITLE"),
			"more_url" => array("scoder_collections_item_edit.php"),
		),
		
	);

	$aMenu = array(
		"parent_menu" => "global_menu_services",
		"section" => "scoder.collections",
		"sort" => 20,
		"text" => GetMessage("SCODER_COLLECTIONS_MENU_SECT"),
		"title" => GetMessage("SCODER_COLLECTIONS_MENU_SECT_TITLE"),
		"icon" => "scoder_collections_menu_icon",
		"page_icon" => "scoder_collections_page_icon",
		"items_id" => "menu_scoder_collections",
		"items" => $module_rights >= 'R' ? $arItems : Array(),
	);
	return $aMenu;
}
return false;
?>