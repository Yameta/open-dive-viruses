<?php

namespace Bitrix\Sign\Type;

final class FieldType
{
	public const FILE = 'file';
	public const STRING = 'string';
	public const DOUBLE = 'double';
	public const INTEGER = 'integer';
	public const LIST = 'list';
	public const DATE = 'date';
	public const EMAIL = 'email';
	public const PHONE = 'phone';
	public const NAME = 'name';
	public const SIGNATURE = 'signature';
	public const STAMP = 'stamp';
	public const ADDRESS = 'address';

	/**
	 * @return array<self::*>
	 */
	public static function getAll(): array
	{
		return [
			self::FILE,
			self::STRING,
			self::DOUBLE,
			self::INTEGER,
			self::LIST,
			self::DATE,
			self::EMAIL,
			self::PHONE,
			self::NAME,
			self::SIGNATURE,
			self::STAMP,
			self::ADDRESS,
		];
	}
}