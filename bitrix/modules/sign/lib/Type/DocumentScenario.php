<?php

namespace Bitrix\Sign\Type;

final class DocumentScenario
{
	public const SIMPLE_SIGN_ONE_PARTY_MANY_MEMBERS = 'SimpleSign:OneParty.ManyMembers';
	public const SIMPLE_SIGN_MANY_PARTIES_ONE_MEMBERS = 'SimpleSign:ManyParties.OneMember';
	public const DSS_ONE_PARTY_MANY_MEMBERS = 'Dss:OneParty.ManyMembers';
	public const DSS_SECOND_PARTY_MANY_MEMBERS = 'Dss:SecondParty.ManyMembers';

	public static function resolveByParties(int $parties, int $lastPartySignerCount, bool $dss = false): ?string
	{
		if ($dss)
		{
			switch($parties) {
				case 1: return self::DSS_ONE_PARTY_MANY_MEMBERS;
				case 2: return self::DSS_SECOND_PARTY_MANY_MEMBERS;
				default: return null;
			}
		}

		if ($parties === 1 && $lastPartySignerCount > 0)
		{
			return self::SIMPLE_SIGN_ONE_PARTY_MANY_MEMBERS;
		}

		if ($parties > 1 && $lastPartySignerCount === 1)
		{
			return self::SIMPLE_SIGN_MANY_PARTIES_ONE_MEMBERS;
		}

		return null;
	}

	/**
	 * @return array<self::*>
	 */
	public static function getAll(): array
	{
		return [
			self::SIMPLE_SIGN_ONE_PARTY_MANY_MEMBERS,
			self::SIMPLE_SIGN_MANY_PARTIES_ONE_MEMBERS,
			self::DSS_ONE_PARTY_MANY_MEMBERS,
			self::DSS_SECOND_PARTY_MANY_MEMBERS,
		];
	}
}