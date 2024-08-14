<?php
namespace Bitrix\Sign\Controller;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Sign\Document;
use Bitrix\Sign\File;

class Blank extends Controller
{
	private const FILES_SENT_WITH_ERRORS_ERROR_CODE = 'FILES_SENT_WITH_ERRORS';
	private const FILES_DOESNT_SEND_ERROR_CODE = 'FILES_DOESNT_SEND';
	private const BLANK_CREATION_HANDLED_ERROR_CODES = [
		'NOT_ALLOWED_EXTENSIONS',
		'FILE_TOO_BIG'
	];

	private const DOCUMENT_ENTITY_TYPE_ID = 'SMART';

	private const IMAGE_SIZES = [
		'width' => 1275,
		'height' => 1650
	];

	protected const BLANKS_PER_PAGE = 10;

	public function getDefaultPreFilters(): array
	{
		return [
			new \Bitrix\Main\Engine\ActionFilter\Authentication(),
			new \Bitrix\Main\Engine\ActionFilter\Csrf(),
			new ActionFilter\Extranet(),
		];
	}

	/**
	 * Assigns blocks with the blank and returns their data.
	 * @param int $documentId Document id.
	 * @param array $blocksData Block items with keys:
	 * - string code Block code.
	 * - int part Member part.
	 * - array data Block data.
	 * @return array|null
	 */
	public function assignBlocksAction(int $documentId, array $blocksData): ?array
	{
		$result = [];

		//string $code, string $part, $data = null
		$document = Document::getById($documentId);
		if ($document && ($blank = $document->getBlank()))
		{
			foreach ($blocksData as $block)
			{
				$block = $blank->assignBlock([
					'code' => $block['code'] ?? '',
					'part' => $block['part'] ?? 0,
					'data' => $block['data'] ?? null
				]);
				if (!$block)
				{
					return null;
				}
				else
				{
					$result[] = $block->getViewData();
				}
			}

			return $result;
		}

		return null;
	}

	/**
	 * Saves the blank.
	 * @param int $documentId Document that initiated saving blank.
	 * @param array|null $blocks Array of blocks to save within blank.
	 * @param array $params Additional params:
	 *      [closeDemoContent] - during save user closed demo content (true if was)
	 * @return bool
	 */
	public function saveAction(int $documentId, ?array $blocks = null, array $params = []): bool
	{
		$document = Document::getById($documentId);
		if ($document)
		{
			$blank = $document->getBlank();
			if ($blank)
			{
				// if we changed common blank, break edit
				if ($blank->getDocumentCount() > 1)
				{
					return false;
				}

				$result = $blank->setBlocks($blocks);
				if (!$result->isSuccess())
				{
					$this->addErrors($result->getErrors());
					return false;
				}
				if ($params['closeDemoContent'] ?? false)
				{
					\CUserOptions::setOption('sign', 'hide_editor_demo', 'Y');
				}

				return true;
			}
		}

		return false;
	}

	/**
	 * Returns blanks list with pagination
	 *
	 * @param int $countPerPage default blanks count - 10.
	 * @param int $page default page - 1
	 * @return array|null
	 */
	public function listAction(int $countPerPage = 10, int $page = 1): ?array
	{
		if ($countPerPage <= 0)
		{
			$this->addError(new Error('Blanks count must be greater than 0. Now: ' . $countPerPage));
			return null;
		}
		if ($page <= 0)
		{
			$this->addError(new Error('Blanks page must be greater than 0. Now: ' . $page));
			return null;
		}

		[$limit, $offset] = \Bitrix\Sign\Util\Query\Db\Paginator::getLimitAndOffset($countPerPage, $page);

		return \Bitrix\Sign\Blank::getPublicList($limit, $offset);
	}

	/**
	 * Return blank info by id
	 *
	 * @param int $id
	 * @return array|null
	 */
	public function getByIdAction(int $id): ?array
	{
		if ($id <= 0)
		{
			$this->addError(new Error("Blank id must be greater than 0. Now: $id", 'INVALID_ID'));
			return null;
		}

		$blank = \Bitrix\Sign\Blank::getById($id);
		if ($blank !== null)
		{
			return [
				'id' => $blank->getId(),
				'title' => $blank->getTitle(),
				'files' => $this->getFileIdsByBlankFile($blank->getFiles()),
			];
		}

		$firstErrorMessage = \Bitrix\Sign\Error::getInstance()->getFirstError();

		if ($firstErrorMessage === null || $firstErrorMessage->getCode() === 'NOT_FOUND')
		{
			$this->addError(new Error("Cant get blank with id $id"));
		}
		else
		{
			$this->addError(new Error("Blank with id $id not found", 'NOT_FOUND'));
		}

		// errors are added in response if collection is not empty
		\Bitrix\Sign\Error::getInstance()->clear();
		return null;
	}

	/**
	 * Create blank by files
	 *
	 * @return array|null return id, title and file ids or null
	 */
	public function createByFilesAction(): ?array
	{
		$filesDataByRequest = $this->getRequest()->getFileList()->toArray();
		$blankCreationResult = $this->createBlankByRequestFiles($filesDataByRequest);

		if (!$blankCreationResult->isSuccess())
		{
			$this->addErrors($blankCreationResult->getErrors());
			return null;
		}

		/** @var \Bitrix\Sign\Blank $blank */
		$blank = $blankCreationResult->getData()["BLANK"];
		return [
			'id' => $blank->getId(),
			'title' => $blank->getTitle(),
			'files' => $this->getFileIdsByBlankFile($blank->getFiles()),
		];
	}

	private function createBlankByRequestFiles(array $requestFiles): Result
	{
		$result = new Result();
		$filesRequestData = $this->getRequest()->getFileList()->toArray();
		if (count($filesRequestData) === 0)
		{
			return $result->addError(
				new Error('Files count must be greater than 0', self::FILES_DOESNT_SEND_ERROR_CODE)
			);
		}

		$filesRequestDataForSignFile = \Bitrix\Sign\Util\Request\File::flatRequestFilesData($filesRequestData);

		/** @var File[] $files */
		$files = [];

		foreach ($filesRequestDataForSignFile as $value)
		{
			if ($value['error'] !== UPLOAD_ERR_OK)
			{
				continue;
			}
			$signFile = new File($value);
			if ($signFile->isImage())
			{
				$signFile->resizeProportional($this::IMAGE_SIZES);
			}

			$files[] = $signFile;
		}

		return $this->createBlankBySignFiles($files);
	}

	private function createBlankBySignFiles(array $files): Result
	{
		$result = new Result();
		$firstBlankFile = array_shift($files);

		if ($firstBlankFile === null)
		{
			$result->addError(new Error('Files sent with errors', self::FILES_SENT_WITH_ERRORS_ERROR_CODE));
			return $result;
		}
		$blank = \Bitrix\Sign\Blank::createFromFile($firstBlankFile);

		if ($blank === null)
		{
			$firstError = \Bitrix\Sign\Error::getInstance()->getFirstError();
			\Bitrix\Sign\Error::getInstance()->clear();

			if (
				$firstError === null
				|| !in_array(
					$firstError->getCode(),
					self::BLANK_CREATION_HANDLED_ERROR_CODES,
					true
				)
			)
			{
				$result->addError(new Error('Cant create blank'));
				return $result;
			}

			$result->addError(new Error($firstError->getMessage(), $firstError->getCode()));
			return $result;
		}

		if (!empty($files) && $firstBlankFile->isImage())
		{
			$blank->addFiles($files);
		}

		return $result->setData([
			'BLANK' => $blank,
		]);
	}

	/**
	 * @param File[] $files
	 * @return void
	 */
	private function getFileIdsByBlankFile(array $files): array
	{
		$result = [];
		foreach ($files as $file)
		{
			$result[] = $file->getId();
		}

		return $result;
	}
}
