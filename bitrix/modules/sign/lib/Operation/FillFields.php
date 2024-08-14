<?php

namespace Bitrix\Sign\Operation;

use Bitrix\Crm\Item;
use Bitrix\Crm\Service\Container;
use Bitrix\Crm\WebForm\Requisite;
use Bitrix\Main\Result;
use Bitrix\Sign\Item\Document;
use Bitrix\Sign\File;
use Bitrix\Sign;
use Bitrix\Sign\Repository\DocumentRepository;
use Bitrix\Sign\Service;
use Bitrix\Sign\Type\FieldType;
use Bitrix\Sign\Type\Member\EntityType;
use Bitrix\Main;
use Bitrix\Crm\Multifield;

use CCrmOwnerType;

class FillFields
{
	private DocumentRepository $documentRepository;
	private static array $resolvedItems = [];

	public function __construct(
		private array $fields,
		private Sign\Item\Member $member,
		?DocumentRepository $documentRepository = null
	)
	{
		$this->documentRepository = $documentRepository ?? Service\Container::instance()
			->getDocumentRepository();
	}

	public function launch(): Result
	{
		if (!$this->isSupportedEntityType())
		{
			return new Result();
		}

		$document = $this->documentRepository->getById($this->member->documentId);

		$fieldsByEntityType = [];
		foreach ($this->fields as $field)
		{
			[$field, $entityName, $item] = $this->extractFieldItem($field, $document);
			$field = $this->checkFieldIsFile($field);

			if (!$item)
			{
				continue;
			}

			$fieldName = str_replace($entityName . "_", "", $field['name']);
			if (isset($field['subName']))
			{
				$fieldsByEntityType[$field['entityTypeId']][$fieldName][$field['subName']] = $field['value'];
			}
			else
			{
				$fieldsByEntityType[$field['entityTypeId']][$fieldName] = $field['value'];
			}
		}

		if (!empty($fieldsByEntityType))
		{
			return $this->updateEntityRequisite($fieldsByEntityType, $document);
		}

		return new Result();
	}

	private function isSupportedEntityType(): bool
	{
		return in_array(mb_strtolower($this->member->entityType), [EntityType::COMPANY, EntityType::CONTACT]);
	}

	private function resolveByEntity(string $entityTypeId, int $entityId): ?Item
	{
		if (!empty(static::$resolvedItems[$entityTypeId][$entityId]))
		{
			return static::$resolvedItems[$entityTypeId][$entityId];
		}

		static::$resolvedItems[$entityTypeId][$entityId] = Container::getInstance()
			?->getFactory($entityTypeId)
			?->getItem($entityId);

		return static::$resolvedItems[$entityTypeId][$entityId];
	}

	private function checkFieldIsFile(array $field): array
	{
		if (in_array($field['type'], [FieldType::SIGNATURE, FieldType::STAMP, FieldType::FILE]))
		{
			$file = new File([
				'name' => $field['value']['name'],
				'type' => $field['value']['type'],
				'content' => $field['value']['content'],
			]);
			$field['value'] = $file->save();
		}

		return $field;
	}

	private function setItemField(Item $item, string $fieldName, mixed $value): void
	{
		try
		{
			if ($fieldName === 'FM')
			{
				$fm = $item->getFm();
				foreach ($value as $typeId => $dataValue)
				{
					foreach ($fm as $multiField)
					{
						if ($multiField->getTypeId() === $typeId && $dataValue === $multiField->getValue())
						{
							continue 2;
						}
					}

					$fm->add((new Multifield\Value())
						->setTypeId($typeId)
						->setValueType('WORK')
						->setValue($dataValue));
				}
				$value = $fm;
			}

			$item->set($fieldName, $value);
			$item->save();
		}
		catch (Main\ArgumentException)
		{}
	}

	private function updateEntityRequisite(array $fieldsByEntityType, Document $document): Result
	{
		foreach ($fieldsByEntityType as $entityTypeId => $values)
		{
			$item = $this->resolveByEntity(
				$entityTypeId,
				$entityTypeId === CCrmOwnerType::SmartDocument ? $document->entityId : $this->member->entityId
			);

			[$rqValues, $entityValues] = Requisite::separateFieldValues($entityTypeId, $values);
			if ($rqValues)
			{
				Requisite::instance()->fill(
					$entityTypeId,
					$this->member->entityId,
					$rqValues,
					$this->member->presetId
				);
			}

			foreach ($entityValues as $entityFieldKey => $entityFieldValue)
			{
				$this->setItemField($item, $entityFieldKey, $entityFieldValue);
			}
		}

		return new Result();
	}/**
 * @param mixed $field
 * @param \Bitrix\Sign\Item\Document|null $document
 *
 * @return array
 */
	public function extractFieldItem(array $field, ?Sign\Item\Document $document): array
	{
		$explodedField = explode('.', $field['name']);
		$field['name'] = mb_strstr($field['name'], '.', true) ? : $field['name'];

		if (isset($explodedField[1]) && $explodedField[1] === FieldType::ADDRESS)
		{
			$field['subName'] = $explodedField[count($explodedField) - 1];
		}
		$explodedFieldName = explode('_', $field['name']);

		foreach ([
			\CCrmFieldMulti::PHONE,
			\CCrmFieldMulti::EMAIL,
			\CCrmFieldMulti::LINK,
			\CCrmFieldMulti::WEB,
			\CCrmFieldMulti::IM,
		] as $type)
		{
			if (mb_strpos($field['name'], $type) !== false)
			{
				$field['subName'] = $type;
				$field['name'] = 'FM';
				break;
			}
		}

		$isSmart = $explodedFieldName[0] === 'SMART';
		$entityName = $isSmart ? $explodedFieldName[0]."_".$explodedFieldName[1] : $explodedFieldName[0];
		$entityTypeId = CCrmOwnerType::ResolveId($entityName);

		$item = $this->resolveByEntity(
			$entityTypeId,
			$isSmart ? $document->entityId : $this->member->entityId
		);

		if (!$item)
		{
			$entityTypeId = CCrmOwnerType::ResolveId($this->member->entityType);
			$item = $this->resolveByEntity(
				$entityTypeId,
				$this->member->entityId
			);
		}

		$field['entityTypeId'] = $entityTypeId;
		return [$field, $entityName, $item,];
	}
}
