<?php
use Bitrix\Main\Localization\Loc;
use Bitrix\Main;
use Bitrix\Crm;

Loc::loadMessages(__FILE__);

if (class_exists('sign'))
{
	return;
}

class sign extends CModule
{
	public $MODULE_ID = 'sign';
	public $MODULE_GROUP_RIGHTS = 'N';
	public $MODULE_VERSION;
	public $MODULE_VERSION_DATE;
	public $MODULE_NAME;
	public $MODULE_DESCRIPTION;

	public $docRoot = '';
	public $eventsData = [
		'crm' => [
			'onSiteFormFillSign' => ['\Bitrix\Sign\Integration\CRM\Form', 'onSiteFormFillSign'],
		],
		'bitrix24' => [
			'onDomainChange' => ['\Bitrix\Sign\Integration\Bitrix24\Domain', 'onChangeDomain'],
		],
	];
	public $installDirs = [
		'components' => 'bitrix',
		'js' => 'sign',
	];

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$arModuleVersion = [];

		$context = \Bitrix\Main\Application::getInstance()->getContext();
		$server = $context->getServer();
		$this->docRoot = $server->getDocumentRoot();

		include(__DIR__ . '/version.php');

		if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion['VERSION'];
			$this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
		}

		$this->MODULE_NAME = Loc::getMessage('SIGN_CORE_MODULE_NAME');
		$this->MODULE_DESCRIPTION = Loc::getMessage('SIGN_CORE_MODULE_DESCRIPTION');
	}

	/**
	 * Calls all install methods.
	 * @returm void
	 */
	public function doInstall()
	{
		global $DB, $APPLICATION;

		$this->installFiles();
		$this->installDB();

		$APPLICATION->includeAdminFile(
			Loc::getMessage('SIGN_CORE_INSTALL_TITLE'),
			$this->docRoot . '/bitrix/modules/sign/install/step1.php'
		);
	}

	/**
	 * Calls all uninstall methods, include several steps.
	 * @returm void
	 */
	public function doUninstall()
	{
		global $APPLICATION;

		$step = isset($_GET['step']) ? intval($_GET['step']) : 1;
		if ($step < 2)
		{
			$APPLICATION->includeAdminFile(
				Loc::getMessage('SIGN_CORE_UNINSTALL_TITLE'),
				$this->docRoot . '/bitrix/modules/sign/install/unstep1.php'
			);
		}
		elseif ($step === 2)
		{
			$params = [];
			if (isset($_GET['savedata']))
			{
				$params['savedata'] = $_GET['savedata'] == 'Y';
			}
			$this->uninstallDB($params);
			$this->uninstallFiles();
			$APPLICATION->includeAdminFile(
				Loc::getMessage('SIGN_CORE_UNINSTALL_TITLE'),
				$this->docRoot . '/bitrix/modules/sign/install/unstep2.php'
			);
		}
	}

	/**
	 * Installs DB, events, etc.
	 * @return bool
	 */
	public function installDB()
	{
		global $DB, $APPLICATION;

		// db
		$errors = $DB->runSQLBatch(
			$this->docRoot.'/bitrix/modules/sign/install/db/mysql/install.sql'
		);
		if ($errors !== false)
		{
			$APPLICATION->throwException(implode('', $errors));
			return false;
		}

		// module
		registerModule($this->MODULE_ID);

		// events
		$eventManager = Bitrix\Main\EventManager::getInstance();
		foreach ($this->eventsData as $module => $events)
		{
			foreach ($events as $eventCode => $callback)
			{
				$eventManager->registerEventHandler(
					$module,
					$eventCode,
					$this->MODULE_ID,
					$callback[0],
					$callback[1]
				);
			}
		}

		return true;
	}

	/**
	 * Installs files.
	 * @return bool
	 */
	public function installFiles()
	{
		// needed to read in bxlink
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sign/install/js", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sign/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sign/install/activities", $_SERVER["DOCUMENT_ROOT"]."/bitrix/activities", true, true);

		return true;
	}

	/**
	 * Uninstalls DB, events, etc.
	 * @param array $arParams Some params.
	 * @return bool
	 */
	public function uninstallDB(array $arParams = [])
	{
		global $APPLICATION, $DB;

		$errors = false;

		// db
		if (isset($arParams['savedata']) && !$arParams['savedata'])
		{
			$errors = $DB->runSQLBatch(
				$this->docRoot.'/bitrix/modules/sign/install/db/mysql/uninstall.sql'
			);
		}
		if ($errors !== false)
		{
			$APPLICATION->throwException(implode('', $errors));
			return false;
		}

		// agents
		\CAgent::removeModuleAgents($this->MODULE_ID);

		// events
		$eventManager = Bitrix\Main\EventManager::getInstance();
		foreach ($this->eventsData as $module => $events)
		{
			foreach ($events as $eventCode => $callback)
			{
				$eventManager->unregisterEventHandler(
					$module,
					$eventCode,
					$this->MODULE_ID,
					$callback[0],
					$callback[1]
				);
			}
		}

		// module
		unregisterModule($this->MODULE_ID);

		return true;
	}

	/**
	 * Uninstalls files.
	 * @return bool
	 */
	public function uninstallFiles()
	{
		foreach ($this->installDirs as $dir => $subdir)
		{
			if ($dir != 'components' && $dir != 'activities')
			{
				deleteDirFilesEx('/bitrix/' . $dir . '/' . $subdir);
			}
		}

		return true;
	}

	/**
	 * Method for migrate from cloud version.
	 * @return void
	 */
	public function migrateToBox()
	{
	}
}
