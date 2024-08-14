<?php

namespace Aspro\Smartseo\Models;

use Aspro\Smartseo\Admin\Settings\SettingSmartseo,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\ORM\Data\DataManager,
	Bitrix\Main\ORM\Fields\IntegerField,
	Bitrix\Main\ORM\Fields\StringField,
	Bitrix\Main\ORM\Fields\TextField,
	Bitrix\Main\ORM\Fields\Relations\Reference,
	Bitrix\Main\ORM\Query\Join;

Loc::loadMessages(__FILE__);

/**
 * Class SmartseoIblockTable
 *
 * @package Bitrix\Aspro
 **/
class SmartseoIblockElementPropMultipleTable extends SmartseoIblockElementPropSingleTable
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		if (self::$iblockId) {
			return 'b_iblock_element_prop_m' . static::$iblockId;
		}

		return '';
	}
}