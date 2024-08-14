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
 * Class SmartseoSkuIblockElementPropSingleTable
 *
 * @package Bitrix\Aspro
 **/
class SmartseoSkuIblockElementPropSingleTable extends DataManager
{
	static public $iblockId;

	public static function setIblockId($iblockId)
	{
		self::$iblockId = $iblockId;
	}

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		if (self::$iblockId) {
			return 'b_iblock_element_prop_s' . static::$iblockId;
		}

		return '';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		if (!static::isTableExists()) {
			throw new \Exception('Table name ' . static::getTableName() . ' no exists');
		}

		$sql = 'SHOW FIELDS FROM ' . static::getTableName();

		$columns = \Bitrix\Main\Application::getConnection(static::getConnectionName())->query($sql)->fetchAll();

		$result = [];

		foreach ($columns as $column) {
			$type = preg_replace('/\(.*/', '', $column['Type']);

			if ($column['Key'] === 'PRI') {
				$result[] = new IntegerField(
					$column['Field'],
					[
						'primary' => true,
						'autocomplete' => true,
					]
				);

				continue;
			}

			switch ($type) {
				case 'int':
				case 'real':
				case 'float':
				case 'decimal':
				case 'double':
					$result[] = new IntegerField(
						$column['Field'],
						[
							'required' => false,
						]
					);
					break;

				default:
					$result[] = new StringField(
						$column['Field'],
						[
							'required' => false,
						]
					);
			}
		}

		return $result;
	}

	public static function isTableExists()
	{
		return \Bitrix\Main\Application::getConnection(static::getConnectionName())->isTableExists(
			static::getTableName()
		);
	}
}