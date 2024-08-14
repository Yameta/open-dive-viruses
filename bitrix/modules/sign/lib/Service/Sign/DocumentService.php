<?php

namespace Bitrix\Sign\Service\Sign;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sign\Document;
use Bitrix\Sign\Integration\CRM\Model\EventData;
use Bitrix\Sign\Main\User;
use Bitrix\Sign\Repository\BlankRepository;
use Bitrix\Sign\Repository\DocumentRepository;
use Bitrix\Sign\Service\Container;
use Bitrix\Sign\Item;
use Bitrix\Sign\Service;
use Bitrix\Sign\Operation;
use Bitrix\Sign\Type;

use Bitrix\Main;
use Bitrix\Sign\Type\Document\EntityType;
use Bitrix\Sign\Type\DocumentScenario;
use Bitrix\Sign\Type\DocumentStatus;

class DocumentService
{
	private const ENTITY_TYPE_SMART = EntityType::SMART;
	private DocumentRepository $documentRepository;
	private BlankRepository $blankRepository;
	private BlankService $blankService;
	private Service\Integration\Crm\EventHandlerService $eventHandlerService;
	private Document\Entity\Factory $documentEntityFactory;

	public function __construct(
		?DocumentRepository $documentRepository = null,
		?BlankService $blankService = null,
		?BlankRepository $blankRepository = null,
		?Service\Integration\Crm\EventHandlerService $eventHandlerService = null,
		private bool $checkPermission = true
	)
	{
		$this->documentRepository = $documentRepository ?? Container::instance()
			->getDocumentRepository();
		$this->blankService = $blankService ?? Container::instance()
			->getSignBlankService();
		$this->blankRepository = $blankRepository ?? Container::instance()
			->getBlankRepository();
		$this->eventHandlerService = $eventHandlerService ?? Container::instance()
			->getEventHandlerService();
		$this->documentEntityFactory = new Document\Entity\Factory();
	}

	/**
	 * @param bool $checkPermission
	 *
	 * @return \Bitrix\Sign\Service\Sign\DocumentService
	 */
	public function setCheckPermission(bool $checkPermission): static
	{
		$this->checkPermission = $checkPermission;

		return $this;
	}

	/**
	 * @param int $blankId
	 * @param string $title
	 *
	 * @return \Bitrix\Main\Result
	 */
	public function register(
		int $blankId,
		string $title = '',
		?int $entityId = null,
		?string $entityType = null
	): Main\Result
	{
		$result = new Main\Result();

		try
		{
			$blank = $this->blankRepository->getById($blankId);
		}
		catch (Main\ObjectPropertyException|Main\ArgumentException|Main\SystemException $e)
		{
			$blank = null;
		}

		if (!$blank)
		{
			return $result->addError(new Main\Error(Loc::getMessage('SIGN_SERVICE_DOCUMENT_BLANK_NOT_FOUND')));
		}

		$documentItem = new Item\Document(entityType: $entityType, entityId: $entityId,);
		$result = $this->insertToDB($title, $blank, $documentItem);

		if (!$result->isSuccess())
		{
			return $result;
		}

		$apiDocument = Service\Container::instance()
			->getApiDocumentService();

		$documentRegisterRequest = new Item\Api\Document\RegisterRequest($documentItem->langId);
		$documentRegisterRequest->title = $documentItem->title;

		$documentRegisterResponse = $apiDocument->register($documentRegisterRequest);

		if (!$documentRegisterResponse->isSuccess())
		{
			return $result->addErrors($documentRegisterResponse->getErrors());
		}

		$documentItem->uid = $documentRegisterResponse->uid;

		return $this->documentRepository->update($documentItem);
	}

	/**
	 * @param string $uid
	 * @param string $title
	 *
	 * @return \Bitrix\Main\Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function modifyTitle(string $uid, string $title): Main\Result
	{
		$result = new Main\Result;
		if ($title === '')
		{
			return $result->addError(new Main\Error('Title is empty'));
		}

		$document = $this->documentRepository->getByUid($uid);
		if (!$document)
		{
			return $result->addError(new Main\Error('Document not found'));
		}

		$this->documentEntityFactory->createByDocument($document)?->setTitle($title);

		$document->title = $title;
		$updateResult = $this->documentRepository->update($document);
		if (!$updateResult->isSuccess())
		{
			return $result->addError(new Main\Error('Error when trying to save document'));
		}

		return $result;
	}

	/**
	 * @param string $uid
	 * @param string $title
	 *
	 * @return \Bitrix\Main\Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function modifyLangId(string $uid, string $langId): Main\Result
	{
		$result = new Main\Result;
		if ($langId === '')
		{
			return $result->addError(new Main\Error('Lang id is empty'));
		}

		$document = $this->documentRepository->getByUid($uid);
		if (!$document)
		{
			return $result->addError(new Main\Error('Document not found'));
		}

		$document->langId = $langId;
		$updateResult = $this->documentRepository->update($document);
		if (!$updateResult->isSuccess())
		{
			return $result->addError(new Main\Error('Error when trying to save document'));
		}

		return $result;
	}

	/**
	 * @param string $uid
	 * @param string $initiator
	 *
	 * @return \Bitrix\Main\Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function modifyInitiator(string $uid, string $initiator): Main\Result
	{
		$result = new Main\Result;
		if ($initiator === '')
		{
			return $result->addError(new Main\Error('Initiator is empty'));
		}

		$document = $this->documentRepository->getByUid($uid);
		if (!$document)
		{
			return $result->addError(new Main\Error('Document not found'));
		}

		$document->initiator = $initiator;
		$updateResult = $this->documentRepository->update($document);
		if (!$updateResult->isSuccess())
		{
			return $result->addError(new Main\Error('Error when trying to save document'));
		}

		return $result;
	}

	/**
	 * @param string $title
	 * @param int $blankId
	 * @param \Bitrix\Sign\Item\Document $documentItem
	 *
	 * @return \Bitrix\Main\Result
	 */
	private function insertToDB(string $title, Item\Blank $blank, Item\Document $documentItem): Main\Result
	{
		$crmSmartDocumentEntityId = $documentItem->entityId > 0 ? $documentItem->entityId
			: Document\Entity\Smart::create($this->checkPermission);
		$crmSmartDocumentEntity = new Document\Entity\Smart($crmSmartDocumentEntityId);

		$documentTitle = $title !== '' ? $title : null;
		$documentTitle ??= $crmSmartDocumentEntity->getTitle() ?? $blank->title;

		$documentItem->title = $documentTitle;
		$documentItem->langId = Context::getCurrent()
			->getLanguage();
		$documentItem->status = DocumentStatus::NEW;
		$documentItem->blankId = $blank->id;
		$documentItem->initiator = $this->createInitiatorName();
		$documentItem->entityId = $crmSmartDocumentEntityId;
		$documentItem->entityType = self::ENTITY_TYPE_SMART;
		$documentItem->scenario = DocumentScenario::SIMPLE_SIGN_MANY_PARTIES_ONE_MEMBERS;
		$documentItem->version = 2;

		$addResult = Service\Container::instance()
			->getDocumentRepository()
			->add($documentItem);

		if (!$addResult->isSuccess())
		{
			return $addResult;
		}

		$eventData = new EventData();
		$eventData->setEventType(EventData::TYPE_ON_REGISTER)
			->setDocument(Document::getById($documentItem->id));

		try
		{
			$this->eventHandlerService->createTimelineEvent($eventData);
		}
		catch (ArgumentException|Main\ArgumentOutOfRangeException $e)
		{
		}

		return $addResult;
	}

	/**
	 * @return string
	 * @see \SignMasterComponent::getResponsibleName
	 */
	private function createInitiatorName(): string
	{
		$lastUserDocuments = $this->getUserLastDocuments(
			User::getInstance()
				->getId(),
			5
		);

		foreach ($lastUserDocuments as $userDocument)
		{
			$initiatorName = $userDocument->initiator;
			if ($initiatorName !== null)
			{
				return $initiatorName;
			}
		}

		return User::getCurrentUserName();
	}

	/**
	 * Change blank for document
	 *
	 * @param string $uid
	 * @param int $blankId
	 *
	 * @return \Bitrix\Main\Result
	 */
	public function changeBlank(string $uid, int $blankId): Main\Result
	{
		['document' => $document, 'result' => $extractionResult] = $this->extractDocumentAndBlank($uid, $blankId);

		if (!$extractionResult->isSuccess())
		{
			return $extractionResult;
		}

		$document->blankId = $blankId;

		return $this->documentRepository->update($document);
	}

	/**
	 * Upload document file to signing server
	 *
	 * @param string $uid
	 *
	 * @return \Bitrix\Main\Result
	 */
	public function upload(string $uid): Main\Result
	{
		['document' => $document, 'blank' => $blank, 'result' => $extractionResult] = $this->extractDocumentAndBlank(
			$uid
		);
		if (!$extractionResult->isSuccess())
		{
			return $extractionResult;
		}

		$fileCollection = new Item\Api\Property\Request\Document\Upload\FileCollection();

		foreach ($blank->fileCollection->toArray() as $file)
		{
			if (empty($file->content->data))
			{
				return (new Main\Result())->addError(new Main\Error(Loc::getMessage('SIGN_SERVICE_DOCUMENT_FILE_EMPTY')));
			}

			$fileCollection->addItem(
				new Item\Api\Property\Request\Document\Upload\File(
					$file->name, $file->type, base64_encode($file->content->data)
				)
			);
		}
		$documentUploadRequest = new Item\Api\Document\UploadRequest($uid, $fileCollection);
		$apiDocument = Service\Container::instance()
			->getApiDocumentService();
		$documentUploadResponse = $apiDocument->upload($documentUploadRequest);

		if (!$documentUploadResponse->isSuccess())
		{
			return (new Main\Result())->addErrors($documentUploadResponse->getErrors());
		}

		$document->status = DocumentStatus::UPLOADED;

		return $this->documentRepository->update($document);
	}

	/**
	 * @param string $uid
	 * @param int|null $blankId
	 *
	 * @return array{document: Item\Document, blank: Item\Blank, result: Main\Result}
	 */
	private function extractDocumentAndBlank(string $uid, ?int $blankId = null): array
	{
		$result = (new Main\Result());
		try
		{
			$document = $this->documentRepository->getByUid($uid);
		}
		catch (Main\ObjectPropertyException|Main\ArgumentException|Main\SystemException $e)
		{
			$document = null;
		}

		if (!$document)
		{
			$result->addError(new Main\Error(Loc::getMessage('SIGN_SERVICE_DOCUMENT_NOT_FOUND')));
		}

		try
		{
			$blank = $this->blankRepository->getById($blankId ? : $document->blankId);
		}
		catch (Main\ObjectPropertyException|Main\ArgumentException|Main\SystemException $e)
		{
			$blank = null;
		}

		if (!$blank)
		{
			$result->addError(
				new Main\Error(Loc::getMessage('SIGN_SERVICE_DOCUMENT_BLANK_NOT_FOUND'))
			);
		}

		return [
			'document' => $document,
			'blank' => $blank,
			'result' => $result,
		];
	}

	/**
	 * Get document by uid or null if not found
	 *
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function getByUid(string $uid): ?Item\Document
	{
		return $this->documentRepository->getByUid($uid);
	}

	private function getUserLastDocuments(int $userId, int $limit = 10): Item\DocumentCollection
	{
		return $this->documentRepository->listLastByUserCreateId($userId, $limit);
	}

	/**
	 * Configure and start signing process
	 *
	 * @param string $uid
	 *
	 * @return \Bitrix\Main\Result
	 */
	public function configureAndStart(string $uid): Main\Result
	{
		$result = (new Operation\ConfigureDocument($uid))->launch();
		if (!$result->isSuccess())
		{
			return $result;
		}

		$result = (new Operation\SigningStart($uid))->launch();
		if (!$result->isSuccess())
		{
			return $result;
		}

		return $result;
	}

	/**
	 * Get last document by blank id
	 *
	 * @param int $blankId
	 *
	 * @return \Bitrix\Sign\Item\Document|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getLastByBlankId(int $blankId): ?Item\Document
	{
		return $this->documentRepository->getLastByBlankId($blankId);
	}

	public function canEditBlank(Item\Document $document): bool
	{
		return in_array($document->status, [null, Type\DocumentStatus::NEW], true);
	}

	public function refreshEntityNumber(Item\Document $document): Main\Result
	{
		$entity = $this->documentEntityFactory->createByDocument($document);
		if ($entity === null)
		{
			return (new Main\Result())->addError(new Main\Error("Document doesnt contains linked crm entity"));
		}

		$entity->refreshNumber();

		return new Main\Result();
	}

	public function rollbackUploadedDocument(string $uid): Main\Result
	{
		$document = $this->getByUid($uid);
		$documentResult = $this->documentRepository->delete($document);
		if (!$documentResult->isSuccess())
		{
			return $documentResult;
		}

		// skip blank deletion, if assigned to documents
		if ($this->documentRepository->getCountByBlankId($document->blankId) > 0)
		{
			return new Main\Result();
		}

		$blank = $this->blankRepository->getById($document->blankId);

		return $this->blankService->deleteWithResources($blank);
	}
}
