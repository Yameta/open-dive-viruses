<?php

namespace Bitrix\Sign\Item;

use Bitrix\Sign\Contract;

class MemberCollection implements Contract\Item, Contract\ItemCollection, \Iterator, \Countable
{
	private array $items;
	/** @var \ArrayIterator<Member> */
	private \ArrayIterator $iterator;

	public function __construct(Member ...$items)
	{
		$this->items = $items;
		$this->iterator = new \ArrayIterator($this->items);
	}

	public function add(Member $item): MemberCollection
	{
		$this->items[] = $item;

		return $this;
	}

	public function clear(): MemberCollection
	{
		$this->items = [];

		return $this;
	}

	public function toArray(): array
	{
		return $this->items;
	}

	public function current(): ?Member
	{
		return $this->iterator->current();
	}

	public function next(): void
	{
		$this->iterator->next();
	}

	public function key(): int
	{
		return $this->iterator->key();
	}

	public function valid(): bool
	{
		return $this->iterator->valid();
	}

	public function rewind(): void
	{
		$this->iterator = new \ArrayIterator($this->items);
	}

	public function count(): int
	{
		return count($this->items);
	}

	public function isEmpty(): bool
	{
		return empty($this->items);
	}

	public function getFirst(): ?Member
	{
		return $this->items[0] ?? null;
	}

	/**
	 * @param \Closure(Member): bool $rule
	 * @return Member|null
	 */
	final public function findFirst(\Closure $rule): ?Member
	{
		foreach ($this as $item)
		{
			if ($rule($item))
			{
				return $item;
			}
		}

		return null;
	}

	final public function findFirstByParty(int $party): ?Member
	{
		foreach ($this as $item)
		{
			if ($item->party === $party)
			{
				return $item;
			}
		}

		return null;
	}

	/**
	 * @return array<?int>
	 */
	final public function getIds(): array
	{
		$result = [];
		foreach ($this as $member)
		{
			$result[] = $member->id;
		}

		return $result;
	}

	final public function sort(\Closure $rule): static
	{
		$result = $this->items;
		usort($result, $rule);

		return new static(...$result);
	}
}