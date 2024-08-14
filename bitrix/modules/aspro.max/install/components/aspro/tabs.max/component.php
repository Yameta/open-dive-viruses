<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?\Bitrix\Main\Loader::includeModule('iblock');
$arTabs = $arShowProp = array();
global $USER;

$bUseModuleProps = \Bitrix\Main\Config\Option::get("iblock", "property_features_enabled", "N") === "Y";

$arResult["SHOW_SLIDER_PROP"] = false;
if(strlen($arParams["FILTER_NAME"])<=0 || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["FILTER_NAME"]))
{
	$arrFilter = array();
}
else
{
	$arrFilter = $GLOBALS[$arParams["FILTER_NAME"]] ?? [];
	if(!is_array($arrFilter))
		$arrFilter = array();
}

$arFilter = array("ACTIVE" => "Y", "IBLOCK_ID" => $arParams["IBLOCK_ID"]);
if($arParams["SECTION_ID"])
	$arFilter[]=array("SECTION_ID" => $arParams["SECTION_ID"], "INCLUDE_SUBSECTIONS" => "Y");
elseif($arParams["SECTION_CODE"])
	$arFilter[]=array("SECTION_CODE" => $arParams["SECTION_CODE"], "INCLUDE_SUBSECTIONS" => "Y");

global $arTheme, $bShowCatalogTab;

//if(!isset($arTheme["INDEX_TYPE"]["SUB_PARAMS"][$arTheme["INDEX_TYPE"]["VALUE"]]["CATALOG_TAB"]["VALUE"]))
$bShowCatalogTab = true;

$bCatalogIndex = $bShowCatalogTab;
$arParams["SET_SKU_TITLE"] = (CMax::GetFrontParametrValue("CHANGE_TITLE_ITEM_LIST") == "Y" ? "Y" : "");
$arParams["SHOW_PROPS"] = (CMax::GetFrontParametrValue("SHOW_PROPS_BLOCK") == "Y" ? "Y" : "N");
$arParams["DISPLAY_TYPE"] = "block";
$arParams["TYPE_SKU"] = "TYPE_1";
$arParams["MAX_SCU_COUNT_VIEW"] = CMax::GetFrontParametrValue("MAX_SCU_COUNT_VIEW");
$arParams["USE_CUSTOM_RESIZE_LIST"] = CMax::GetFrontParametrValue("USE_CUSTOM_RESIZE_LIST");
$arParams["IS_COMPACT_SLIDER"] = CMax::GetFrontParametrValue("MOBILE_CATALOG_LIST_ELEMENTS_COMPACT") == 'Y' && CMax::GetFrontParametrValue("MOBILE_COMPACT_LIST_ELEMENTS") == 'slider';
$arParams["CHECK_REQUEST_BLOCK"] = CMax::checkRequestBlock('catalog_tab');
$arParams["USE_FAST_VIEW"] = CMax::GetFrontParametrValue('USE_FAST_VIEW_PAGE_DETAIL');
$arParams["DISPLAY_WISH_BUTTONS"] = CMax::GetFrontParametrValue('CATALOG_DELAY');

$arParams["USE_PERMISSIONS"] = isset($arParams["USE_PERMISSIONS"]) && $arParams["USE_PERMISSIONS"] == "Y";
if(!isset($arParams["GROUP_PERMISSIONS"]) || !is_array($arParams["GROUP_PERMISSIONS"]))
	$arParams["GROUP_PERMISSIONS"] = array(1);

$bUSER_HAVE_ACCESS = !$arParams["USE_PERMISSIONS"];
if($arParams["USE_PERMISSIONS"] && isset($GLOBALS["USER"]) && is_object($GLOBALS["USER"]))
{
	$arUserGroupArray = $USER->GetUserGroupArray();
	foreach($arParams["GROUP_PERMISSIONS"] as $PERM)
	{
		if(in_array($PERM, $arUserGroupArray))
		{
			$bUSER_HAVE_ACCESS = true;
			break;
		}
	}
}

if(CMax::GetFrontParametrValue('SHOW_POPUP_PRICE') == 'Y')
	$arParams['SHOW_POPUP_PRICE'] = 'Y';

$arParams['TYPE_VIEW_BASKET_BTN'] = CMax::GetFrontParametrValue('TYPE_VIEW_BASKET_BTN');
$arParams['REVIEWS_VIEW'] = CMax::GetFrontParametrValue('REVIEWS_VIEW') ==  'EXTENDED';

$arParams['OFFER_TREE_PROPS'] = $arParams['OFFER_TREE_PROPS'] ?? [];
if($arParams['OFFER_TREE_PROPS'])
{
	$keys = array_search('ARTICLE', $arParams['OFFER_TREE_PROPS']);
	if(false !== $keys)
		unset($arParams['OFFER_TREE_PROPS'][$keys]);
}


if(!in_array('DETAIL_PAGE_URL', $arParams['OFFERS_FIELD_CODE']))
	$arParams['OFFERS_FIELD_CODE'][] = 'DETAIL_PAGE_URL';
if(!in_array('NAME', $arParams['OFFERS_FIELD_CODE']))
	$arParams['OFFERS_FIELD_CODE'][] = 'NAME';

$arParams["IS_AJAX"] = CMax::checkAjaxRequest();

$context = \Bitrix\Main\Application::getInstance()->getContext();
$request = $context->getRequest();

if($arParams['IS_AJAX'] == 'Y')
{
	// $APPLICATION->ShowCss();
	// $APPLICATION->ShowHeadScripts();
	$APPLICATION->ShowAjaxHead();

	// not load core.js in CJSCore:Init()
	CJSCore::markExtensionLoaded('core');

	// not load main.popup.bundle.js, ui.font.opensans.css
	$arParams["DISABLE_INIT_JS_IN_COMPONENT"] = "Y";
}

if($bCatalogIndex)
{
	if($arParams["IS_AJAX"] != 'Y')
	{
		$this->IncludeComponentTemplate();
	}
	else
	{
		$arShowProp = CMaxCache::CIBlockPropertyEnum_GetList(Array("sort" => "asc", "id" => "desc", "CACHE" => array("TAG" => CMaxCache::GetPropertyCacheTag($arParams["TABS_CODE"]))), Array("ACTIVE" => "Y", "IBLOCK_ID" => $arParams["IBLOCK_ID"], "CODE" => $arParams["TABS_CODE"]));

		if($arShowProp)
		{
			if($arParams['STORES'])
			{
				foreach($arParams['STORES'] as $key => $store)
				{
					if(!$store)
						unset($arParams['STORES'][$key]);
				}
			}
			$arFilterStores = array();
			global $arRegion;
			if(CMax::GetFrontParametrValue('USE_REGIONALITY') == 'Y')
				$arParams['USE_REGION'] = 'Y';

			$arRegion = CMaxRegionality::getCurrentRegion();
			if($arRegion && $arParams['USE_REGION'] == 'Y')
			{
				if(CMax::GetFrontParametrValue('REGIONALITY_FILTER_ITEM') == 'Y' && CMax::GetFrontParametrValue('REGIONALITY_FILTER_CATALOG') == 'Y'){
					$arFilter['PROPERTY_LINK_REGION'] = $arRegion['ID'];
					CMax::makeElementFilterInRegion($arFilter, $arRegion['ID']);
				}
				
				if($arRegion['LIST_PRICES'])
				{
					if(reset($arRegion['LIST_PRICES']) != 'component')
					{
						$arParams['PRICE_CODE'] = array_keys($arRegion['LIST_PRICES']);
						$arParams['~PRICE_CODE'] = array_keys($arRegion['LIST_PRICES']);
					}
				}
				if($arRegion['LIST_STORES'])
				{
					if(reset($arRegion['LIST_STORES']) != 'component')
					{
						$arParams['STORES'] = $arRegion['LIST_STORES'];
						$arParams['~STORES'] = $arRegion['LIST_STORES'];
					}

					if($arParams["HIDE_NOT_AVAILABLE"] == "Y")
					{
						
						/*
						$arTmpFilter["LOGIC"] = "OR";
						foreach($arParams['STORES'] as $storeID)
						{
							$arTmpFilter[] = array(">CATALOG_STORE_AMOUNT_".$storeID => 0);
						}
						$arFilterStores[] = $arTmpFilter;
						*/
						
						if(CMax::checkVersionModule('18.6.200', 'iblock')){
							$arTmpFilter["LOGIC"] = "OR";
							$arTmpFilter[] = array('TYPE' => array('2','3'));//complects and offers
							$arTmpFilter[] = array(
								'STORE_NUMBER' => $arParams['STORES'],
								'>STORE_AMOUNT' => 0,
							);						
						}
						else{
							if(count($arParams['STORES']) > 1){
								$arTmpFilter = array('LOGIC' => 'OR');
								foreach($arParams['STORES'] as $storeID)
								{
									$arTmpFilter[] = array(">CATALOG_STORE_AMOUNT_".$storeID => 0);
								}
							}
							else{
								foreach($arParams['STORES'] as $storeID)
								{
									$arTmpFilter = array(">CATALOG_STORE_AMOUNT_".$storeID => 0);
								}
							}
						}
						$arFilterStores[] = $arTmpFilter;
						

					}
				}
			}

			foreach($arShowProp as $key => $prop)
			{
				$arItems = array();
				$arFilterProp = array("PROPERTY_".$arParams["TABS_CODE"]."_VALUE" => array($prop));

				$arItems = CMaxCache::CIBLockElement_GetList(array('CACHE' => array("MULTI" => "N", "TAG" => CMaxCache::GetIBlockCacheTag($arParams["IBLOCK_ID"]))), array_merge($arFilter, $arrFilter, $arFilterStores, $arFilterProp), false, array("nTopCount" => 1), array("ID"));
				if($arItems)
				{
					$arTabs[$key] = array(
						"CODE" => $key,
						"TITLE" => $prop,
						"FILTER" => array_merge($arFilter, $arrFilter, $arFilterStores, $arFilterProp)
					);
					$arResult["SHOW_SLIDER_PROP"] = true;
				}
			}
		}
		else
		{
			return;
		}
		$arParams["PROP_CODE"] = $arParams["TABS_CODE"];
		$arResult["TABS"] = $arTabs;

		if ($bUseModuleProps){
			$arSKU = CCatalogSKU::GetInfoByProductIBlock($arParams['IBLOCK_ID']);
			$arParams['OFFERS_CART_PROPERTIES'] = (array)\Bitrix\Catalog\Product\PropertyCatalogFeature::getBasketPropertyCodes($arSKU['IBLOCK_ID'], ['CODE' => 'Y']);
		}
		?>
		<?$this->IncludeComponentTemplate('ajax');?>
	<?}?>
<?}
else
	return;?>