<?
global $MESS;
$strPath2Lang = str_replace("\\", "/", __FILE__);
$strPath2Lang = substr($strPath2Lang, 0, strlen($strPath2Lang)-strlen("/install/index.php"));
include(GetLangFileName($strPath2Lang."/lang/", "/install/index.php"));

class aspro_smartseo extends CModule {
	const solutionName	= 'smartseo';
	const partnerName = 'aspro';
	const moduleClass = 'Aspro\Smartseo\General\Smartseo';

	var $MODULE_ID = "aspro.smartseo";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "Y";

	function __construct(){
		$arModuleVersion = array();

		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");

		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		$this->MODULE_NAME = GetMessage("ASPRO_SMARTSEO_SCOM_INSTALL_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("ASPRO_SMARTSEO_SCOM_INSTALL_DESCRIPTION");
		$this->PARTNER_NAME = GetMessage("ASPRO_SMARTSEO_SPER_PARTNER");
		$this->PARTNER_URI = GetMessage("ASPRO_SMARTSEO_PARTNER_URI");
	}

	function checkValid(){
		return true;
	}

	function InstallDB($install_wizard = true){
		global $DB, $DBType, $APPLICATION;

		RegisterModule($this->MODULE_ID);
		// RegisterModuleDependences("main", "OnBeforeProlog", $this->MODULE_ID, self::moduleClassEvents, "ShowPanel");

		// autoload classes
		require_once realpath(__DIR__.'/../include.php');

		Aspro\Smartseo\General\SmartseoInstall::install();

		return true;
	}

	function UnInstallDB($arParams = array()){
		global $DB, $DBType, $APPLICATION;

		// autoload classes
		require_once realpath(__DIR__.'/../include.php');

		Aspro\Smartseo\General\SmartseoInstall::unInstall();

		UnRegisterModule($this->MODULE_ID);


		return true;
	}

	function InstallEvents(){
		//RegisterModuleDependences("seo", "\Bitrix\Seo\Sitemap::OnAfterUpdate", $this->MODULE_ID, self::moduleClassEvents, 'OnAfterUpdateSitemapHandler');
		return true;
	}

	function UnInstallEvents(){
		//UnRegisterModuleDependences("seo", "\Bitrix\Seo\Sitemap::OnAfterUpdate", $this->MODULE_ID, self::moduleClassEvents, 'OnAfterUpdateSitemapHandler');
		return true;
	}

	function removeDirectory($dir){
		if($objs = glob($dir."/*")){
			foreach($objs as $obj){
				if(is_dir($obj)){
					CMax::removeDirectory($obj);
				}
				else{
					if(!unlink($obj)){
						if(chmod($obj, 0777)){
							unlink($obj);
						}
					}
				}
			}
		}
		if(!rmdir($dir)){
			if(chmod($dir, 0777)){
				rmdir($dir);
			}
		}
	}

	function InstallFiles(){
		CopyDirFiles(__DIR__.'/admin/', $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin', true);
		CopyDirFiles(__DIR__.'/css/', $_SERVER['DOCUMENT_ROOT'].'/bitrix/css/'.self::partnerName.'.'.self::solutionName, true, true);
		CopyDirFiles(__DIR__.'/js/', $_SERVER['DOCUMENT_ROOT'].'/bitrix/js/'.self::partnerName.'.'.self::solutionName, true, true);
		CopyDirFiles(__DIR__.'/tools/', $_SERVER['DOCUMENT_ROOT'].'/bitrix/tools/'.self::partnerName.'.'.self::solutionName, true, true);
		CopyDirFiles(__DIR__.'/images/', $_SERVER['DOCUMENT_ROOT'].'/bitrix/images/'.self::partnerName.'.'.self::solutionName, true, true);
		CopyDirFiles(__DIR__.'/components/', $_SERVER['DOCUMENT_ROOT'].'/bitrix/components', true, true);
		//CopyDirFiles(__DIR__.'/wizards/', $_SERVER['DOCUMENT_ROOT'].'/bitrix/wizards', true, true);

		//$this->InstallGadget();

		return true;
	}

	function InstallPublic(){
	}

	function UnInstallFiles(){
		DeleteDirFiles(__DIR__.'/admin/', $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin');
		DeleteDirFilesEx('/bitrix/css/'.self::partnerName.'.'.self::solutionName.'/');
		DeleteDirFilesEx('/bitrix/js/'.self::partnerName.'.'.self::solutionName.'/');
		DeleteDirFilesEx('/bitrix/tools/'.self::partnerName.'.'.self::solutionName.'/');
		DeleteDirFilesEx('/bitrix/images/'.self::partnerName.'.'.self::solutionName.'/');
		//DeleteDirFilesEx('/bitrix/wizards/'.self::partnerName.'/'.self::solutionName.'/');

		$this->UnInstallGadget();

		return true;
	}

	function InstallGadget(){
		return true;
	}

	function UnInstallGadget(){
		return true;
	}

	function DoInstall(){
		global $APPLICATION, $step;

		// autoload classes
		require_once realpath(__DIR__.'/../include.php');
		$this->InstallFiles();
		$this->InstallDB(false);
		$this->InstallEvents();
		$this->InstallPublic();

		//$APPLICATION->IncludeAdminFile(GetMessage("ASPRO_SMARTSEO_SCOM_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/aspro.smartseo/install/step.php");
	}

	function DoUninstall(){
		global $APPLICATION, $step;

		// autoload classes
		require_once realpath(__DIR__.'/../include.php');

		$this->UnInstallDB();
		$this->UnInstallFiles();
		$this->UnInstallEvents();

		//$APPLICATION->IncludeAdminFile(GetMessage("ASPRO_SMARTSEO_SCOM_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/aspro.smartseo/install/unstep.php");
	}
}
?>