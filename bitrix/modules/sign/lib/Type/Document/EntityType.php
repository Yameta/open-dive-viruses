<?php

namespace Bitrix\Sign\Type\Document;

final class EntityType
{
	/** @see \Bitrix\Sign\Document\Entity\Smart */
	public const SMART = 'SMART';

	/**
	 * @return array<self::*>
	 */
	public static function getAll(): array
	{
		return [
			self::SMART,
		];
	}
}