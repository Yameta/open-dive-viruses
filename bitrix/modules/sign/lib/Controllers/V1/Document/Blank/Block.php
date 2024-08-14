<?php

namespace Bitrix\Sign\Controllers\V1\Document\Blank;

use Bitrix\Sign\Access\ActionDictionary;
use Bitrix\Sign\Blanks;
use Bitrix\Sign\Blanks\Block\Factory;
use Bitrix\Sign\Serializer\ItemPropertyJsonSerializer;
use Bitrix\Sign\Service;
use Bitrix\Sign\Repository;
use Bitrix\Sign\Operation;
use Bitrix\Sign\Item;
use Bitrix\Sign\Attribute;
use Bitrix\Main;

class Block extends \Bitrix\Sign\Engine\Controller
{
	private Service\Sign\BlockService $blockService;

	private Repository\DocumentRepository $documentRepository;
	private Repository\BlankRepository $blankRepository;
	private ItemPropertyJsonSerializer $itemPropertyJsonSerializer;

	public function __construct(Main\Request $request = null)
	{
		$this->blockService = Service\Container::instance()->getSignBlockService();
		$this->blankRepository = Service\Container::instance()->getBlankRepository();
		$this->documentRepository = Service\Container::instance()->getDocumentRepository();
		$this->itemPropertyJsonSerializer = new ItemPropertyJsonSerializer();

		parent::__construct($request);
	}

	#[Attribute\ActionAccess(ActionDictionary::ACTION_DOCUMENT_EDIT)]
	public function saveAction(string $documentUid, array $blocks): array
	{
		$document = $this->documentRepository->getByUid($documentUid);
		if (!$document)
		{
			$this->addError(new Main\Error('Document not found'));
			return [];
		}

		if (!$document->blankId)
		{
			$this->addError(new Main\Error('Blank is not assigned'));
			return [];
		}

		if ($this->documentRepository->getCountByBlankId($document->blankId) > 1)
		{
			$cloneResult = (new Operation\CloneBlankForDocument($document))->launch();
			if (!$cloneResult->isSuccess())
			{
				$this->addErrors($cloneResult->getErrors());
				return [];
			}
		}

		$blockCollection = new Item\BlockCollection();

		$requiredBlockDataKeys = ['code', 'party', 'position', 'style', 'data'];
		foreach ($blocks as $blockData)
		{
			foreach ($requiredBlockDataKeys as $key)
			{
				if (!array_key_exists($key, $blockData))
				{
					$this->addError(new Main\Error("Block data must contains key: `{$key}`"));
					return [];
				}
			}
			[
				'code' => $code,
				'party' => $party,
				'position' => $position,
				'style' => $style,
				'data' => $data,
			] = $blockData;

			try
			{
				/** @var Item\Block\Position $position */
				$position = (new ItemPropertyJsonSerializer())->deserialize((array)$position, Item\Block\Position::class);
			}
			catch (\Exception $exception)
			{
				$this->addError(new Main\Error("Block position has invalid data"));
				return [];
			}
			try
			{
				/** @var Item\Block\Style $style */
				$style = (new ItemPropertyJsonSerializer())->deserialize($style, Item\Block\Style::class);
			}
			catch (\Exception $exception)
			{
				$this->addError(new Main\Error("Block style has invalid data"));
				return [];
			}

			$blockCollection->add(new Item\Block(
				party: $party,
				type: (new Factory())->getTypeByCode($code),
				code: $code,
				blankId: $document->blankId,
				position: $position,
				data: $data,
				id: null,
				style: $style
			));
		}

		$operation = new Operation\Block\RefillForBlank(
			$document->blankId,
			blockCollection: $blockCollection
		);

		$result = $operation->launch();
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());
			return [];
		}

		return [];
	}

	#[Attribute\ActionAccess(ActionDictionary::ACTION_DOCUMENT_EDIT)]
	public function loadDataAction(string $documentUid, array $blocks): array
	{
		$document = $this->documentRepository->getByUid($documentUid);
		if (!$document)
		{
			$this->addError(new Main\Error('Document not found'));
			return [];
		}
		$blank = $this->blankRepository->getById($document->blankId ?? 0);
		if (!$blank)
		{
			$this->addError(new Main\Error('Blank not found'));
			return [];
		}

		$blockFactory = new Factory();

		$result = [];
		foreach ($blocks as $block)
		{
			$item = $blockFactory->makeItem(
				document: $document,
				code: $block['code'] ?? '',
				party: $block['part'] ?? 0,
				data: $block['data'] ?? null,
			);

			$result[] = ['data' => $item->data];
		}

		return $result;
	}

	#[Attribute\ActionAccess(ActionDictionary::ACTION_DOCUMENT_EDIT)]
	public function loadByDocumentAction(string $documentUid): array
	{
		$document = $this->documentRepository->getByUid($documentUid);
		if ($document === null)
		{
			$this->addError(new Main\Error('Document not found'));
			return [];
		}

		$loadResult = $this->blockService->loadBlocksAndDataByDocument($document);
		if (!$loadResult->isSuccess())
		{
			$this->addErrors($loadResult->getErrors());
			return [];
		}

		$blocks = $loadResult->getData()['blocks'] ?? new Item\BlockCollection();
		$result = [];
		foreach ($blocks as $block)
		{
			$result[] = [
				'id' => $block->id,
				'code' => $block->code,
				'data' => $block->data,
				'type' => $block->type,
				'party' => $block->party,
				'style' => $block->style !== null ? $this->itemPropertyJsonSerializer->serialize($block->style) : null,
				'position' => $block->position !== null ? $this->itemPropertyJsonSerializer->serialize($block->position) : null,
			];
		}

		return $result;
	}
}