<?php

namespace Bitrix\Sign\Item\Api\Property\Request\Signing\Configure\Member;

use Bitrix\Sign\Contract;

class Channel implements Contract\Item
{
	public string $type;
	public string $value;

	public function __construct(string $type, string $value)
	{
		$this->type = $type;
		$this->value = $value;
	}
}