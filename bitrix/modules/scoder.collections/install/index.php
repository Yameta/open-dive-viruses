<?
IncludeModuleLangFile(__FILE__);
Class scoder_collections extends CModule
{
	const MODULE_ID = 'scoder.collections';
	var $MODULE_ID = 'scoder.collections'; 
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $strError = '';

	function __construct()
	{
		$arModuleVersion = array();
		include(dirname(__FILE__)."/version.php");
		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		$this->MODULE_NAME = GetMessage("SCODER_COLLECTIONS_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("SCODER_COLLECTIONS_MODULE_DESC");

		$this->PARTNER_NAME = GetMessage("SCODER_COLLECTIONS_PARTNER_NAME");
		$this->PARTNER_URI = GetMessage("SCODER_COLLECTIONS_PARTNER_URI");
	}

	function InstallDB($arParams = array())
	{
		global $DB;
		
		if(!$DB->Query("SELECT 'x' FROM sc_collections", true))
			$DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/".$this->MODULE_ID."/install/db/mysql/install.sql");
			
		RegisterModule(self::MODULE_ID);		
		
		RegisterModuleDependences("main", "OnAdminTabControlBegin", self::MODULE_ID, "CScoderCollectionsEvents", "OnAdminTabControlBeginHandler");
		RegisterModuleDependences("main", "OnBeforeProlog", self::MODULE_ID, "CScoderCollectionsEvents", "OnBeforePrologHandler");
		
		RegisterModuleDependences("iblock", "OnAfterIBlockElementUpdate", self::MODULE_ID, "CScoderCollectionsEvents", "OnAfterIBlockElementUpdateHandler");
		RegisterModuleDependences("iblock", "OnAfterIBlockElementAdd", self::MODULE_ID, "CScoderCollectionsEvents", "OnAfterIBlockElementAddHandler");
		
		RegisterModuleDependences("iblock", "OnAfterIBlockSectionAdd", self::MODULE_ID, "CScoderCollectionsEvents", "OnAfterIBlockSectionAddHandler");
		RegisterModuleDependences("iblock", "OnAfterIBlockSectionUpdate", self::MODULE_ID, "CScoderCollectionsEvents", "OnAfterIBlockSectionUpdateHandler");
		RegisterModuleDependences("iblock", "OnAfterIBlockSectionDelete", self::MODULE_ID, "CScoderCollectionsEvents", "OnAfterIBlockSectionDeleteHandler");
		
		RegisterModuleDependences("iblock", "OnBeforeIBlockSectionUpdate", self::MODULE_ID, "CScoderCollectionsEvents", "OnBeforeIBlockSectionUpdateHandler");
		
		RegisterModuleDependences("iblock", "OnAfterIBlockElementSetPropertyValues", self::MODULE_ID, "CScoderCollectionsEvents", "OnAfterIBlockElementSetPropertyValuesHandler");
		RegisterModuleDependences("iblock", "OnAfterIBlockElementSetPropertyValuesEx", self::MODULE_ID, "CScoderCollectionsEvents", "OnAfterIBlockElementSetPropertyValuesExHandler");
		
		CAgent::AddAgent("CScoderCollectionsAgents::Reindex();", self::MODULE_ID, "N", 86400, "", "Y");
		return true;
	}

	function UnInstallDB($arParams = array())
	{
		global $DB;
		if (!$arParams['savedata'])
		{
			$DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/".$this->MODULE_ID."/install/db/mysql/uninstall.sql");
		}		
		
		UnRegisterModuleDependences("main", "OnAdminTabControlBegin", self::MODULE_ID, "CScoderCollectionsEvents", "OnAdminTabControlBeginHandler");
		UnRegisterModuleDependences("main", "OnBeforeProlog", self::MODULE_ID, "CScoderCollectionsEvents", "OnBeforePrologHandler");
		
		UnRegisterModuleDependences("iblock", "OnAfterIBlockElementUpdate", self::MODULE_ID, "CScoderCollectionsEvents", "OnAfterIBlockElementUpdateHandler");
		UnRegisterModuleDependences("iblock", "OnAfterIBlockElementAdd", self::MODULE_ID, "CScoderCollectionsEvents", "OnAfterIBlockElementAddHandler");
		
		UnRegisterModuleDependences("iblock", "OnAfterIBlockSectionAdd", self::MODULE_ID, "CScoderCollectionsEvents", "OnAfterIBlockSectionAddHandler");
		UnRegisterModuleDependences("iblock", "OnAfterIBlockSectionUpdate", self::MODULE_ID, "CScoderCollectionsEvents", "OnAfterIBlockSectionUpdateHandler");
		UnRegisterModuleDependences("iblock", "OnAfterIBlockSectionDelete", self::MODULE_ID, "CScoderCollectionsEvents", "OnAfterIBlockSectionDeleteHandler");
		
		UnRegisterModuleDependences("iblock", "OnBeforeIBlockSectionUpdate", self::MODULE_ID, "CScoderCollectionsEvents", "OnBeforeIBlockSectionUpdateHandler");
		
		UnRegisterModuleDependences("iblock", "OnAfterIBlockElementSetPropertyValues", self::MODULE_ID, "CScoderCollectionsEvents", "OnAfterIBlockElementSetPropertyValuesHandler");
		UnRegisterModuleDependences("iblock", "OnAfterIBlockElementSetPropertyValuesEx", self::MODULE_ID, "CScoderCollectionsEvents", "OnAfterIBlockElementSetPropertyValuesExHandler");
		
		CAgent::RemoveModuleAgents(self::MODULE_ID);
		
		UnRegisterModule(self::MODULE_ID);
		return true;
	}
	
	function InstallEvents($arParams = array())
	{
		return true;
	}

	function UnInstallEvents($arParams = array())
	{
		return true;
	}

	function InstallFiles($arParams = array())
	{
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".self::MODULE_ID."/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", true);
		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/install/themes'))
		{
			if ($dir = opendir($p))
			{
				while (false !== $item = readdir($dir))
				{
					if ($item == '..' || $item == '.')
						continue;
					CopyDirFiles($p.'/'.$item, $_SERVER['DOCUMENT_ROOT'].'/bitrix/themes/'.$item, $ReWrite = True, $Recursive = True);
				}
				closedir($dir);
			}
		}
		return true;
	}

	function UnInstallFiles()
	{	
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".self::MODULE_ID."/install/admin/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
		DeleteDirFilesEx("/bitrix/themes/.default/".self::MODULE_ID.".css");
		DeleteDirFilesEx("/bitrix/themes/.default/icons/".self::MODULE_ID."/");
		return true;
	}

	function DoInstall()
	{
		global $APPLICATION;
		$this->InstallFiles();
		$this->InstallEvents();
		$this->InstallDB();
		$APPLICATION->IncludeAdminFile(GetMessage("SCODER_COLLECTIONS_INST_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".self::MODULE_ID."/install/step.php");
	}

	function DoUninstall()
	{
		global $APPLICATION;

		$APPLICATION->ResetException();
		if (!check_bitrix_sessid())
			return false;
		
		$step = IntVal($_REQUEST['step']);
		if($step < 2)
		{
			$APPLICATION->IncludeAdminFile(GetMessage("SCODER_COLLECTIONS_UNINST_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".self::MODULE_ID."/install/unstep1.php");
		}
		elseif($step == 2)
		{
			$arParams = array(
				"savedata" => $_REQUEST["savedata"],
			); 
			$this->UnInstallDB($arParams);
			$this->UnInstallFiles();
			$this->UnInstallEvents();
			$APPLICATION->IncludeAdminFile(GetMessage("SCODER_COLLECTIONS_UNINST_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".self::MODULE_ID."/install/unstep2.php");
		}	
	}
}
?>