<?php

namespace Bitrix\Sign\Item;

use Bitrix\Sign\Contract;
use Bitrix\Sign\Type;

class Document implements Contract\Item
{
	public function __construct(
		public ?string $scenario = null,
		public ?int $parties = null,
		public ?int $id = null,
		public ?string $title = null,
		public ?string $uid = null,
		public ?int $blankId = null,
		public ?string $langId = null,
		public ?string $status = null,
		public ?string $initiator = null,
		public ?string $entityType = null,
		public ?int $entityTypeId = null,
		public ?int $entityId = null,
		public ?int $resultFileId = null,
		public ?int $version = null,
		public ?int $createdById = null,
	) {}
}