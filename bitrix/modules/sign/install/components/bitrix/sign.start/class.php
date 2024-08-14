<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Crm\Service\Container;
use Bitrix\Crm\Service\Factory\SmartDocument;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Uri;

\CBitrixComponent::includeComponentClass('bitrix:sign.base');

class SignStartComponent extends SignBaseComponent
{
	/**
	 * Section menu item index.
	 * @var string|null
	 */
	private static $menuIndex = null;

	/**
	 * Required params of component.
	 * If not specified, will be set to null.
	 * @var string[]
	 */
	protected static array $requiredParams = [
		'SEF_FOLDER'
	];

	/**
	 * Default sef urls.
	 * @var string[]
	 */
	private $defaultUrlTemplates = [
		'main_page' => '',
		'kanban' => 'kanban/',
		'list' => 'list/',
		'mysafe' => 'mysafe/',
		'contact' => 'contact/',
		'config_permissions' => 'config/permission/',
		'document' => 'doc/#doc_id#/',
		'edit' => 'edit/#doc_id#/',
	];

	/**
	 * Map of url and their params.
	 * @var array
	 */
	private $urlWithVariables = [
		'main_page' => [],
		'kanban' => [],
		'list' => [],
		'mysafe' => [],
		'contact' => [],
		'config_permissions' => [],
		'document' => ['doc_id'],
		'edit' => ['doc_id'],
	];

	/**
	 * Map between crm and local urls.
	 * @var string[]
	 */
	private $crmUrls = [
		'bitrix:crm.document.details' => 'PAGE_URL_DOCUMENT',
		'bitrix:crm.item.kanban' => 'PAGE_URL_KANBAN',
		'bitrix:crm.item.list' => 'PAGE_URL_LIST'
	];

	/**
	 * Resolves complex component's URLs.
	 * @return void
	 */
	private function resolveTemplate(): void
	{
		// if sef mode is ON
		if ($this->getParam('SEF_MODE') === 'Y')
		{
			// merge default paths with custom
			$urlTemplates = array_merge(
				$this->defaultUrlTemplates,
				$this->getArrayParam('SEF_URL_TEMPLATES')
			);

			// resolve template page
			$componentPage = \CComponentEngine::parseComponentPath(
				$this->getStringParam('SEF_FOLDER'),
				$urlTemplates,
				$variables
			);

			// init variables
			\CComponentEngine::initComponentVariables($componentPage, [], [], $variables);

			// build urls by rules
			foreach ($this->urlWithVariables as $code => $var)
			{
				$this->setParam(
					'PAGE_URL_' . mb_strtoupper($code),
					$this->getStringParam('SEF_FOLDER') . $urlTemplates[$code]
				);
			}
		}
		// if sef mode is OFF
		else
		{
			// collect all expected variables
			$defaultVariableAliases = [
				'page' => 'page'
			];
			foreach ($this->urlWithVariables as $vars)
			{
				foreach ($vars as $var)
				{
					$defaultVariableAliases[$var] = $var;
				}
			}

			// merge default variables with custom
			$variableAliases = array_merge(
				$defaultVariableAliases,
				$this->getArrayParam('VARIABLE_ALIASES')
			);

			// init variables
			\CComponentEngine::initComponentVariables(
				false,
				$defaultVariableAliases,
				$variableAliases,
				$variables
			);

			// resolve template page
			$varPage = $variableAliases['page'];
			$componentPage = $variables['page'] ?? '';
			if (!($this->urlWithVariables[$componentPage] ?? null))
			{
				$componentPage = '';
			}

			// build urls by rules
			foreach ($this->urlWithVariables as $code => $vars)
			{
				$paramCode = 'PAGE_URL_' . mb_strtoupper($code);
				$uri = new Uri($this->getRequestedPage());
				$uri->addParams([$varPage => $code]);

				foreach ($vars as $var)
				{
					if (isset($variableAliases[$var]))
					{
						$uri->addParams([$variableAliases[$var] => '#' . $var . '#']);
					}
				}

				$this->setParam($paramCode, urldecode($uri->getUri()));
			}
		}

		// set variables to params
		if ($componentPage)
		{
			foreach ($this->urlWithVariables[$componentPage] as $var)
			{
				$this->setParam('VAR_' . mb_strtoupper($var), null);
			}
		}
		foreach ($variables as $code => $var)
		{
			$this->setParam('VAR_' . mb_strtoupper($code), $var);
		}

		$this->setTemplate($componentPage ?: (array_keys($this->urlWithVariables)[0] ?? ''));
	}

	/**
	 * Sets new custom urls in some CRM's places.
	 * @return void
	 */
	private function replaceCrmUrls(): void
	{
		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->addEventHandler(
			'crm',
			'onGetUrlForTemplateRouter',
			function(\Bitrix\Main\Event $event)
			{
				$componentName = $event->getParameter('componentName');
				$parameters = $event->getParameter('parameters');

				if ($componentName === 'bitrix:crm.document.details' && ($parameters['ENTITY_ID'] ?? 0) === 0)
				{
					return new \Bitrix\Main\Web\Uri(
						str_replace('#doc_id#', 0, $this->arParams[$this->crmUrls[$componentName]])
					);
				}
				else if ($componentName === 'bitrix:crm.item.list' || $componentName === 'bitrix:crm.item.kanban')
				{
					return new \Bitrix\Main\Web\Uri($this->arParams[$this->crmUrls[$componentName]]);
				}
			}
		);
	}

	/**
	 * Sets section menu item index.
	 * @param string $code Menu item code.
	 * @return void
	 */
	public function setMenuIndex(string $code): void
	{
		$this::$menuIndex = $code;
	}

	/**
	 * Returns section menu item index.
	 * @return string|null
	 */
	public function getMenuIndex(): ?string
	{
		return $this::$menuIndex;
	}

	private function prepareMenuItems()
	{
		$this->arParams['MENU_ITEMS'] = [];
		$this->arParams['MENU_ITEMS'][] = [
			'TEXT' => Loc::getMessage('SIGN_CMP_START_TPL_MENU_INDEX'),
			'URL' => $this->arParams['PAGE_URL_MAIN_PAGE'],
			'ID' => 'sign_index',
			'COUNTER' => 0,
			'COUNTER_ID' => 'sign_index'
		];

		if ($this->accessController->check(\Bitrix\Sign\Access\ActionDictionary::ACTION_MY_SAFE))
		{
			$this->arParams['MENU_ITEMS'][] = [
				'TEXT' => Loc::getMessage('SIGN_CMP_START_TPL_MENU_MYSAFE'),
				'URL' => $this->arParams['PAGE_URL_MYSAFE'],
				'ID' => 'sign_mysafe',
				'COUNTER' => 0,
				'COUNTER_ID' => 'sign_mysafe',
			];
		}

		$userPermissions = \Bitrix\Crm\Service\Container::getInstance()->getUserPermissions();
		$contactCategoryId = Container::getInstance()
			->getFactory(CCrmOwnerType::Contact)
			?->getCategoryByCode(SmartDocument::CONTACT_CATEGORY_CODE)
			?->getId();

		if ($contactCategoryId && $userPermissions->checkReadPermissions(CCrmOwnerType::Contact, 0, $contactCategoryId))
		{
			$this->arParams['MENU_ITEMS'][] = [
				'TEXT' => Loc::getMessage('SIGN_CMP_START_TPL_MENU_CONTACTS'),
				'URL' => $this->arParams['PAGE_URL_CONTACT'],
				'ID' => 'sign_contacts',
				'COUNTER' => 0,
				'COUNTER_ID' => 'sign_contacts',
			];
		}

		if ($this->accessController->check(\Bitrix\Sign\Access\ActionDictionary::ACTION_ACCESS_RIGHTS))
		{
			$this->arParams['MENU_ITEMS'][] = [
				'TEXT' => Loc::getMessage('SIGN_CMP_START_TPL_MENU_CONFIG_PERMISSIONS'),
				'URL' => $this->arParams['PAGE_URL_CONFIG_PERMISSIONS'],
				'ID' => 'sign_config_permission',
				'COUNTER' => 0,
				'COUNTER_ID' => 'sign_config_permission',
			];
		}
	}

	/**
	 * Executes component.
	 * @return void
	 */
	public function exec(): void
	{
		$this->resolveTemplate();
		$this->replaceCrmUrls();
		$this->prepareMenuItems();

		$this->setParam('ENTITY_ID', \Bitrix\Sign\Document\Entity\Smart::getEntityTypeId());
	}
}
