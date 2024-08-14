<?php

namespace Bitrix\Sign\Operation;

use Bitrix\Main;

use Bitrix\Sign\Item;
use Bitrix\Sign\Repository;
use Bitrix\Sign\Service;

class CloneBlankForDocument
{

	public function __construct(
		private Item\Document $document,
		private ?Repository\DocumentRepository $documentRepository = null,
		private ?Repository\BlankRepository $blankRepository = null,
		private ?Repository\BlockRepository $blockRepository = null,
	)
	{
		$this->documentRepository ??= Service\Container::instance()->getDocumentRepository();
		$this->blankRepository ??= Service\Container::instance()->getBlankRepository();
		$this->blockRepository ??= Service\Container::instance()->getBlockRepository();
	}

	public function getDocument(): Item\Document
	{
		return $this->document;
	}

	public function launch(): Main\Result
	{
		$result = new Main\Result();

		if (!$this->document->id)
		{
			return $result->addError(new Main\Error("Document not found"));
		}

		if (!$this->document->blankId)
		{
			return $result->addError(new Main\Error("Document item field `blankId` is empty"));
		}

		$blank = $this->blankRepository->getById($this->document->blankId);
		if (!$blank)
		{
			return $result->addError(new Main\Error("Blank not found"));
		}

		$result = $this->blankRepository->clone($blank);
		if (!$result->isSuccess())
		{
			return $result;
		}
		$blank->id = $result->getId();

		// Getting blocks and load fields
		$blockCollection = $this->blockRepository->getCollectionByBlankId($this->document->blankId);
		if ($blockCollection->isEmpty())
		{
			return $result;
		}

		foreach ($blockCollection as $block)
		{
			$block->id = null;
			$block->blankId = $blank->id;
		}

		$result = $this->blockRepository->addCollection($blockCollection);
		if (!$result->isSuccess())
		{
			return $result;
		}

		$this->document->blankId = $blank->id;
		return $this->documentRepository->update($this->document);
	}
}