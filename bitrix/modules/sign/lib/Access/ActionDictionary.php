<?php

namespace Bitrix\Sign\Access;

use Bitrix\Sign\Access\Permission\PermissionDictionary;
use Bitrix\Sign\Access\Permission\SignPermissionDictionary;
use ReflectionClass;

class ActionDictionary
{
	
	public const ACTION_DOCUMENT_ADD = 'ACTION_DOCUMENT_ADD';
	public const ACTION_DOCUMENT_EDIT = 'ACTION_DOCUMENT_EDIT';
	public const ACTION_DOCUMENT_READ = 'ACTION_DOCUMENT_READ';
	public const ACTION_MY_SAFE_DOCUMENTS = 'ACTION_MY_SAFE_DOCUMENTS';
	public const ACTION_MY_SAFE = 'ACTION_MY_SAFE';
	public const ACTION_ACCESS_RIGHTS = 'ACTION_ACCESS_RIGHTS';
	public const ACTION_USE_TEMPLATE = 'ACTION_USE_TEMPLATE';
	public const PREFIX = "ACTION_";

	/**
	 * get action name by string value
	 *
	 * @param string $value string value of action
	 *
	 * @return string|null
	 */
	public static function getActionName(string $value): ?string
	{
		$constants = self::getActionNames();
		if (!array_key_exists($value, $constants))
		{
			return null;
		}
		
		return str_replace(self::PREFIX, '', $constants[$value]);
	}

	/**
	 * @return array
	 */
	private static function getActionNames(): array
	{
		$class = new ReflectionClass(__CLASS__);
		$constants = $class->getConstants();
		foreach ($constants as $name => $value)
		{
			if (mb_strpos($name, self::PREFIX) !== 0)
			{
				unset($constants[$name]);
			}
		}
		
		return array_flip($constants);
	}
	
	public static function getActionPermissionMap(): array
	{
		return [
			self::ACTION_DOCUMENT_ADD => PermissionDictionary::SIGN_CRM_SMART_DOCUMENT_ADD,
			self::ACTION_MY_SAFE_DOCUMENTS => SignPermissionDictionary::SIGN_MY_SAFE_DOCUMENTS,
			self::ACTION_MY_SAFE => SignPermissionDictionary::SIGN_MY_SAFE,
			self::ACTION_ACCESS_RIGHTS => SignPermissionDictionary::SIGN_ACCESS_RIGHTS,
			self::ACTION_DOCUMENT_EDIT => PermissionDictionary::SIGN_CRM_SMART_DOCUMENT_WRITE,
			self::ACTION_USE_TEMPLATE => SignPermissionDictionary::SIGN_TEMPLATES,
			self::ACTION_DOCUMENT_READ => PermissionDictionary::SIGN_CRM_SMART_DOCUMENT_READ,
		];
	}
}