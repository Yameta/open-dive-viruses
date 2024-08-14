<?php

namespace Bitrix\Sign\Attribute;

use \Attribute;

#[Attribute]
class ActionAccess
{
	public function __construct(
		public string $permission,
	)
	{}
}