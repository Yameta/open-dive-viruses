<?php
@set_time_limit(0);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
use Bitrix\Main\Loader; 
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc; 
use Bitrix\Iblock\PropertyIndex; 

Loc::loadMessages(__FILE__); 
define("SC_COLLECTIONS_MODULE_ID", "scoder.collections");
$module_id = SC_COLLECTIONS_MODULE_ID;
$moduleAccessLevel = $APPLICATION->GetGroupRight($module_id);
?>
<?if(check_bitrix_sessid()
	&& intval($_REQUEST["last_id"]) >= 0
		&& Loader::includeModule(SC_COLLECTIONS_MODULE_ID)
			&& $moduleAccessLevel > "R"
	)
{		
	$first_id = $last_id = (int) $_REQUEST["last_id"];
	$first_set_id = $set_last_id = (int) $_REQUEST["set_last_id"];
	
	$limit = Option::get(SC_COLLECTIONS_MODULE_ID, "STEP_COUNT");
	$limit_step = Option::get(SC_COLLECTIONS_MODULE_ID, "STEP_SET_COUNT");
	$dis_active_filter = Option::get(SC_COLLECTIONS_MODULE_ID, "DIS_ACTIVE_FILTER");
	$timeout = (int) Option::get(SC_COLLECTIONS_MODULE_ID, "TIMEOUT",100);
	
	if ($limit == 0)
		$limit = 500;
	if ($limit_step == 0)
		$limit_step = 500;
	
	$str = Option::get("scoder.collections", "REINDEX_IBLOCKS_ALL");
	
	$arValues = unserialize($str);
	if (is_array($arValues))
		$arIblocks = $arValues;
	
	//Если интерфейс фильтра НЕ отключен
	if ($dis_active_filter!="Y")
		$ar_types = CScoderCollections::get_iblocks_data(); 	//возвращает иинофрмацию по инфоблокам и их типам
	
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");

	//-----------------------------КОЛЛЕКЦИИ--------------------------------
	$ar_set_filter = array(
		">ID" => $set_last_id,
	);
	$ar_set_filter["IBLOCK_ID"] = $arIblocks;
	//Если интерфейс фильтра отключен
	if ($dis_active_filter=="Y")
		$ar_set_filter["!TYPE_ID"] = "F";
	
	$obCache = new CPHPCache();
	if ($obCache->InitCache(3600, 'scoder_'.$limit_step.serialize($ar_set_filter), "/scoder/collections"))
	{
		$arCollections = $obCache->GetVars();
	}
	elseif ($obCache->StartDataCache())
	{
		$arCollections = array();

		$res = CScoderCollectionsApi::GetList(
			Array("ID" => "ASC"), 
			$ar_set_filter,
			false,
			Array("nPageSize" => $limit_step),
			array("ID","IBLOCK_ID","FILTER","TYPE_ID","CONDITIONS","CHECK_PARENT","CATALOG_AVAILABLE","UNPACK","DISCOUNT_ACTION","SECTION_ID")
		);
		while ($arCollection = $res->GetNext())
		{
			//-------------------ИНТЕРФЕЙС ФИЛЬТРА------------------
			//Если интерфейс фильтра не отключен
			if ($dis_active_filter != "Y")
			{
				$arCollection['IBLOCK_TYPE'] = $ar_types["IBLOCK_TYPES"][$arCollection["IBLOCK_ID"]];
				
				//---------ГОТОВИМ ФИЛЬТР И КЕЭШИРУЕМ----
				$params = array(
					"IBLOCK_ID" => $arCollection["IBLOCK_ID"],
					"IBLOCK_TYPE" => $arCollection["IBLOCK_TYPE"],
				);
				
				$obCacheSub = new CPHPCache();
				if ($obCacheSub->InitCache(36000, 'scoder_'.serialize($params), "/scoder/collections"))
				{
					$all_fields = $obCacheSub->GetVars();
				}
				elseif ($obCacheSub->StartDataCache())
				{
					$all_fields = CScoderCollections::__get_filters($params);

					$obCacheSub->EndDataCache($all_fields);
				}
				//---------КОНЕЦ. ГОТОВИМ ФИЛЬТР И КЭШИРУЕМ----
				if ($arCollection['TYPE_ID']=="F" 
					&& $arCollection['~FILTER']!='')
				{
					$ar_str = unserialize($arCollection['~FILTER']);
					if (isset($ar_str['fields']))
						$val_fields = $ar_str['fields'];
					elseif (isset($ar_str['data']['fields']))
						$val_fields = $ar_str['data']['fields'];
					
					if (isset($val_fields))
					{
						$arCollectionFilter = array(
							"IBLOCK_ID" => $arCollection["IBLOCK_ID"],
							"SHOW_NEW" => "Y",
							"CHECK_PERMISSIONS" => "Y",
							"MIN_PERMISSION" => "R",
						);
						
						CScoderCollections::CreateFilter($all_fields["CATALOG_FIELDS"], $arCollectionFilter, $val_fields);
						
						if (is_array($all_fields["OFFER_FIELDS"]))
						{
							$arCatalog = CCatalogSKU::GetInfoByIBlock($params["IBLOCK_ID"]);
							$arSubQuery = array();
							$arSubQuery = array("IBLOCK_ID" => $arCatalog["IBLOCK_ID"]);
							
							CScoderCollections::CreateFilter($all_fields["OFFER_FIELDS"], $arSubQuery, $val_fields);
							
							if (1 < sizeof($arSubQuery))
							{
								$arCollectionFilter["ID"] = CIBlockElement::SubQuery("PROPERTY_".$arCatalog["SKU_PROPERTY_ID"], $arSubQuery);
							}
						}
									
						$arCollection["REAL_FILTER"] = $arCollectionFilter;
					}
				}
			}
			//-------------------КОНЕЦ. ИНТЕРФЕЙС ФИЛЬТРА-------
			if ($dis_active_filter!="Y" || ($dis_active_filter=="Y" && $arCollection['TYPE_ID']!="F" ))
				$arCollections[] = $arCollection;
			
			$arCollections[] = $arCollection;
		}
		$obCache->EndDataCache($arCollections);
	}

	if (isset($arCollections))
	{
		$count_set = count($arCollections)-1;
		$set_last_id = (int) $arCollections[$count_set]["ID"];
	}
	//-----------------------------КОНЕЦ. КОЛЛЕКЦИИ--------------------------	
	$ar_filter = array(
		">ID" => $last_id,
	);
	if (is_array($arIblocks) && count($arIblocks)>0)
		$ar_filter["IBLOCK_ID"] = $arIblocks;
	
	$rs = CIBlockElement::GetList(
		array("ID" => "ASC"), 
		$ar_filter, 
		false, 
		array("nTopCount" => $limit), 
		array("ID","IBLOCK_ID")
	);
	while ($ob = $rs->GetNextElement())
	{
		$arItem = $ob -> GetFields();
		
		$last_id = intval($arItem["ID"]);
		$arItem["DISREGARD"] = 'Y';
		
		//------------КОЛЛЕКЦИИ--------------------------
		if (is_array($arCollections))
		{
			CScoderCollections::ProductEdit($arItem,false,$arIblocks,$arCollections);
		}
		//------------КОНЕЦ. КОЛЛЕКЦИИ-------------------	
	}
	
	Option::set($module_id, "LAST_ID",$last_id);
	//query mod
	$arFilter1 = array("IBLOCK_ID" => $arIblocks, "<=ID" => $last_id);
	$leftBorderCnt = CIBlockElement::GetList(array("ID" => "ASC"), $arFilter1,array());
	
	//query all
	$arFilter2 = array("IBLOCK_ID" => $arIblocks);		
	$allCnt = CIBlockElement::GetList(array("ID" => "ASC"), $arFilter2,array());
	
	//--------------КОЛЛЕКЦИИ--------------------------
	//сколько сделано
	$ar_set_filter1 = array(
		"<=ID" => $set_last_id,
	);
	if (isset($ar_set_filter["IBLOCK_ID"]))
		$ar_set_filter1["IBLOCK_ID"] = $ar_set_filter["IBLOCK_ID"];
	//Если интерфейс фильтра отключен
	if ($dis_active_filter=="Y")
		$ar_set_filter1["!TYPE_ID"] = "F";
	$leftSetCnt = CScoderCollectionsApi::GetList(Array("ID" => "ASC"),$ar_set_filter1,Array());
	
	//сколько всего
	$ar_set_filter2 = array();
	if (isset($ar_set_filter["IBLOCK_ID"]))
		$ar_set_filter2["IBLOCK_ID"] = $ar_set_filter["IBLOCK_ID"];
	if ($dis_active_filter=="Y")
		$ar_set_filter2["!TYPE_ID"] = "F";
	$allSetCnt = CScoderCollectionsApi::GetList(Array("ID" => "ASC"),$ar_set_filter2,Array());
	//--------------КОНЕЦ. КОЛЛЕКЦИИ-------------------
	
	if(($leftBorderCnt < $allCnt) || ($leftSetCnt<$allSetCnt))	
	{
		//Если товары не закончились, то перебиарем тот же интервал колллекций
		if($leftBorderCnt < $allCnt)
			$set_last_id = $first_set_id;
		elseif ($leftSetCnt<$allSetCnt)		//Если колллекции не закончились
			$last_id = 0;
		
		CAdminMessage::ShowMessage(array(
			"MESSAGE" => Loc::getMessage('SCODER_ITEM_REINDEX'),
			"TYPE" => "PROGRESS",
			"PROGRESS_VALUE" => $leftBorderCnt,
			"PROGRESS_TOTAL" => $allCnt,
			"HTML" => true,
			"DETAILS" => Loc::getMessage("SCODER_PS_ADMIN_FILTERGEN_PROGRESS_DETAIL", array(
				"#done#" => $leftBorderCnt,
				"#todo#" => $allCnt,
				"#done_set#" => $leftSetCnt,
				"#todo_set#" => $allSetCnt,
			))."<br><br>#PROGRESS_BAR#",
		));?>
		<script>setTimeout("StartCollectionsReindex('<?echo CUtil::JSEscape($last_id)?>','<?echo CUtil::JSEscape($set_last_id)?>')", <?=$timeout;?>);</script>
		<?
	}
	else
	{
		Option::set($module_id, "LAST_ID",0);
		CAdminMessage::ShowMessage(array(
			"MESSAGE" => Loc::getMessage('SCODER_SUCCESS_ITEM_REINDEX'),
			"TYPE" => "OK",
			"HTML" => true,
			"DETAILS" => Loc::getMessage('SCODER_ALL_SUCCESS_ITEM_REINDEX'),
		));?>
		<script>setTimeout("Stop()", 100);</script>
		<?
		
	}	
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
}
?>