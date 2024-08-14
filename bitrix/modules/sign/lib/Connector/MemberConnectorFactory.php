<?php

namespace Bitrix\Sign\Connector;

use Bitrix\Sign\Connector\Crm\Company;
use Bitrix\Sign\Connector\Crm\Contact;
use Bitrix\Sign\Contract;
use Bitrix\Sign\Item;
use Bitrix\Sign\Type;

class MemberConnectorFactory
{
	public function create(Item\Member $member): ?Contract\Connector
	{
		return match ($member->entityType)
		{
			Type\Member\EntityType::CONTACT => new Contact($member->entityId),
			Type\Member\EntityType::COMPANY => new Company($member->entityId),
			default => null,
		};
	}
}