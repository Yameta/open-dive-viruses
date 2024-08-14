<?php

namespace Bitrix\Sign\Controllers\V1;

use Bitrix\Main\Application;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Request;
use Bitrix\Sign\Access\ActionDictionary;
use Bitrix\Sign\Config\Storage;
use Bitrix\Sign\Operation;
use Bitrix\Sign\Service;
use Bitrix\Sign\Attribute;
use Bitrix\Main\ModuleManager;

class Document extends \Bitrix\Sign\Engine\Controller
{
	private Service\Sign\DocumentService $documentService;

	public function __construct(Request $request = null)
	{
		parent::__construct($request);
		$this->documentService = Service\Container::instance()->getDocumentService();
	}

	/**
	 * @param int $blankId
	 *
	 * @return array{uid: string}
	 */
	#[Attribute\ActionAccess(ActionDictionary::ACTION_DOCUMENT_ADD)]
	public function registerAction(int $blankId): array
	{
		$result = $this->documentService->register($blankId);
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());
			return [];
		}

		return [
			'uid' => $result->getData()['document']->uid,
		];
	}

	/**
	 * @param string $uid
	 * @param int $blankId
	 *
	 * @return array{uid: string}
	 */
	#[Attribute\ActionAccess(ActionDictionary::ACTION_DOCUMENT_EDIT)]
	public function changeBlankAction(string $uid, int $blankId): array
	{
		$result = $this->documentService->changeBlank($uid, $blankId);
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());
			return [];
		}

		return [
			'uid' => $result->getData()['document']->uid,
		];
	}

	/**
	 * @param string $uid
	 *
	 * @return array
	 */
	#[Attribute\ActionAccess(ActionDictionary::ACTION_DOCUMENT_ADD)]
	public function uploadAction(
		string $uid
	): array
	{
		$result = $this->documentService->upload($uid);
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());
            $this->documentService->rollbackUploadedDocument($uid);
			return [];
		}

		return [];
	}

	/**
	 * @param string $uid
	 *
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	#[Attribute\ActionAccess(ActionDictionary::ACTION_DOCUMENT_EDIT)]
	public function loadAction(string $uid): array
	{
		$document = Service\Container::instance()->getDocumentRepository()->getByUid($uid);

		if (!$document)
		{
			$this->addError(new Error(Loc::getMessage('SIGN_CONTROLLER_DOCUMENT_NOT_FOUND')));
			return [];
		}

		return (array)$document;
	}

	/**
	 * @return array
	 */
	public function loadLanguageAction(): array
	{
		return Storage::instance()->getLanguages();
	}

	/**
	 * @param string $uid
	 * @param string $title
	 *
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	#[Attribute\ActionAccess(ActionDictionary::ACTION_DOCUMENT_EDIT)]
	public function modifyTitleAction(
		string $uid,
		string $title
	): array
	{
		$result = $this->documentService->modifyTitle($uid, $title);
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());
		}

		return [];
	}

	/**
	 * @param string $uid
	 * @param string $langId
	 *
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	#[Attribute\ActionAccess(ActionDictionary::ACTION_DOCUMENT_EDIT)]
	public function modifyLangIdAction(
		string $uid,
		string $langId
	): array
	{
		$result = $this->documentService->modifyLangId($uid, $langId);
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());
		}

		return [];
	}

	/**
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 */
	#[Attribute\ActionAccess(ActionDictionary::ACTION_DOCUMENT_EDIT)]
	public function modifyInitiatorAction(
		string $uid,
		string $initiator
	): array
	{
		$result = $this->documentService->modifyInitiator($uid, $initiator);
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());
		}

		return [];
	}

	#[Attribute\ActionAccess(ActionDictionary::ACTION_DOCUMENT_EDIT)]
	public function refreshEntityNumberAction(string $documentUid): array
	{
		$document = $this->documentService->getByUid($documentUid);
		if ($document === null)
		{
			return [];
		}
		$result = $this->documentService->refreshEntityNumber($document);
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());
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
	public function configureAction(string $uid): array
	{
		$result = (new Operation\ConfigureDocument($uid))->launch();
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());
		}
		return [];
	}
}
