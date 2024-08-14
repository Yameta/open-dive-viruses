<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Sign\Access\Permission;

class SignPermissionDictionary extends \Bitrix\Main\Access\Permission\PermissionDictionary
{
	public const SIGN_ACCESS_RIGHTS = 1;
	public const SIGN_MY_SAFE_DOCUMENTS = 2;
	public const SIGN_MY_SAFE = 3;
	public const SIGN_TEMPLATES = 4;

	private static function isVariable($permissionId): bool
	{
		return in_array($permissionId, [
			self::SIGN_MY_SAFE_DOCUMENTS,
			self::SIGN_TEMPLATES
		]);
	}
	public static function getType($permissionId): string
	{
		return self::isVariable($permissionId)
			? static::TYPE_VARIABLES
			: static::TYPE_TOGGLER;
	}
	
	public static function getName($permissionId): ?string
	{
		$permissions = static::getList();
		if (!array_key_exists($permissionId, $permissions))
		{
			return null;
		}
		return $permissions[$permissionId]['NAME'];
	}
}