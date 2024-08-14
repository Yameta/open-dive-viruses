<?php
namespace Bitrix\Sign\Access;

use Bitrix\Main\Localization\Loc;
use Bitrix\Sign\Access\Permission\PermissionDictionary;
use Bitrix\Sign\Access\Permission\SignPermissionDictionary;
use ReflectionClass;

class SectionDictionary
{
	const CONTACT = 1;
	const DOCUMENT = 2;
	const ACCESS = 3;
	const SAFE = 4;
	const TEMPLATES = 5;

	/**
	 * @return array[]
	 */
	public static function getMap(): array
	{
		return [
			self::CONTACT => [
				PermissionDictionary::SIGN_CRM_CONTACT_READ,
			],
			self::DOCUMENT => [
				PermissionDictionary::SIGN_CRM_SMART_DOCUMENT_ADD,
				PermissionDictionary::SIGN_CRM_SMART_DOCUMENT_READ,
				PermissionDictionary::SIGN_CRM_SMART_DOCUMENT_WRITE,
				PermissionDictionary::SIGN_CRM_SMART_DOCUMENT_DELETE,
			],
			self::SAFE => [
				SignPermissionDictionary::SIGN_MY_SAFE_DOCUMENTS,
				SignPermissionDictionary::SIGN_MY_SAFE,
			],
			self::TEMPLATES => [
				SignPermissionDictionary::SIGN_TEMPLATES,
			],
			self::ACCESS => [
				SignPermissionDictionary::SIGN_ACCESS_RIGHTS,
			],
		];
	}

	protected static function getClassName(): string
	{
		return __CLASS__;
	}

	/**
	 * Getting a list of the permission settings
	 * @return array
	 */
	public static function getList(): array
	{
		$class = new ReflectionClass(__CLASS__);
		return array_flip($class->getConstants());
	}

	/**
	 * This method returning Localized title of the sections in Permission settings
	 * @param int $value
	 * @return string
	 */
	public static function getTitle(int $value): string
	{
		$sectionsList = self::getList();

		if (!array_key_exists($value, $sectionsList))
		{
			return '';
		}
		$title = $sectionsList[$value];

		return Loc::getMessage("SIGN_CONFIG_SECTIONS_".$title) ?? '';
	}
}
