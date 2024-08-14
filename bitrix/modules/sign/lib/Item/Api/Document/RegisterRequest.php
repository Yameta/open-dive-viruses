<?php

namespace Bitrix\Sign\Item\Api\Document;

use Bitrix\Sign\Contract;

class RegisterRequest implements Contract\Item
{
	public ?string $title = null;
	public string $lang;

	public function __construct(string $lang)
	{
		$this->lang = $lang;
	}
}