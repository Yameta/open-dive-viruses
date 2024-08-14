<?php

namespace Bitrix\Sign\Item\Api\Property\Request\Signing\Configure;

use Bitrix\Sign\Contract;

class Member implements Contract\Item
{
	public int $party;
	public Member\Channel $channel;
	public ?string $key = null;

	public function __construct(
		int $party,
		Member\Channel $channel,
		public string $uid,
		public ?string $name,
	)
	{
		$this->party = $party;
		$this->channel = $channel;
	}
}