<?php

namespace Bitrix\Sign\Item\Api\Document\Signing;

use Bitrix\Sign\Contract;
use Bitrix\Sign\Item\Api\Property;

class ConfigureRequest implements Contract\Item
{
	public function __construct(
		public string $documentUid,
		public string $title,
		public Property\Request\Signing\Configure\Owner $owner,
		public int $parties,
		public string $scenario,
		public Property\Request\Signing\Configure\FieldCollection $fields,
		public Property\Request\Signing\Configure\BlockCollection $blocks,
		public Property\Request\Signing\Configure\MemberCollection $members,
		public string $langId,
	)
	{}

}