<?php

namespace Bitrix\Sign\Repository;

use Bitrix\Main\Entity\UpdateResult;
use Bitrix\Main\Error;
use Bitrix\Main;
use Bitrix\Main\Security\Random;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sign\Internal;
use Bitrix\Sign\Item;
use Bitrix\Sign\Type\Member\EntityType;

class MemberRepository
{
	/**
	 * @param \Bitrix\Sign\Item\Member $item
	 *
	 * @return \Bitrix\Main\Result
	 */
	public function add(Item\Member $item): Main\Result
	{
		$item->uid = $this->generateUniqueUid();

		$now = new DateTime();
		$filledMemberEntity = $this
			->extractModelFromItem($item)
			->setDateCreate($now)
			->setDateModify($now)
		;

		$saveResult = $filledMemberEntity->save();

		if (!$saveResult->isSuccess())
		{
			return (new Main\Result())->addErrors($saveResult->getErrors());
		}

		$item->id = $saveResult->getId();

		return (new Main\Result())->setData(['member' => $item]);
	}

	public function deleteById(int $id)
	{
		Internal\MemberTable::delete($id);
	}

	/**
	 * @param \Bitrix\Sign\Internal\Member|null $model
	 *
	 * @return \Bitrix\Sign\Item\Member
	 */
	private function extractItemFromModel(?Internal\Member $model): Item\Member
	{
		if (!$model)
		{
			return new Item\Member();
		}

		return new Item\Member(
			documentId: $model->getDocumentId(),
			party: $model->getPart(),
			id: $model->getId(),
			uid: $model->getUid(),
			channelType: $model->getCommunicationType(),
			channelValue: $model->getCommunicationValue(),
			dateSigned: $model->getDateSign(),
			entityType: $model->getEntityType(),
			entityId: $model->getEntityId(),
			presetId: $model->getPresetId(),
			signatureFileId: $model->getSignatureFileId(),
			stampFileId: $model->getStampFileId(),
		);
	}

	/**
	 * @param \Bitrix\Sign\Item\Member $item
	 *
	 * @return \Bitrix\Sign\Internal\Member
	 */
	private function extractModelFromItem(Item\Member $item): Internal\Member
	{
		return $this->getFilledModelFromItem($item);
	}

	/**
	 * @param \Bitrix\Sign\Internal\MemberCollection $modelCollection
	 *
	 * @return \Bitrix\Sign\Item\MemberCollection
	 */
	private function extractItemCollectionFromModelCollection(Internal\MemberCollection $modelCollection): Item\MemberCollection
	{
		$models = $modelCollection->getAll();
		$items = array_map([$this, 'extractItemFromModel'], $models);

		return new Item\MemberCollection(...$items);
	}

	/**
	 * @param \Bitrix\Sign\Item\Member $item
	 *
	 * @return \Bitrix\Sign\Internal\Member
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function getFilledModelFromItem(Item\Member $item): Internal\Member
	{
		$model = Internal\MemberTable::createObject(true);

		return $model
			->setCommunicationValue($item->channelValue)
			->setCommunicationType($item->channelType)
			->setPart($item->party)
			->setDocumentId($item->documentId)
			->setPresetId($item->presetId)
			->setEntityId($item->entityId)
			->setEntityType($item->entityType)
			->setHash($item->uid)
			->setStampFileId($item->stampFileId)
			->setSignatureFileId($item->signatureFileId)
			->setModifiedById(Main\Engine\CurrentUser::get()->getId())
			->setCreatedById(Main\Engine\CurrentUser::get()->getId())
			->setContactId($item->entityType === EntityType::CONTACT ? $item->entityId : 0)
			->setMute('N')
			->setSigned('N')
			->setVerified('N')
			;
	}

	public function listByDocumentIdWithParty(int $documentId, int $party, int $limit = 0): Item\MemberCollection
	{
		$models = Internal\MemberTable
			::query()
			->addSelect('*')
			->where('DOCUMENT_ID', $documentId)
			->where('PART', $party)
		;
		if ($limit)
		{
			$models->setLimit($limit);
		}

		return $this->extractItemCollectionFromModelCollection($models->fetchCollection());
	}

	public function getByDocumentIdWithParty(int $documentId, int $party, string $entityType, int $entityId): Item\Member
	{
		$models = Internal\MemberTable
			::query()
			->addSelect('*')
			->where('DOCUMENT_ID', $documentId)
			->where('ENTITY_TYPE', $entityType)
			->where('ENTITY_ID', $entityId)
			->where('PART', $party)
			->setLimit(1)
		;

		return $this->extractItemFromModel($models->fetchObject());
	}

	public function listByDocumentIdExcludeParty(int $documentId, int $party): Item\MemberCollection
	{
		$models = Internal\MemberTable
			::query()
			->addSelect('*')
			->where('DOCUMENT_ID', $documentId)
			->whereNot('PART', $party)
			->fetchCollection()
		;

		return $this->extractItemCollectionFromModelCollection($models);
	}

	public function listByDocumentId(int $documentId): Item\MemberCollection
	{
		$models = Internal\MemberTable
			::query()
			->addSelect('*')
			->where('DOCUMENT_ID', $documentId)
			->fetchCollection()
		;

		return $this->extractItemCollectionFromModelCollection($models);
	}

	public function getByUid(string $uid): ?Item\Member
	{
		$memberEntity = Internal\MemberTable::query()
			->addSelect('*')
			->where('UID', $uid)
			->fetchObject();

		return $memberEntity
			? $this->extractItemFromModel($memberEntity)
			: null
		;
	}

	private function generateUniqueUid(): string
	{
		do
		{
			$uid = $this->generateUid();
		}
		while ($this->isMemberWithUidExist($uid));

		return $uid;
	}

	/**
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 */
	private function isMemberWithUidExist(string $uid): bool
	{
		$fetchData = Internal\MemberTable
			::query()
			->addSelect('ID')
			->addFilter('UID', $uid)
			->setLimit(1)
			->fetch()
		;

		return $fetchData !== false;
	}
	private function generateUid(): string
	{
		return Random::getStringByAlphabet(32, Random::ALPHABET_ALPHALOWER | Random::ALPHABET_NUM);
	}

	public function update(Item\Member $item): UpdateResult
	{
		if (!$item->id)
		{
			return (new UpdateResult())->addError(new Error('Document not found'));
		}

		$member = Internal\MemberTable::getById($item->id)
			->fetchObject();

		if (isset($item->documentId))
		{
			$member->setDocumentId($item->documentId);
		}

		if (isset($item->party))
		{
			$member->setPart($item->party);
		}

		if (isset($item->uid))
		{
			$member->setHash($item->uid);
		}

		if (isset($item->channelType))
		{
			$member->setCommunicationType($item->channelType);
		}

		if (isset($item->channelValue))
		{
			$member->setCommunicationValue($item->channelValue);
		}

		if (isset($item->dateSigned))
		{
			$member->setDateSign($item->dateSigned);
		}

		if (isset($item->ip))
		{
			$member->setIp($item->ip);
		}

		if (isset($item->timeZoneOffset))
		{
			$member->setTimeZoneOffset($item->timeZoneOffset);
		}

		if (isset($item->entityType))
		{
			$member->setEntityType($item->entityType);
		}

		if (isset($item->entityId))
		{
			$member->setEntityId($item->entityId);
		}

		if (isset($item->presetId))
		{
			$member->setPresetId($item->presetId);
		}

		if (isset($item->stampFileId))
		{
			$member->setStampFileId($item->stampFileId);
		}

		return $member->save();
	}

	public function existsByDocumentAndPartAndEntityTypeAndEntityId(
		int $documentId,
		int $party,
		string $entityType,
		int|string $entityId
	): bool
	{
		return (bool)Internal\MemberTable
			::query()
			->addSelect('*')
			->where('DOCUMENT_ID', $documentId)
			->where('ENTITY_TYPE', $entityType)
			->where('ENTITY_ID', $entityId)
			->where('PART', $party)
			->setLimit(1)
			->exec()
			->fetchObject()
			;
	}
}