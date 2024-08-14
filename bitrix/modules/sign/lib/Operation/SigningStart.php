<?php

namespace Bitrix\Sign\Operation;

use Bitrix\Sign\Item\Api\Document\Signing\StartRequest;
use Bitrix\Sign\Repository\DocumentRepository;
use Bitrix\Sign\Service\Container;
use Bitrix\Sign\Type;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

class SigningStart
{
	public function __construct(
		private string $uid,
		private ?DocumentRepository $documentRepository = null
	)
	{
		$this->documentRepository ??= Container::instance()->getDocumentRepository();
	}

	public function launch(): Main\Result
	{
		$result = new Main\Result();
		$document = $this->documentRepository->getByUid($this->uid);
		if (!$document)
		{
			return $result->addError(new Main\Error(Loc::getMessage('SIGN_OPERATION_DOCUMENT_NOT_FOUND')));
		}

		$signingStartResponse = Container::instance()->getApiDocumentSigningService()
			->start(
				new StartRequest($this->uid)
			)
		;

		if (!$signingStartResponse->isSuccess())
		{
			return $result->addErrors($signingStartResponse->getErrors());
		}
		$document->status = Type\DocumentStatus::SIGNING;

		return $this->documentRepository->update($document);
	}
}