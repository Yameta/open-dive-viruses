<?php

namespace Bitrix\Sign\Item\Api\Property\Request\Field\Fill;

use Bitrix\Sign\Contract;

class MemberFields implements Contract\Item
{
	public string $memberId;
	public FieldCollection $fields;

	public function __construct(string $memberId, FieldCollection $fields)
	{
		$this->memberId = $memberId;
		$this->fields = $fields;
	}
}