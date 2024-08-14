<?php

namespace Bitrix\Sign\Operation;

use Bitrix\Sign\Item\Api\Document\Signing\StopRequest;
use Bitrix\Sign\Repository\DocumentRepository;
use Bitrix\Sign\Service\Container;
use Bitrix\Sign\Type;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

class SigningStop
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

		$signingStopResponse = Container::instance()->getApiDocumentSigningService()
			->stop(
				new StopRequest($this->uid)
			)
		;

		if (!$signingStopResponse->isSuccess())
		{
			return $result->addErrors($signingStopResponse->getErrors());
		}
		$document->status = Type\DocumentStatus::STOPPED;

		return $this->documentRepository->update($document);
	}
}