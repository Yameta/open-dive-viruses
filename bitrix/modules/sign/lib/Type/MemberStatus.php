<?php

namespace Bitrix\Sign\Type;

final class MemberStatus
{
	public const WAIT = 'wait';
	public const READY = 'ready';
	public const PROCESSING = 'processing';
	public const DONE = 'done';

	/**
	 * @return array<self::*>
	 */
	public static function getAll(): array
	{
		return [
			self::WAIT,
			self::READY,
			self::PROCESSING,
			self::DONE,
		];
	}
}