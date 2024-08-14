<?php

namespace Bitrix\Sign\Type\Member;

final class EntityType
{
	public const CONTACT = 'contact';
	public const COMPANY = 'company';

	/**
	 * @return array<self::*>
	 */
	public static function getAll(): array
	{
		return [
			self::CONTACT,
			self::COMPANY,
		];
	}
}