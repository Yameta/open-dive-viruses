<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
use Bitrix\Main\Loader; 
use Bitrix\Main\Entity;
use Bitrix\Main\Config\Option;
use Bitrix\Highloadblock as HL;
use Bitrix\Iblock\PropertyIndex; 
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__); 

define("SC_COLLECTIONS_MODULE_ID", "scoder.collections");
$module_id = SC_COLLECTIONS_MODULE_ID;
$moduleAccessLevel = $APPLICATION->GetGroupRight($module_id);
?>
<?if(check_bitrix_sessid() 
	&& intval($_REQUEST["last_id"]) >= 0 
		&& intval($_REQUEST["set_id"]) > 0 
			&& $moduleAccessLevel > "R")
{	
	$SET_ID = (int) $_REQUEST["set_id"];
	$last_id = (int) $_REQUEST["last_id"];
	$limit = Option::get(SC_COLLECTIONS_MODULE_ID, "STEP_COUNT", 500);
	$timeout = (int) Option::get(SC_COLLECTIONS_MODULE_ID, "TIMEOUT",100);
	
	$arSet = array();
	$bError = false;
	
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");
	
	@set_time_limit(0);

	Loader::includeModule(SC_COLLECTIONS_MODULE_ID);
	
	$arSet = CScoderCollectionsSet::CheckRecord($SET_ID);	//запрос коллекции
	$ar_types = CScoderCollections::get_iblocks_data(); 	//возвращает иинофрмацию по инфоблокам и их типам
	
	if(empty($arSet))
	{
		CAdminMessage::ShowMessage(array(
			"MESSAGE" => Loc::getMessage("SCODER_PS_ADMIN_FILTERGEN_FAIL"),
			"TYPE" => "ERROR",
			"HTML" => true,
			"DETAILS" => Loc::getMessage("SCODER_PS_ADMIN_FILTERGEN_FAIL_NO_SET_ID"),
		));?><script>setTimeout("Stop()", 100);</script><?
		$bError = true;
	}
	$set = new CScoderCollectionsSet;
	//Если не заданы условия для фильтра
	if((empty($arSet["UNPACK"]) || $arSet["UNPACK"] == '((1 == 1))')
		&& $arSet["TYPE_ID"] != 'F'
	)
	{
		//Сбрасываем фильтр
		$Data = Array(
			"SECTION_ID" => $SET_ID,
			"DATE_GENERATION" => new \Bitrix\Main\Type\DateTime()
		);		
		$result = $set->Edit($Data);
		
		CAdminMessage::ShowMessage(array(
			"MESSAGE" => Loc::getMessage("SCODER_PS_ADMIN_FILTERGEN_FAIL"),
			"TYPE" => "ERROR",
			"HTML" => true,
			"DETAILS" => Loc::getMessage("SCODER_PS_ADMIN_FILTERGEN_FAIL_NO_UNPACK"),
		));?><script>setTimeout("Stop()", 100);</script><?
		$bError = true;
	}
	
	if($bError)
	{
		die();
	}
	
	if ($arSet["TYPE_ID"] != 'F')
	{
		if (!is_null($arSet['COLLECTION_IBLOKS']) && strlen($arSet['COLLECTION_IBLOKS'])>0)
		{
			$ar_iblocks = unserialize($arSet['COLLECTION_IBLOKS']);
		}
		if (isset($ar_iblocks) && is_array($ar_iblocks) && count($ar_iblocks)>0)
			$arIblocks = $ar_iblocks;
		else
			$arIblocks = CScoderCollections::get_iblocks();
	}
	else
		$arIblocks = array($arSet["IBLOCK_ID"]);
	
	if(!$arIblocks || empty($arIblocks))
	{
		die();
	}
	$arFilter = array(
		"IBLOCK_ID" => $arIblocks, 
		">ID" => $last_id
	);
	if ($arSet['CATALOG_AVAILABLE']=='Y' && $arSet["TYPE_ID"] != 'F')
		$arFilter['CATALOG_AVAILABLE'] = $arSet['CATALOG_AVAILABLE'];	
	
	$rs = CIBlockElement::GetList(
		array("ID" => "ASC"), 
		$arFilter, 
		false, 
		array("nTopCount" => $limit), 
		array("ID","IBLOCK_ID")
	);
	
	while ($ob = $rs->GetNextElement())
	{
		$arFields = $ob -> GetFields();
		$arFields['IBLOCK_TYPE'] = $ar_types["IBLOCK_TYPES"][$arFields["IBLOCK_ID"]];
		
		$last_id = intval($arFields["ID"]);
		$arFields["DISREGARD"] = 'Y';
		$arFields["SET_ID"] = $SET_ID;
		
		CScoderCollections::ProductEdit($arFields,false,$arIblocks);
	}
	$arFilter1 = array("IBLOCK_ID" => $arIblocks, "<=ID" => $last_id);
	if ($arSet['CATALOG_AVAILABLE']=='Y')
		$arFilter1['CATALOG_AVAILABLE'] = $arSet['CATALOG_AVAILABLE'];	
	
	$leftBorderCnt = CIBlockElement::GetList(array("ID" => "ASC"), $arFilter1, array());
	
	$arFilter2 = array("IBLOCK_ID" => $arIblocks);
	if ($arSet['CATALOG_AVAILABLE']=='Y')
		$arFilter2['CATALOG_AVAILABLE'] = $arSet['CATALOG_AVAILABLE'];	
	
	$allCnt = CIBlockElement::GetList(array("ID" => "ASC"), $arFilter2, array());
	
	if($leftBorderCnt < $allCnt)
	{
		CAdminMessage::ShowMessage(array(
			"MESSAGE" => Loc::getMessage("SCODER_PS_ADMIN_FILTERGEN_PROGRESS"),
			"TYPE" => "PROGRESS",
			"PROGRESS_VALUE" => $leftBorderCnt,
			"PROGRESS_TOTAL" => $allCnt,
			"HTML" => true,
			"DETAILS" => Loc::getMessage("SCODER_PS_ADMIN_FILTERGEN_PROGRESS_DETAIL", array(
				"#done#" => $leftBorderCnt,
				"#todo#" => $allCnt
			))."<br><br>#PROGRESS_BAR#",
		));?><script>setTimeout("Generate('<?echo CUtil::JSEscape($last_id)?>')", <?=$timeout;?>);</script><?
	}
	else
	{		
		//Переписываем фильтр
		$Data = Array(
			"SECTION_ID" => $SET_ID,
			"DATE_GENERATION" => new \Bitrix\Main\Type\DateTime()
		);
		$result = $set->Edit($Data);
		
		CAdminMessage::ShowMessage(array(
			"MESSAGE" => Loc::getMessage("SCODER_PS_ADMIN_FILTERGEN_DONE"),
			"TYPE" => "OK",
			"HTML" => true,
			"DETAILS" => Loc::getMessage("SCODER_PS_ADMIN_FILTERGEN_DONE_DETAIL"),
		));?><script>setTimeout("Stop()", 100);</script><?
	}
	
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
}
?>