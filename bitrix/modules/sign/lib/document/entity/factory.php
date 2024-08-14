<?php
namespace Bitrix\Sign\Document\Entity;

use Bitrix\Sign\Item;
use Bitrix\Sign\Type;

class Factory
{
	public function create(string $code, int $entityId): ?Dummy
	{
		return match ($code)
		{
			Type\Document\EntityType::SMART => new \Bitrix\Sign\Document\Entity\Smart($entityId),
			default => null,
		};
	}

	public function createByDocument(Item\Document $document): ?Dummy
	{
		return $this->create($document->entityType, $document->entityId);
	}
}
