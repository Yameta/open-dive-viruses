<?php

namespace Bitrix\Sign\Item\Api\Property\Request\Field\Fill;

use Bitrix\Sign\Contract;

class FieldCollection implements Contract\ItemCollection, Contract\Item
{
	/** @var Field[] */
	private array $items;

	public function __construct(Field ...$items)
	{
		$this->items = $items;
	}

	public function addItem(Field $item): self
	{
		$this->items[] = $item;
		return $this;
	}

	public function toArray(): array
	{
		return $this->items;
	}
}