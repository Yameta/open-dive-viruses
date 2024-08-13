<?
	
AddEventHandler("main", "OnBuildGlobalMenu", "OnBuildGlobalMenuWRPRedirectHandler");

function OnBuildGlobalMenuWRPRedirectHandler(&$adminMenu, &$moduleMenu)
{
	if(file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/wrp_redirect_loader_s1.php"))
	{
		$moduleMenu[] = array(
			"parent_menu" => "global_menu_settings",
			"sort" => 101,
			"text" => 'Загрузка редиректов s1',
			"url" => "wrp_redirect_loader_s1.php?lang=ru"
		);
	}
	if(file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/wrp_redirect_loader_s2.php"))
	{
		$moduleMenu[] = array(
			"parent_menu" => "global_menu_settings",
			"sort" => 101,
			"text" => 'Загрузка редиректов s2',
			"url" => "wrp_redirect_loader_s2.php?lang=ru"
		);
	}
}	
	

AddEventHandler("main", "OnPageStart", array("WRPRedirect", "ManualRedirect"));
//AddEventHandler("main", "OnPageStart", array("WRPRedirect", "htaccessRedirects"));
//WRPRedirect::createHlb(#site_id#);
class WRPRedirect
{	
	CONST REDIRECT_HLB_TABLE = "wrp_redirects";
	CONST REDIRECT_TYPE = "301 Moved permanently";
	CONST HOST = "";//"www.site.ru";
	CONST HTTP = "";//"https://";
	
	public static function GetHLBEntityDataClassByTableName($tableName)
	{
		if(empty($tableName)) return false;
		global $DB;
		$strSql = "SELECT ID FROM b_hlblock_entity WHERE TABLE_NAME = '".$tableName."'";
		$res = $DB->Query($strSql, true);
		if(!$res) return false;
		if(!$row = $res->Fetch()) return false;
		return self::GetHLBEntityDataClass($row["ID"]);
	}
	public static function GetHLBEntityDataClass($HlBlockId)
	{
		if (empty($HlBlockId) || $HlBlockId < 1)
		{
			return false;
		}
		Bitrix\Main\Loader::includeModule('highloadblock');
		if(!$hlblock = Bitrix\Highloadblock\HighloadBlockTable::getById($HlBlockId)->fetch()) return false;   
		$entity = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
		$entity_data_class = $entity->getDataClass();
		return $entity_data_class;
	}
	
	public static function ManualRedirect()
	{
		if(CSite::InDir("/bitrix/") || CSite::InDir("/local/")) return;
		$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();
		$uriString = $request->getRequestUri();
		$uri = new Bitrix\Main\Web\Uri($uriString);
		$redirectUrl = $uri->getPath();
		$redirectUrlQ = $uri->getPathQuery();
		$filterUrl = rtrim($redirectUrl,"/");
		
		$hlbEntity = self::GetHLBEntityDataClassByTableName(self::REDIRECT_HLB_TABLE."_".SITE_ID);
		if(!$hlbEntity) return;
		$arFilter = array('UF_URL_FROM' => $redirectUrlQ, 'UF_FULL_URL_MATCH' => 1, 'UF_ACTIVE' => 1);
		$arFilter['UF_FULL_URL_MATCH'] = 1;
		$rsData = $hlbEntity::getList(array(
		   'select' => array('UF_URL_TO', 'UF_TYPE'),
		   'limit' => '1',
		   'filter' => $arFilter 
		));
		
		if($el = $rsData->fetch())
		{
			if($redirectUrlQ != $el["UF_URL_TO"])
			{
				$redirectType = self::REDIRECT_TYPE;
				$value_res = CUserFieldEnum::GetList(array(), array(
					"ID" => intVal($el["UF_TYPE"]),
				));
				if($type = $value_res->GetNext())
					$redirectType = $type["VALUE"];
				LocalRedirect(self::HTTP.self::HOST.$el["UF_URL_TO"], false, $redirectType);
			}
		}else{
			if(empty($filterUrl)){
			$filterUrl = rtrim($redirectUrl,"/")."/";
			$arFilter = array('UF_URL_FROM' => $filterUrl, 'UF_FULL_URL_MATCH' => 0, 'UF_ACTIVE' => 1);
		}else{
			$arFilter = Array(
				   Array(
					  "LOGIC"=>"OR",
					  Array(
						 "UF_URL_FROM"=>rtrim($redirectUrl,"/")
					  ),
					  Array(
						 "UF_URL_FROM"=>rtrim($redirectUrl,"/")."/"
					  )
				   ),
				   'UF_FULL_URL_MATCH' => 0, 
				   'UF_ACTIVE' => 1
				);
			}
						
			$rsData = $hlbEntity::getList(array(
			   'select' => array('UF_URL_FROM','UF_URL_TO', 'UF_TYPE'),
			   'limit' => '1',
			   'filter' => $arFilter 
			));
			if($el = $rsData->fetch())
			{
				if($redirectUrlQ != $el["UF_URL_TO"])
				{
					$redirectType = self::REDIRECT_TYPE;
					$value_res = CUserFieldEnum::GetList(array(), array(
						"ID" => intVal($el["UF_TYPE"]),
					));
					if($type = $value_res->GetNext())
						$redirectType = $type["VALUE"];
					LocalRedirect(self::HTTP.self::HOST.$el["UF_URL_TO"], false, $redirectType);
				}	
			}
		}			
	}
	public static function htaccessRedirects(){
		$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();
		$uriString = $request->getRequestUri();
		$uri = new Bitrix\Main\Web\Uri($uriString);
		$redirectUrl = $uri->getPath();
		$redirectUrlQ = $uri->getPathQuery();
		$arPath = parse_url($redirectUrlQ);
		$info = pathinfo($redirectUrlQ);

		$needSlash = false;
		if(empty($info["extension"]) && substr($arPath["path"],mb_strlen($arPath["path"])-1) !== "/"){
			$needSlash = true;
			$arPath["path"] .="/";

		}
		if((($_SERVER["REDIRECT_HTTPS"] != "On" && $_SERVER["HTTPS"] != "On" && $_SERVER["HTTP_X_HTTPS"] != 1) || $_SERVER["HTTP_HOST"] != self::HOST || $needSlash) && strpos($_SERVER["PHP_SELF"],"update_providers_") === false)
			LocalRedirect(self::HTTP.self::HOST.($arPath["path"]!="/"?$arPath["path"]:"").($arPath["query"]?"?".$arPath["query"]:"").($arPath["fragment"]?"#".$arPath["fragment"]:""), false, self::REDIRECT_TYPE);	
	}
	
	public static function loadRedirect($siteId, $arFromTo,$type="301_Moved_permanently")
	{
		$value_res = CUserFieldEnum::GetList(array(), array(
			"XML_ID" => $type,
		));
		if($type = $value_res->GetNext())
			$redirectType = $type["ID"];
		$entity_data_class = self::GetHLBEntityDataClassByTableName(self::REDIRECT_HLB_TABLE."_".$siteId);
		$resRedirects = $entity_data_class::getList();
		$curRedirect = array();
		while($arRedir = $resRedirects->fetch()){
			$curRedirect[$arRedir["UF_URL_FROM"]] = $arRedir["UF_URL_TO"];
		}
		$countLoad = 0;
		foreach($arFromTo as $from=>$to){
			if(array_key_exists($from, $curRedirect)) continue;
			$entity_data_class::add(
				array(
					"UF_ACTIVE" => 1,
					"UF_URL_FROM" => $from,
					"UF_URL_TO" =>  $to,
					"UF_FULL_URL_MATCH" =>  strpos($from,"?") !== false ? 1:0,
					"UF_TYPE" => $redirectType
				)
			);
			$countLoad++;
		}
		return $countLoad;
	}
	
	public static function load301Redirects($siteId, $filePath)
	{
		if(!file_exists($filePath)) return false;
		$content=file_get_contents($filePath);
		$arContent = explode("\n",$content);
		$arRedirect = array();
		foreach($arContent as $str){
			if(empty($str)) continue;
			$arstr = explode(";", $str);
			$arstr[0] = trim($arstr[0]);
			$arstr[1] = trim($arstr[1]);
			if(empty($arstr[0]) || empty($arstr[1])) continue;
			$arRedirect[$arstr[0]] = $arstr[1];
		}
		return self::loadRedirect($siteId, $arRedirect,"301_Moved_permanently");
	}
	
	public static function createHlb($site_id)
	{
		Bitrix\Main\Loader::includeModule('highloadblock');
		$tableName = self::REDIRECT_HLB_TABLE."_".$site_id;

		$filter = array(
			'select' => array('ID', 'NAME', 'TABLE_NAME'),
			'filter' => array('=TABLE_NAME' => $tableName)
		);
		$hlblock = Bitrix\Highloadblock\HighloadBlockTable::getList($filter)->fetch();
		if(!empty($hlblock)) return $hlblock["ID"];
		
		

		$data = array(
			'NAME' => "Redirects".$site_id,
			'TABLE_NAME' => $tableName
		);

		$result = Bitrix\Highloadblock\HighloadBlockTable::add($data);
		$ID = $result->getId();	
		if($ID < 1) return false;
		$localizations = array( 
			array(
				'ID' => $ID,
				"LID" => 'ru',
				'NAME' => 'Управление редиректами '.$site_id
			),
			array(
				'ID' => $ID,
				"LID" => 'en',
				'NAME' => 'Redirects '.$site_id
			)
		);
		foreach($localizations as $localization)
		{
			Bitrix\Highloadblock\HighloadBlockLangTable::add($localization);	
		}
		
		$oUserTypeEntity    = new CUserTypeEntity();
		 
		$aUserFields["FROM"]  = array(
			'ENTITY_ID'         => 'HLBLOCK_'.$ID,
			'FIELD_NAME'        => 'UF_URL_FROM',
			'USER_TYPE_ID'      => 'string',
			'XML_ID'            => 'UF_URL_FROM',
			'SORT'              => 100,
			'MULTIPLE'          => 'N',
			'MANDATORY'         => 'Y',
			'SHOW_FILTER'       => 'S',
			'SHOW_IN_LIST'      => '',
			'EDIT_IN_LIST'      => '',
			'IS_SEARCHABLE'     => 'N',
			'SETTINGS'  => array(
				'DEFAULT_VALUE' => '',
				'SIZE' => '100',
				'ROWS'          => '1',
				'MIN_LENGTH'    => '0',
				'MAX_LENGTH'    => '0',
				'REGEXP'        => '',
			),
			'EDIT_FORM_LABEL'   => array(
				'ru'    => 'Откуда',
				'en'    => 'From',
			),
			'LIST_COLUMN_LABEL' => array(
				'ru'    => 'Откуда',
				'en'    => 'From',
			),
			'LIST_FILTER_LABEL' => array(
				'ru'    => 'Откуда',
				'en'    => 'From',
			),
			'ERROR_MESSAGE'     => array(
				'ru'    => '',
				'en'    => '',
			),
			'HELP_MESSAGE'      => array(
				'ru'    => '',
				'en'    => '',
			)
		);
		$aUserFields["TO"]  = array(
			'ENTITY_ID'         => 'HLBLOCK_'.$ID,
			'FIELD_NAME'        => 'UF_URL_TO',
			'USER_TYPE_ID'      => 'string',
			'XML_ID'            => 'UF_URL_TO',
			'SORT'              => 200,
			'MULTIPLE'          => 'N',
			'MANDATORY'         => 'Y',
			'SHOW_FILTER'       => 'S',
			'SHOW_IN_LIST'      => '',
			'EDIT_IN_LIST'      => '',
			'IS_SEARCHABLE'     => 'N',
			'SETTINGS'  => array(
				'DEFAULT_VALUE' => '',
				'SIZE' => '100',
				'ROWS'          => '1',
				'MIN_LENGTH'    => '0',
				'MAX_LENGTH'    => '0',
				'REGEXP'        => '',
			),
			'EDIT_FORM_LABEL'   => array(
				'ru'    => 'Куда',
				'en'    => 'To',
			),
			'LIST_COLUMN_LABEL' => array(
				'ru'    => 'Куда',
				'en'    => 'To',
			),
			'LIST_FILTER_LABEL' => array(
				'ru'    => 'Куда',
				'en'    => 'To',
			),
			'ERROR_MESSAGE'     => array(
				'ru'    => '',
				'en'    => '',
			),
			'HELP_MESSAGE'      => array(
				'ru'    => '',
				'en'    => '',
			)
		);
		$aUserFields["TYPE"]  = array(
			'ENTITY_ID'         => 'HLBLOCK_'.$ID,
			'FIELD_NAME'        => 'UF_TYPE',
			'USER_TYPE_ID'      => 'enumeration',
			'XML_ID'            => 'UF_TYPE',
			'SORT'              => 300,
			'MULTIPLE'          => 'N',
			'MANDATORY'         => 'Y',
			'SHOW_FILTER'       => 'S',
			'SHOW_IN_LIST'      => '',
			'EDIT_IN_LIST'      => '',
			'IS_SEARCHABLE'     => 'N',
			'SETTINGS'  => array(
				'DISPLAY' => 'LIST',
				'LIST_HEIGHT' => '1',
				'CAPTION_NO_VALUE' => '',
				'SHOW_NO_VALUE'    => 'Y'
			),
			'EDIT_FORM_LABEL'   => array(
				'ru'    => 'Тип',
				'en'    => 'Type',
			),
			'LIST_COLUMN_LABEL' => array(
				'ru'    => 'Тип',
				'en'    => 'Type',
			),
			'LIST_FILTER_LABEL' => array(
				'ru'    => 'Тип',
				'en'    => 'Type',
			),
			'ERROR_MESSAGE'     => array(
				'ru'    => '',
				'en'    => '',
			),
			'HELP_MESSAGE'      => array(
				'ru'    => '',
				'en'    => '',
			)
		); 
		$arAddEnum["TYPE"] = array(
			'n0' => array(
				'XML_ID' => "301_Moved_permanently",
				'VALUE' => "301 Moved permanently",
				'DEF' => 'Y',
				'SORT' => 100
			),
			'n1' => array(
				'XML_ID' => "302_Found",
				'VALUE' => "302 Found",
				'DEF' => 'N',
				'SORT' => 200

			)
		);
		
		 
		$aUserFields["FULL_URL_MATCH"]  = array(
			'ENTITY_ID'         => 'HLBLOCK_'.$ID,
			'FIELD_NAME'        => 'UF_FULL_URL_MATCH',
			'USER_TYPE_ID'      => 'boolean',
			'XML_ID'            => 'UF_FULL_URL_MATCH',
			'SORT'              => 400,
			'MULTIPLE'          => 'N',
			'MANDATORY'         => 'N',
			'SHOW_FILTER'       => 'N',
			'SHOW_IN_LIST'      => '',
			'EDIT_IN_LIST'      => '',
			'IS_SEARCHABLE'     => 'N',
			'SETTINGS'  => array(
				'LABEL' => array(
					'1' => 'Да',
					'0' => 'Нет'
				),
				'DEFAULT_VALUE' => '0',
				'DISPLAY' => 'CHECKBOX',
				'LABEL_CHECKBOX' => 'Да'
			),
			'EDIT_FORM_LABEL'   => array(
				'ru'    => 'Полное совпадение url',
				'en'    => 'Full url match',
			),
			'LIST_COLUMN_LABEL' => array(
				'ru'    => 'Полное совпадение url',
				'en'    => 'Full url match',
			),
			'LIST_FILTER_LABEL' => array(
				'ru'    => 'Полное совпадение url',
				'en'    => 'Full url match',
			),
			'ERROR_MESSAGE'     => array(
				'ru'    => '',
				'en'    => '',
			),
			'HELP_MESSAGE'      => array(
				'ru'    => '',
				'en'    => '',
			)
		); 
		
		$aUserFields["ACTIVE"]  = array(
			'ENTITY_ID'         => 'HLBLOCK_'.$ID,
			'FIELD_NAME'        => 'UF_ACTIVE',
			'USER_TYPE_ID'      => 'boolean',
			'XML_ID'            => 'UF_ACTIVE',
			'SORT'              => 500,
			'MULTIPLE'          => 'N',
			'MANDATORY'         => 'N',
			'SHOW_FILTER'       => 'N',
			'SHOW_IN_LIST'      => '',
			'EDIT_IN_LIST'      => '',
			'IS_SEARCHABLE'     => 'N',
			'SETTINGS'  => array(
				'LABEL' => array(
					'1' => 'Да',
					'0' => 'Нет'
				),
				'DEFAULT_VALUE' => '1',
				'DISPLAY' => 'CHECKBOX',
				'LABEL_CHECKBOX' => 'Да'
			),
			'EDIT_FORM_LABEL'   => array(
				'ru'    => 'Активность',
				'en'    => 'Active',
			),
			'LIST_COLUMN_LABEL' => array(
				'ru'    => 'Активность',
				'en'    => 'Active',
			),
			'LIST_FILTER_LABEL' => array(
				'ru'    => 'Активность',
				'en'    => 'Active',
			),
			'ERROR_MESSAGE'     => array(
				'ru'    => '',
				'en'    => '',
			),
			'HELP_MESSAGE'      => array(
				'ru'    => '',
				'en'    => '',
			)
		);
		foreach($aUserFields as $code=>$aUserField)
		{
			$iUserFieldId   = $oUserTypeEntity->Add($aUserField);
			if($iUserFieldId > 0)
			{
				switch($aUserField['USER_TYPE_ID'])
				{
					case "enumeration":
						$obEnum = new CUserFieldEnum();
						$obEnum->SetEnumValues($iUserFieldId, $arAddEnum[$code]);
					break;
					default:
					break;
				}	
			}

		}

	}

}
?>