<?php

namespace Bitrix\Sign\Repository;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Entity\UpdateResult;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Data\AddResult;
use Bitrix\Main\ORM\Data\DeleteResult;
use Bitrix\Main\Result;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main;
use Bitrix\Sign\Access\Model\UserModel;
use Bitrix\Sign\Access\Permission\SignPermissionDictionary;
use Bitrix\Sign\Access\Service\RolePermissionService;
use Bitrix\Sign\Blank\Block\Date;
use Bitrix\Sign\File;
use Bitrix\Sign\Internal;
use Bitrix\Sign\Item;
use Bitrix\Sign\Item\Blank;
use CCrmPerms;

class BlankRepository
{
	/**
	 * @param Item\Blank $item
	 *
	 * @return AddResult
	 */
	public function add(Item\Blank $item): \Bitrix\Main\Entity\AddResult
	{
		$blank = new Internal\Blank();

		if ($item->fileCollection)
		{
			$files = $item->fileCollection->toArray();
			$fileIds = [];
			foreach ($files as $file)
			{
				$fileIds[] = $file->id;
			}

			$blank->setFileId($fileIds);
		}
		$result = $blank
			->setCreatedById(CurrentUser::get()->getId() ?? 0)
			->setModifiedById(CurrentUser::get()->getId() ?? 0)
			->setTitle($item->title)
			->setDateCreate(new DateTime())
			->setDateModify(new DateTime())
			->save()
		;
		if ($result->isSuccess())
		{
			$item->id = $result->getId();
		}

		return $result;
	}

	/**
	 * @param Blank $item
	 *
	 * @return UpdateResult
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function update(Item\Blank $item): UpdateResult
	{
		if (!$item->id)
		{
			return (new UpdateResult())->addError(new Error('Blank not found'));
		}
		$blank = Internal\BlankTable::getById($item->id)
			->fetchObject();

		if (!$blank)
		{
			return (new UpdateResult())->addError(new Error('Blank not found'));
		}

		if ($item->fileCollection)
		{
			$files = $item->fileCollection->toArray();
			$fileIds = [];
			foreach ($files as $file)
			{
				if ($file->id)
				{
					$fileIds[] = $file->id;
				}
			}

			$blank->setFileId($fileIds);
		}

		if (isset($item->title))
		{
			$blank->setTitle($item->title);
		}

		if (isset($item->status))
		{
			$blank->setStatus($item->status);
		}

		return $blank
			->setModifiedById(CurrentUser::get()->getId() ?? 0)
			->setDateModify(new DateTime())
			->save();
	}

	/**
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function getById(int $blankId): ?Item\Blank
	{
		$document = Internal\BlankTable::getById($blankId)
			->fetchObject();

		return $document === null ? null : $this->extractItemFromModel($document);
	}

	public function getByIdAndValidatePermissions(int $blankId): ?Item\Blank
	{
		$document = Internal\BlankTable::query()
			->setFilter($this->getFilterForBlankByPermissions(CurrentUser::get()->getId()))
			->addFilter('ID', $blankId)
			->setLimit(1)
			->fetchObject()
		;

		return $document === null ? null : $this->extractItemFromModel($document);
	}

	private function extractItemFromModel(Internal\Blank $model): Item\Blank
	{
		$item = new Item\Blank(
			title: $model->getTitle(),
			fileCollection: new Item\Fs\FileCollection(),
			status: $model->getStatus(),
			id: $model->getId(),
			dateCreate: $model->getDateCreate(),
			createdById: $model->getCreatedById(),
		);

		foreach ($model->getFileId() ?? [] as $fileId)
		{
			$file = new File($fileId);
			if ($file->isExist())
			{
				$item->fileCollection->addItem(new Item\Fs\File(
					$file->getName(),
					$file->getPath(),
					$file->getType(),
					$file->getId(),
					new Item\Fs\FileContent($file->getContent()),
					$file->isImage()
				));
			}
		}
		return $item;
	}

	public  function getPublicList(int $limit = 10, int $offset = 0): Item\BlankCollection
	{
		if (!Loader::includeModule('crm'))
		{
			return new Item\BlankCollection();
		}

		$filter = $this->getFilterForBlankByPermissions(CurrentUser::get()->getId());

		$collection = Internal\BlankTable::query()
			->setSelect(['TITLE', 'STATUS', 'DATE_CREATE'])
			->setLimit($limit)
			->setOffset($offset)
			->setFilter($filter)
			->addOrder('ID', 'desc')
			->fetchCollection();

		return $this->extractItemCollectionFromModelCollection($collection);
	}

	public function clone(Item\Blank $blankItem): AddResult
	{
		$result = new AddResult();
		$blankModel = Internal\BlankTable::getById($blankItem->id)
			->fetchObject()
		;

		if (!$blankModel)
		{
			return $result->addError(new Error("Blank with id $blankItem->id dont exist"));
		}
		$now = new DateTime();

		$newBlankModel = new Internal\Blank();
		$result = $newBlankModel
			->setTitle($blankModel->getTitle())
			->setCreatedById(CurrentUser::get()->getId() ?? 0)
			->setModifiedById(CurrentUser::get()->getId() ?? 0)
			->setDateCreate($now)
			->setDateModify($now)
			->setFileId($blankModel->getFileId())
			->save()
		;

		return $result;
	}

    public function delete(int $blankId): DeleteResult
    {
        return Internal\BlankTable::delete($blankId);
    }

	private function extractItemCollectionFromModelCollection(Internal\BlankCollection $modelCollection): Item\BlankCollection
	{
		$models = $modelCollection->getAll();
		$items = array_map([$this, 'extractItemFromModel'], $models);

		return new Item\BlankCollection(...$items);
	}

	private function getFilterForBlankByPermissions(int $userId): array
	{
		$user = UserModel::createFromId($userId);
		$permission = (new RolePermissionService)->getValueForPermission(
			$user->getRoles(),
			SignPermissionDictionary::SIGN_TEMPLATES
		);
		$filter = [];
		$filter['=CREATED_BY_ID'] = null;

		if($permission === CCrmPerms::PERM_ALL || $user->isAdmin())
		{
			unset($filter['=CREATED_BY_ID']);
		}
		elseif ($permission === CCrmPerms::PERM_SUBDEPARTMENT)
		{
			unset($filter['=CREATED_BY_ID']);
			$filter['@CREATED_BY_ID'] = $user->getUserDepartmentMembers(true);
		}
		elseif ($permission === CCrmPerms::PERM_DEPARTMENT)
		{
			unset($filter['=CREATED_BY_ID']);
			$filter['@CREATED_BY_ID'] = $user->getUserDepartmentMembers();
		}
		elseif ($permission === CCrmPerms::PERM_SELF)
		{
			$filter['=CREATED_BY_ID'] = $user->getUserId();
		}

		return $filter;
	}
}