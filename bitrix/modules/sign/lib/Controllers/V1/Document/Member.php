<?php

namespace Bitrix\Sign\Controllers\V1\Document;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Request;
use Bitrix\Sign\Access\ActionDictionary;
use Bitrix\Sign\Attribute;
use Bitrix\Sign\Service;

class Member extends \Bitrix\Sign\Engine\Controller
{
	private Service\Sign\MemberService $memberService;

	public function __construct(Request $request = null)
	{
		parent::__construct($request);
		$this->memberService = Service\Container::instance()->getMemberService();

	}

	/**
	 * @param string $documentUid
	 *
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	#[Attribute\ActionAccess(ActionDictionary::ACTION_DOCUMENT_EDIT)]
	public function loadAction(string $documentUid): array
	{
		$document = Service\Container::instance()
			->getDocumentRepository()
			->getByUid($documentUid);

		if (!$document)
		{
			$this->addError(new Error(Loc::getMessage('SIGN_CONTROLLER_MEMBER_DOCUMENT_NOT_FOUND')));

			return [];
		}

		return Service\Container::instance()
			->getMemberRepository()
			->listByDocumentId($document->id)
			->toArray();
	}

	/**
	 * @param string $documentUid
	 * @param string $entityType
	 * @param int $entityId
	 * @param int $party
	 * @param int $presetId
	 *
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	#[Attribute\ActionAccess(ActionDictionary::ACTION_DOCUMENT_EDIT)]
	public function addAction(string $documentUid, string $entityType, int $entityId, int $party, int $presetId = 0): array
	{
		$addResult = $this->memberService->addForDocument($documentUid, $entityType, $entityId, $party, $presetId);

		if (!$addResult->isSuccess())
		{
			$this->addErrors($addResult->getErrors());
			return [];
		}

		return [
			'uid' => $addResult->getData()['member']->uid,
		];
	}

	/**
	 * @param string $uid
	 *
	 * @return array
	 */
	#[Attribute\ActionAccess(ActionDictionary::ACTION_DOCUMENT_EDIT)]
	public function removeAction(string $uid)
	{
		$removeResult = $this->memberService->remove($uid);

		if (!$removeResult->isSuccess())
		{
			$this->addErrors($removeResult->getErrors());
			return [];
		}

		return [];
	}

	/**
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 */
	#[Attribute\ActionAccess(ActionDictionary::ACTION_DOCUMENT_EDIT)]
	public function removeByPartAction(string $documentUid, string $entityType, int $entityId, int $party): array
	{
		$removeResult = $this->memberService->removeFromDocumentAndPart($documentUid, $entityType, $entityId, $party);

		if (!$removeResult->isSuccess())
		{
			$this->addErrors($removeResult->getErrors());
			return [];
		}

		return [];
	}

	public function cleanAction(string $documentUid): array
	{
		return [];
	}

	/**
	 * @param string $uid
	 * @param string $channelType
	 * @param string $channelValue
	 *
	 * @return array
	 */
	#[Attribute\ActionAccess(ActionDictionary::ACTION_DOCUMENT_EDIT)]
	public function modifyCommunicationChannelAction(string $uid, string $channelType, string $channelValue): array
	{
		$modifyResult = $this->memberService->modifyCommunicationChannel(
			$uid,
			$channelType,
			$channelValue
		);

		if (!$modifyResult->isSuccess())
		{
			$this->addErrors($modifyResult->getErrors());

			return [];
		}

		return [];
	}

	/**
	 * @param string $uid
	 *
	 * @return array
	 */
	#[Attribute\ActionAccess(ActionDictionary::ACTION_DOCUMENT_EDIT)]
	public function loadCommunicationsAction(string $uid): array
	{
		$member = $this->memberService->getByUid($uid);

		if (!$member)
		{
			return [];
		}

		return $this->memberService->getCommunications($member);
	}

	/**
	 * @param string $uid
	 *
	 * @return array
	 */
	#[Attribute\ActionAccess(ActionDictionary::ACTION_DOCUMENT_EDIT)]
	public function loadAppliedCommunicationAction(string $uid): array
	{
		$member = $this->memberService->getByUid($uid);

		if (!$member || !$member->channelType)
		{
			return [];
		}

		return [
			'type' => $member->channelType,
			'value' => $member->channelValue,
		];
	}
	#[Attribute\ActionAccess(ActionDictionary::ACTION_DOCUMENT_EDIT)]
	public function saveStampAction(string $memberUid, string $fileId): array
	{
		$fileController = new \Bitrix\Sign\Upload\StampUploadController();
		$uploader = new \Bitrix\UI\FileUploader\Uploader($fileController);
		$pendingFiles = $uploader->getPendingFiles([$fileId]);
		$file = $pendingFiles->get($fileId);

		$stampFileId = $file?->getFileId();
		if ($stampFileId === null)
		{
			$this->addError(new Error("File didnt loaded"));
			return [];
		}
		$member = $this->memberService->getByUid($memberUid);
		$result = $this->memberService->saveStampFile($stampFileId, $member);
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());
			return [];
		}
		$savedFileId = (int)$result->getData()['fileId'];

		return [
			'id' => $savedFileId,
			'srcUri' => \CFile::GetPath($stampFileId),
		];
	}

}