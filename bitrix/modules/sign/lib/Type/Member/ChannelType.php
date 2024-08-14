<?php

namespace Bitrix\Sign\Type\Member;

final class ChannelType
{
	public const PHONE = 'PHONE';
	public const EMAIL = 'EMAIL';

	/**
	 * @return array<self::*>
	 */
	public static function getAll(): array
	{
		return [
			self::PHONE,
			self::EMAIL,
		];
	}
}