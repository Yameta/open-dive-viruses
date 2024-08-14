<?php

namespace Bitrix\Sign\Controllers\V1\Document;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\Request;
use Bitrix\Main\SystemException;
use Bitrix\Sign\Access\ActionDictionary;
use Bitrix\Sign\Attribute;
use Bitrix\Sign\Service\Container;
use Bitrix\Sign\Upload\BlankUploadController;

class Blank extends \Bitrix\Sign\Engine\Controller
{
	public function __construct(Request $request = null)
	{
		parent::__construct($request);
		Loader::includeModule('ui');
	}

	/**
	 * @param array $files
	 *
	 * @return array
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function createAction(array $files): array
	{
		/** @var array<int> $fileIds */
		$fileIds = [];
		foreach ($files as $fileId)
		{
			if (!is_string($fileId))
			{
				$this->addError(new Error("Invalid file id"));

				return [];
			}

			$fileController = new BlankUploadController([]);
			$uploader = new \Bitrix\UI\FileUploader\Uploader($fileController);
			$pendingFiles = $uploader->getPendingFiles([$fileId]);
			$pendingFiles->makePersistent();
			$file = $pendingFiles->get($fileId);
			$persistentFileId = $file?->getFileId();

			if ($persistentFileId === null)
			{
				$this->addError(new Error("Invalid file id"));

				return [];
			}
			$fileIds[] = $persistentFileId;
		}

		$result = Container::instance()->getSignBlankService()->createFromFileIds($fileIds);
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return [];
		}

		return [
			'id' => $result->getId(),
		];
	}

	/**
	 * @param int $blankId
	 *
	 * @return array
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function loadAction(int $blankId): array
	{
		$blank = Container::instance()
			->getBlankRepository()
			->getByIdAndValidatePermissions($blankId)
		;

		if (!$blank)
		{
			return [];
		}

		return get_object_vars($blank);
	}

	/**
	 * @param int $countPerPage
	 * @param int $page
	 *
	 * @return void
	 */
	public function listAction(int $countPerPage = 10, int $page = 1): array
	{
		if ($countPerPage <= 0)
		{
			$this->addError(new Error('Blanks count must be greater than 0. Now: '.$countPerPage));

			return [];
		}
		if ($page <= 0)
		{
			$this->addError(new Error('Blanks page must be greater than 0. Now: '.$page));

			return [];
		}

		[$limit, $offset] = \Bitrix\Sign\Util\Query\Db\Paginator::getLimitAndOffset($countPerPage, $page);

		return Container::instance()
			->getBlankRepository()
			->getPublicList($limit, $offset)
			->toArray();
	}
}