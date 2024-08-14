<?php

namespace Bitrix\Sign\Service\Sign;

use Bitrix\Main\Localization\Loc;
use Bitrix\Sign\File;
use Bitrix\Sign\Integration\CRM;
use Bitrix\Sign\Repository\DocumentRepository;
use Bitrix\Sign\Repository\FileRepository;
use Bitrix\Sign\Repository\MemberRepository;
use Bitrix\Sign\Restriction;
use Bitrix\Sign\Service\Container;
use Bitrix\Main;
use Bitrix\Sign\Item;
use Bitrix\Sign\Type;
use Bitrix\Sign\Type\Member\EntityType;

class MemberService
{
	private MemberRepository $memberRepository;
	private DocumentRepository $documentRepository;
	private FileRepository $fileRepository;

	private const ALLOWED_ENTITY_TYPES = [
		EntityType::COMPANY,
		EntityType::CONTACT,
	];
	private const CHANNEL_TYPE_PHONE = Type\Member\ChannelType::PHONE;
	private const CHANNEL_TYPE_EMAIL = Type\Member\ChannelType::EMAIL;
	private const ALLOWED_CHANNEL_TYPES = [
		self::CHANNEL_TYPE_PHONE,
		self::CHANNEL_TYPE_EMAIL,
	];

	/**
	 * @param \Bitrix\Sign\Repository\MemberRepository|null $memberRepository
	 * @param \Bitrix\Sign\Repository\DocumentRepository|null $documentRepository
	 */
	public function __construct(
		?MemberRepository $memberRepository = null,
		?DocumentRepository $documentRepository = null,
		?FileRepository $fileRepository = null,
	)
	{
		$this->memberRepository = $memberRepository ?? Container::instance()
			->getMemberRepository();
		$this->documentRepository = $documentRepository ?? Container::instance()
			->getDocumentRepository();
		$this->fileRepository = $fileRepository ?? Container::instance()->getFileRepository();
	}

	/**
	 * @param string $documentUid
	 * @param string $entityType
	 * @param int $entityId
	 * @param int $party
	 * @param int $presetId
	 *
	 * @return \Bitrix\Main\Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function addForDocument(
		string $documentUid,
		string $entityType,
		int $entityId,
		int $party,
		int $presetId = 0
	): Main\Result
	{
		$document = $this->documentRepository->getByUid($documentUid);

		if (!$document)
		{
			return (new Main\Result())->addError(
				new Main\Error(Loc::getMessage('SIGN_SERVICE_MEMBER_DOCUMENT_NOT_FOUND'))
			);
		}

		if (!in_array($entityType, self::ALLOWED_ENTITY_TYPES, true))
		{
			return (new Main\Result())->addError(
				new Main\Error(
					Loc::getMessage('SIGN_SERVICE_MEMBER_ADD_ERROR'),
					'MEMBER_ENTITY_TYPE_NOT_ALLOWED'
				),
			);
		}

		if ($this->memberRepository->existsByDocumentAndPartAndEntityTypeAndEntityId(
			$document->id,
			$party,
			$entityType,
			$entityId,
		))
		{
			return new Main\Result();
		}

		$addResult = $this->memberRepository->add(new Item\Member(
			documentId: $document->id,
			party: $party,
			entityType: $entityType,
			entityId: $entityId,
			presetId: $presetId,
		));

		if (!$addResult->isSuccess())
		{
			return (new Main\Result())->addError(
				new Main\Error(
					Loc::getMessage('SIGN_SERVICE_MEMBER_ADD_ERROR'),
					'MEMBER_ADD_ERROR'
				)
			);
		}

		$member = $addResult->getData()['member'];
		$this->setDefaultCommunications($member);

		if ($presetId === 0)
		{
			$member->presetId = $this->prepareDefaultCrmRequisite($member)->getData()['PRESET_ID'];
		}
		$this->memberRepository->update($member);

		return $addResult;
	}

	/**
	 * @param string $documentUid
	 * @param string $entityType
	 * @param string $entityId
	 * @param int $party
	 *
	 * @return \Bitrix\Main\Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function removeFromDocumentAndPart(
		string $documentUid,
		string $entityType,
		int $entityId,
		int $party,
	): Main\Result
	{
		$document = $this->documentRepository->getByUid($documentUid);

		if (!$document)
		{
			return (new Main\Result())->addError(
				new Main\Error(Loc::getMessage('SIGN_SERVICE_MEMBER_DOCUMENT_NOT_FOUND'))
			);
		}

		$member = $this->memberRepository->getByDocumentIdWithParty(
			$document->id,
			$party,
			$entityType,
			$entityId
		);

		if (!$member->id)
		{
			return (new Main\Result())->addError(new Main\Error(
				Loc::getMessage('SIGN_SERVICE_MEMBER_NOT_FOUND',
					'MEMBER_NOT_FOUND'
				))
			);
		}

		$this->memberRepository->deleteById($member->id);
	}

	/**
	 * @param string $uid
	 *
	 * @return \Bitrix\Main\Result
	 */
	public function remove(string $uid): Main\Result
	{
		$member = $this->memberRepository->getByUid($uid);

		if (!$member->id)
		{
			return (new Main\Result())->addError(
				new Main\Error(Loc::getMessage('SIGN_SERVICE_MEMBER_NOT_FOUND'))
			);
		}

		$this->memberRepository->deleteById($member->id);
		return (new Main\Result());
	}

	/**
	 * @param string $uid
	 * @param string $channelType
	 * @param string $channelValue
	 *
	 * @return Main\Result
	 */
	public function modifyCommunicationChannel(string $uid, string $channelType, string $channelValue): Main\Result
	{
		if (!in_array($channelType, self::ALLOWED_CHANNEL_TYPES, true))
		{
			return (new Main\Result())->addError(
				new Main\Error(Loc::getMessage('SIGN_SERVICE_MEMBER_CHANNEL_NOT_ALLOWED'))
			);
		}

		$member = $this->memberRepository->getByUid($uid);

		if (!$member?->id)
		{
			return (new Main\Result())->addError(
				new Main\Error(Loc::getMessage('SIGN_SERVICE_MEMBER_NOT_FOUND'))
			);
		}

		$member->channelValue = $channelValue;
		$member->channelType = $channelType;

		$result = $this->validateMemberChannelLicenceRestrictions($member);
		if (!$result->isSuccess())
		{
			return $result;
		}

		return $this->memberRepository->update($member);
	}

	/**
	 * @param string $uid
	 *
	 * @return \Bitrix\Sign\Item\Member|null
	 */
	public function getByUid(string $uid): ?Item\Member
	{
		return $this->memberRepository->getByUid($uid);
	}

	private function prepareDefaultCrmRequisite(Item\Member $member): Main\ORM\Data\AddResult|Main\Result
	{
		$document = $this->documentRepository->getById($member->documentId);
		$presetId = $member->party === 1
			? CRM::getMyDefaultPresetId($document->entityId, $member->entityId)
			: CRM::getOtherSidePresetId($document->entityId);

		if (!$presetId && $member->entityId)
		{
			return CRM::createDefaultRequisite(
				$document->entityId,
				$member->entityId,
				\CCrmOwnerType::ResolveID($member->entityType)
			);
		}
		$addResult = new Main\ORM\Data\AddResult();
		$addResult->setId($presetId);
		$addResult->setData(['PRESET_ID' => $presetId]);

		return $addResult;
	}

	/**
	 * @param \Bitrix\Sign\Item\Member $member
	 *
	 * @return array
	 */
	public function getCommunications(Item\Member $member): array
	{
		$connector = (new \Bitrix\Sign\Connector\MemberConnectorFactory())->create($member);

		$values = $connector->fetchFields();
		$communications = [];
		foreach ($values as $field)
		{
			if ($field->name === 'FM')
			{
				foreach ($field->data as $type => $multipleField)
				{
					$communicationTypes = [self::CHANNEL_TYPE_PHONE, self::CHANNEL_TYPE_EMAIL,];
					if (in_array($type, $communicationTypes))
					{
						foreach ($multipleField as $communication)
						{
							if (!isset($communications[$type]))
							{
								$communications[$type] = [];
							}
							$communications[$type][] = $communication;
						}
					}
				}
			}
		}

		return $communications;
	}

	public function saveStampFile(int $fileId, Item\Member $member): Main\Result
	{
		if ($member->entityType !== EntityType::COMPANY)
		{
			return (new Main\Result())->addError(new Main\Error("Member must be the company. Now: `{$member->entityType}`"));
		}
		if ($member->documentId === null)
		{
			return (new Main\Result())->addError(new Main\Error("Member must be the company. Now: `{$member->entityType}`"));
		}
		$stamp = new File($fileId);

		if (!$stamp->isExist())
		{
			return (new Main\Result())->addError(new Main\Error("File with id: `$fileId` doesnt exist"));
		}
		$stamp->setModule('crm');
		$savedStampFileId = $stamp->save();
		if ($savedStampFileId === null)
		{
			$stamp->unlink();
			return (new Main\Result())->addError(new Main\Error("Cant save stamp file"));
		}

		$result = \Bitrix\Sign\Integration\CRM::saveCompanyStamp($member->entityId, $stamp);
		if (!$result)
		{
			$stamp->unlink();
			return (new Main\Result())->addError(new Main\Error("Cant save stamp file"));
		}
		$member->stampFileId = $savedStampFileId;

		$updateResult = $this->memberRepository->update($member);
		if (!$updateResult->isSuccess())
		{
			return (new Main\Result())->addErrors($updateResult->getErrors());
		}

		return (new Main\Result())->setData(['fileId' => $stamp->getId()]);
	}

	public function getStampFileFromMemberOrEntity(Item\Member $member): ?Item\Fs\File
	{
		$stampFileId = $member->stampFileId;
		if ($stampFileId === null)
		{
			return $this->getMemberStampFromEntity($member);
		}

		return $this->fileRepository->getById($stampFileId);
	}

	private function getMemberStampFromEntity(Item\Member $member): ?Item\Fs\File
	{
		if ($member->entityType !== EntityType::COMPANY || $member->entityId === null)
		{
			return null;
		}
		$oldFileEntity = CRM::getCompanyStamp($member->entityId);
		$fileId = $oldFileEntity?->getId();
		if ($fileId === null)
		{
			return null;
		}

		return $this->fileRepository->getById($fileId);
	}

	private function setDefaultCommunications(Item\Member $member): void
	{
		$communications = $this->getCommunications($member);
		$isSmsAllowed = \Bitrix\Sign\Restriction::isSmsAllowed();

		if ($isSmsAllowed && isset($communications[self::CHANNEL_TYPE_PHONE]))
		{
			$member->channelType = self::CHANNEL_TYPE_PHONE;
			$member->channelValue = $communications[self::CHANNEL_TYPE_PHONE][0]['VALUE'] ?? null;
			return;
		}
		
		if (isset($communications[self::CHANNEL_TYPE_EMAIL]))
		{
			$member->channelType = self::CHANNEL_TYPE_EMAIL;
			$member->channelValue = $communications[self::CHANNEL_TYPE_EMAIL][0]['VALUE'] ?? null;
		}
	}

	private function validateMemberChannelLicenceRestrictions(Item\Member $member): Main\Result
	{
		if ($member->channelType === self::CHANNEL_TYPE_PHONE && !Restriction::isSmsAllowed())
		{
			return (new Main\Result())->addError(
				new Main\Error(Loc::getMessage('SIGN_SERVICE_MEMBER_CHANNEL_NOT_ALLOWED_BY_TARIFF'), 'TARIFF_SMS_RESTRICTION')
			);
		}

		return new Main\Result();
	}
}