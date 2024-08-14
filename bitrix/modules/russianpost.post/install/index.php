<?
IncludeModuleLangFile(__FILE__);
//CModule::IncludeModule("highloadblock");

if ( class_exists('russianpost_post') )
{
	return;
}
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
class russianpost_post extends CModule
{
	var $MODULE_ID = "russianpost.post";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $PARTNER_NAME;
	var $PARTNER_URI;
	var $MODULE_GROUP_RIGHTS = 'Y';
	var $NEED_MAIN_VERSION = '18.0.0';
	var $NEED_MODULES = array("sale");
	var $GROUP_ID;

    public function __construct()
	//function russianpost_post()
	{
		$arModuleVersion = array();

		$path = str_replace('\\', '/', __FILE__);
		$path = substr($path, 0, strlen($path) - strlen('/index.php'));
		include($path.'/version.php');

		if ( is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion) )
		{
			$this->MODULE_VERSION = $arModuleVersion['VERSION'];
			$this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
		}

		// !Twice! Marketplace bug. 2013-03-13
		$this->PARTNER_NAME = "RUSSIANPOST";
		$this->PARTNER_NAME = GetMessage('RUSSIANPOST_POST_PARTNER_NAME');
		$this->PARTNER_URI = "https://otpravka.pochta.ru/";

		$this->MODULE_NAME = GetMessage('RUSSIANPOST_POST_MODULE_NAME');
		$this->MODULE_DESCRIPTION = GetMessage('RUSSIANPOST_POST_MODULE_DESCRIPTION');
	}

	public function DoInstall()
	{
		global $APPLICATION;

		global $russianpost_post_global_errors;
		$russianpost_post_global_errors = array();


		if ( is_array($this->NEED_MODULES) && !empty($this->NEED_MODULES) )
		{
			foreach ( $this->NEED_MODULES
				as
				$module )
			{
				if ( !IsModuleInstalled($module) )
				{
					$russianpost_post_global_errors[] = GetMessage('RUSSIANPOST_POST_NEED_MODULES', array('#MODULE#' => $module));
				}
			}
		}

		if ( strlen($this->NEED_MAIN_VERSION) > 0 && version_compare(SM_VERSION, $this->NEED_MAIN_VERSION) < 0 )
		{
			$russianpost_post_global_errors[] = GetMessage('RUSSIANPOST_POST_NEED_RIGHT_VER', array('#NEED#' => $this->NEED_MAIN_VERSION));
		}


		global $APPLICATION;

        if ( count( $russianpost_post_global_errors ) == 0 )
        {
            RegisterModule("russianpost.post");
            $this->InstallFiles();
            $this->InstallDB();
            $this->InstallEvents();
            $this->CreateProperties();


        }
		$APPLICATION->IncludeAdminFile(GetMessage("RUSSIANPOST_POST_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/step1.php");
        return true;
	}

	public function DoUninstall()
	{

		global $APPLICATION;
		$russianpost_post_step = IntVal($_REQUEST['russianpost_post_step']);
        Loader::includeModule("russianpost.post");
		$request = new \Russianpost\Post\Request();
		$request->UnInstallCabinet();
        Option::delete("russianpost.post", array(
            "name" => "GUID_KEY",
        ));
        $arParams = Array();
		$this->UnInstallFiles($arParams);
		//$this->DeletMenuItem();
        $this->UnInstallDB($arParams);
        $this->UnInstallEvents($arParams);
        UnRegisterModule("russianpost.post");
        $APPLICATION->IncludeAdminFile(GetMessage("russianpost_post_uninstal_title"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/unstep1.php");
        return true;
	}


	function InstallDB($arParams = array())
	{
		CModule::AddAutoloadClasses("russianpost.post", array(
			"CRussianpostHLtool" => "lib/hltool.php",
			"Russianpost\\Post\\Hllist" => "lib/hllist.php",
		));

		$arData = array(
			"url_data_file" => "/bitrix/modules/russianpost.post/install/upload/countryList.xml",
			"object" => "",
			"xml_id" => "ID",
			"import_hl" => true,
			"import_data" => true,
			"save_reference" => true,
		);


		\CModule::IncludeModule("highloadblock");
		$HLName = "PostListCountryCodes";
		$hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getList(array("filter" => array('=NAME' => $HLName)))->fetch();
		$hlID = $hlblock["ID"];
		if ( $hlID )
		{
			$arData["object"] = $hlID;
		}
		$resParams = CRussianpostHLtool::importBlockStep($arData);
	}

	function InstallFiles($arParams = array())
	{
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/js/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js/", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/delivery/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/include/sale_delivery/", true, true);

	}

	function InstallEvents($arParams = array())
	{
		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->registerEventHandler("sale", "OnSaleOrderSaved", $this->MODULE_ID, "\\Russianpost\\Post\\Tools", "SaleSaved");
        $eventManager->registerEventHandler("sale", "OnSaleComponentOrderResultPrepared", $this->MODULE_ID, "\\Russianpost\\Post\\Tools", "OneStep");
        $eventManager->registerEventHandler("sale", "OnSaleComponentOrderDeliveriesCalculated", $this->MODULE_ID, "\\Russianpost\\Post\\Tools", "AfterDeliveryCalculated");
        $eventManager->registerEventHandler("sale", "OnSaleComponentOrderUserResult", $this->MODULE_ID, "\\Russianpost\\Post\\Tools", "UserResult");
        $eventManager->registerEventHandler("sale", "OnSaleComponentOrderShowAjaxAnswer", $this->MODULE_ID, "\\Russianpost\\Post\\Tools", "AjaxAnswer");
        $eventManager->registerEventHandler("sale", "OnSaleOrderBeforeSaved", $this->MODULE_ID, "\\Russianpost\\Post\\Tools", "BeforeSaved");
        $eventManager->registerEventHandler("main", "OnAdminContextMenuShow", $this->MODULE_ID, "\\Russianpost\\Post\\Tools", "AdminButtons");
		$eventManager->registerEventHandler("main", "OnEpilog", $this->MODULE_ID, "\\Russianpost\\Post\\Tools", "OnEpilog");
		$eventManager->registerEventHandler("sale", "onSaleDeliveryHandlersClassNamesBuildList", $this->MODULE_ID, "\\Russianpost\\Post\\Tools", "BuildList");
		$eventManager->registerEventHandler('sale', 'onSaleDeliveryTrackingClassNamesBuildList', $this->MODULE_ID, '\Russianpost\Post\Tools', 'onSaleDeliveryTrackingClassNamesBuildList');

	}

	function UnInstallFiles($arParams = array())
	{

		DeleteDirFilesEx("/bitrix/js/russianpost.post/");
		DeleteDirFilesEx("/bitrix/php_interface/include/sale_delivery/russianpost/");

	}


	function UnInstallDB($arParams = array())
	{
		\CModule::IncludeModule("highloadblock");

		$HLName = "PostListCountryCodes";
		$hlblock = Bitrix\Highloadblock\HighloadBlockTable::getList(array("filter" => array('=NAME' => $HLName)))->fetch();
		$hlID = $hlblock["ID"];
		if ( $hlID )
		{
			Bitrix\Highloadblock\HighloadBlockTable::delete($hlID);
		}
	}

	function UnInstallEvents($arParams = array())
	{
		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->unRegisterEventHandler("sale", "OnSaleOrderSaved", $this->MODULE_ID, "\\Russianpost\\Post\\Tools", "SaleSaved");
        $eventManager->unRegisterEventHandler("sale", "OnSaleComponentOrderResultPrepared", $this->MODULE_ID, "\\Russianpost\\Post\\Tools", "OneStep");
        $eventManager->unRegisterEventHandler("sale", "OnSaleComponentOrderDeliveriesCalculated", $this->MODULE_ID, "\\Russianpost\\Post\\Tools", "AfterDeliveryCalculated");
        $eventManager->unRegisterEventHandler("sale", "OnSaleComponentOrderUserResult", $this->MODULE_ID, "\\Russianpost\\Post\\Tools", "UserResult");
        $eventManager->unRegisterEventHandler("sale", "OnSaleComponentOrderShowAjaxAnswer", $this->MODULE_ID, "\\Russianpost\\Post\\Tools", "AjaxAnswer");
        $eventManager->unRegisterEventHandler("sale", "OnSaleOrderBeforeSaved", $this->MODULE_ID, "\\Russianpost\\Post\\Tools", "BeforeSaved");
        $eventManager->unRegisterEventHandler("main", "OnAdminContextMenuShow", $this->MODULE_ID, "\\Russianpost\\Post\\Tools", "AdminButtons");
		$eventManager->unRegisterEventHandler("main", "OnEpilog", $this->MODULE_ID, "\\Russianpost\\Post\\Tools", "OnEpilog");
		$eventManager->unRegisterEventHandler("sale", "onSaleDeliveryHandlersClassNamesBuildList", $this->MODULE_ID, "\\Russianpost\\Post\\Tools", "BuildList");
		$eventManager->unRegisterEventHandler('sale', 'onSaleDeliveryTrackingClassNamesBuildList', $this->MODULE_ID, '\Russianpost\Post\Tools', 'onSaleDeliveryTrackingClassNamesBuildList');

	}

	function CreateProperties()
	{
		Loader::includeModule("russianpost.post");
		\Russianpost\Post\Tools::CreateOrderProps();
	}

}

?>