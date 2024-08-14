<?php

namespace Bitrix\Sign\Item\Api\Property\Request\Field\Fill;

use Bitrix\Sign\Contract;

class Field implements Contract\Item
{
	public string $name;
	public FieldValuesCollection $value;

	public function __construct(string $name, FieldValuesCollection $value)
	{
		$this->name = $name;
		$this->value = $value;
	}
}