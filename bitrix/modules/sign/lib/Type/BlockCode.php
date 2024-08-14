<?php

namespace Bitrix\Sign\Type;

final class BlockCode
{
	public const MY_REFERENCE = 'myreference';
	public const MY_REQUISITES = 'myrequisites';
	public const MY_SIGN = 'mysign';
	public const MY_STAMP = 'mystamp';

	public const REFERENCE = 'reference';
	public const REQUISITES = 'requisites';
	public const SIGN = 'sign';
	public const STAMP = 'stamp';

	public const DATE = 'date';
	public const TEXT = 'text';
	public const NUMBER = 'number';

	/**
	 * @return array<self::*>
	 */
	public static function getAll(): array
	{
		return [
			self::TEXT,
			self::DATE,
			self::MY_REFERENCE,
			self::MY_REQUISITES,
			self::MY_SIGN,
			self::MY_STAMP,
			self::REQUISITES,
			self::REFERENCE,
			self::SIGN,
			self::STAMP,
			self::NUMBER,
		];
	}

	/**
	 * @return array<self::*>
	 */
	public static function getCommon(): array
	{
		return [
			self::DATE,
			self::TEXT,
			self::NUMBER
		];
	}
}