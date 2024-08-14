<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if(!CModule::IncludeModule("iblock")) return;
if(!CModule::IncludeModule("aspro.max")) return;

if(!defined("WIZARD_SITE_ID")) return;
if(!defined("WIZARD_SITE_DIR")) return;
if(!defined("WIZARD_SITE_PATH")) return;
if(!defined("WIZARD_TEMPLATE_ID")) return;
if(!defined("WIZARD_TEMPLATE_ABSOLUTE_PATH")) return;
if(!defined("WIZARD_THEME_ID")) return;

// iblocks ids
$catalogIBlockID = CMaxCache::$arIBlocks[WIZARD_SITE_ID]["aspro_max_catalog"]["aspro_max_catalog"][0];
$banners_catalogIBlockID = CMaxCache::$arIBlocks[WIZARD_SITE_ID]["aspro_max_adv"]["aspro_max_banners_catalog"][0];
$skuIblockID = CMaxCache::$arIBlocks[WIZARD_SITE_ID]["aspro_max_catalog"]["aspro_max_sku"][0];

if ($catalogIBlockID) {
	$ib = new CIBlock;
	$ib->Update(
		$catalogIBlockID,
		[
			'INDEX_ELEMENT' => 'Y',
			'INDEX_SECTION' => 'Y',
		]
	);
}

if ($banners_catalogIBlockID) {
	$ib = new CIBlock;
	$ib->Update(
		$banners_catalogIBlockID,
		[
			'INDEX_ELEMENT' => 'N',
			'INDEX_SECTION' => 'N',
		]
	);
}

//set option for props group
$strOptionGroup = "";
if($catalogIBlockID){
	$strOptionGroup = $catalogIBlockID;
	if($skuIblockID){
		$strOptionGroup = $catalogIBlockID . "," . $skuIblockID;
	}
}
\Bitrix\Main\Config\Option::set("aspro.max", "ASPRO_PROPS_GROUP_IBLOCK", $strOptionGroup, WIZARD_SITE_ID);
?>