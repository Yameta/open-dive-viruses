<?php

namespace Aspro\Smartseo\Condition;

use Aspro\Smartseo,
	Bitrix\Main\Localization\Loc;

class ConditionQuery
{
	const IBLOCK_VERSION_1 = '1';
	const IBLOCK_VERSION_2 = '2';

	protected $errors = [];

	protected $iblockId = null;
	protected $iblockVersion = '1';
	protected $skuIblockId = null;
	protected $skuIblockVersion = '1';
	protected $skuPropertyId = null;
	protected $conditionItemTree = [];
	protected $sectionIds = [];
	protected $sectionMargins = [];
	protected $isIncludeSubsection = true;
	protected $isOnlyActive = true;
	protected $entityTableFields = [];

	protected $hasProperties = false;
	protected $hasSkuProperties = false;
	protected $hasCatalogPrice = false;

	public function __construct()
	{
	}

	public function addError($error)
	{
		$this->errors[] = $error;
	}

	public function setErrors($errors)
	{
		if (is_array($errors)) {
			$this->errors = array_map(function ($item) {
				return $item;
			}, $errors);
		} else {
			$this->errors[] = $errors;
		}
	}

	public function getErrors()
	{
		return $this->errors;
	}

	public function hasErrors()
	{
		return $this->errors ? true : false;
	}

	public function validate()
	{
		if (!$this->iblockId) {
			$this->addError(get_class($this) . ': IBLOCK_ID param expected not found');

			return false;
		}

		return true;
	}

	public function setIblockId($iblockId)
	{
		$this->iblockId = $iblockId;

		$this->iblockVersion = $this->getIblockVersion($iblockId);

		return $this;
	}

	public function setSkuIblockId($iblockId)
	{
		$this->skuIblockId = $iblockId;

		$this->skuIblockVersion = $this->getIblockVersion($iblockId);

		return $this;
	}

	public function setSkuPropertyId($skuPropertyId)
	{
		$this->skuPropertyId = $skuPropertyId;

		return $this;
	}

	public function setSectionIds(array $sectionIds)
	{
		$this->sectionIds = $sectionIds;

		return $this;
	}

	public function setSectionMargins(array $sectionMargins)
	{
		$this->sectionMargins = $sectionMargins;

		return $this;
	}

	public function isOnlyActiveElement(bool $isOnlyActive)
	{
		$this->isOnlyActive = $isOnlyActive;

		return $this;
	}

	public function setIncludeSubsection(bool $value)
	{
		$this->isIncludeSubsection = $value;
	}

	public function setConditionItemTree(array $conditionItemTree)
	{
		$this->conditionItemTree = $conditionItemTree;

		$this->treeTraversal($this->conditionItemTree);
	}

	public function getConditionItemTree()
	{
		return $this->conditionItemTree;
	}

	public function getQuery()
	{
		if (!$this->validate()) {
			return null;
		}

		$iblockPropertyBuilder = $this->getIblockPropertyBuildeClass();

		$iblockSkuPropertyBuilder = null;
		if ($this->skuIblockId && $this->skuPropertyId) {
			$iblockSkuPropertyBuilder = $this->getIblockSkuPropertyBuilderClass();
		}

		if (($this->hasProperties && $this->hasSkuProperties)
			|| $this->hasCatalogPrice) {

			if($iblockSkuPropertyBuilder !== null) {
				if($this->iblockVersion == self::IBLOCK_VERSION_1 
				|| $this->hasProperties) {
					$iblockSkuPropertyBuilder->isSubQuery(true);
				}
			}
		}

		foreach ($this->getEntityTableFields() as $field) {
			if ($field['ENTITY'] === 'ELEMENT_PROPERTY') {
				$iblockPropertyBuilder->addProperty($field);
			}

			if ($iblockSkuPropertyBuilder !== null && $field['ENTITY'] === 'SKU_ELEMENT_PROPERTY') {
				$iblockSkuPropertyBuilder->addProperty($field);
			}

			if ($field['ENTITY'] === 'CATALOG_GROUP') {
				$iblockPropertyBuilder->addPrice($field);

				if ($iblockSkuPropertyBuilder !== null) {
					$iblockSkuPropertyBuilder->addPrice($field);
				}
			}
		}

		$result = null;

		if ($this->hasProperties && $this->hasSkuProperties) {
			$iblockPropertyBuilder
				->setOnlyActive($this->isOnlyActive)
				->setSectionMargins($this->sectionMargins);

			$iblockPropertyBuilder->addSkuPropertyBuild($iblockSkuPropertyBuilder);

			return $iblockPropertyBuilder->getQuery();
		}

		if ($this->hasProperties) {
			$iblockPropertyBuilder
				->setOnlyActive(true)
				->setSectionMargins($this->sectionMargins);

			if($iblockSkuPropertyBuilder !== null) {
				$iblockPropertyBuilder->addSkuPropertyBuild($iblockSkuPropertyBuilder);
			}

			return $iblockPropertyBuilder->getQuery();
		}

		if ($this->hasSkuProperties) {
			$iblockSkuPropertyBuilder
				->setOnlyActive(true)
				->setSectionMargins($this->sectionMargins);

			return $iblockSkuPropertyBuilder->getQuery();
		}

		if($this->hasCatalogPrice) {
			$iblockPropertyBuilder
				->setOnlyActive($this->isOnlyActive)
				->setSectionMargins($this->sectionMargins);

			if($iblockSkuPropertyBuilder !== null) {
				$iblockPropertyBuilder->addSkuPropertyBuild($iblockSkuPropertyBuilder);
			}

			return $iblockPropertyBuilder->getQuery();
		}

		return null;
	}
	protected function getIblockVersion($iblockId)
	{
		$row = \Bitrix\Iblock\IblockTable::getRow([
			'select' => [
				'VERSION'
			],
			'filter' => [
				'ID' => $iblockId,
			]
		]);

		return $row['VERSION'] ? $row['VERSION'] : '1';
	}

	protected function getIblockPropertyBuildeClass()
	{
		if($this->iblockVersion == self::IBLOCK_VERSION_1) {
			return new Smartseo\Condition\Entities\Iblock\IblockPropertyBuilder($this->iblockId);
		} else {
			return new Smartseo\Condition\Entities\Iblock2\IblockPropertyBuilder($this->iblockId);
		}
	}

	protected function getIblockSkuPropertyBuilderClass()
	{
		if($this->skuIblockVersion == self::IBLOCK_VERSION_1) {
			return new Smartseo\Condition\Entities\Iblock\IblockSkuPropertyBuilder($this->iblockId, $this->skuIblockId, $this->skuPropertyId);
		} else {
			return new Smartseo\Condition\Entities\Iblock2\IblockSkuPropertyBuilder($this->iblockId, $this->skuIblockId, $this->skuPropertyId);
		}
	}

	protected function addEntityField(array $field)
	{
		$this->entityTableFields[$field['ENTITY_ID']] = $field;
	}

	protected function getEntityTableFields()
	{
		return $this->entityTableFields;
	}

	private function treeTraversal($items)
	{
		$result = [];

		foreach ($items as $item) {
			if ($item['GROUP'] == 'Y') {
				$this->treeTraversalLevel($item['CHILDREN']);
			}
		}

		return $result;
	}

	private function treeTraversalLevel($items)
	{
		foreach ($items as $item) {
			if ($item['GROUP'] == 'Y') {
				$this->treeTraversalLevel($item['CHILDREN']);

				continue;
			}

			$field = [];

			if ($item['ENTITY'] == 'ELEMENT_PROPERTY') {
				$this->hasProperties = true;

				$field = [
					'ENTITY' => $item['ENTITY'],
					'ENTITY_ID' => $item['ID'],
					'ALIAS' => $item['ENTITY'] . '_' . $item['ID'],
					'PROPERTY_ID' => $item['ID'],
					'PROPERTY_TYPE' => $item['PROPERTY_TYPE'],
					'PROPERTY_MULTIPLE' => $item['PROPERTY_MULTIPLE'],
					'LINK_IBLOCK_ID' => $item['PROPERTY_LINK_IBLOCK_ID'],
					'LOGICS' => $item['LOGICS'],
				];
			}

			if ($item['ENTITY'] == 'SKU_ELEMENT_PROPERTY') {
				$this->hasSkuProperties = true;

				$field = [
					'ENTITY' => $item['ENTITY'],
					'ENTITY_ID' => $item['ID'],
					'ALIAS' => $item['ENTITY'] . '_' . $item['ID'],
					'PROPERTY_ID' => $item['ID'],
					'PROPERTY_TYPE' => $item['PROPERTY_TYPE'],
					'PROPERTY_MULTIPLE' => $item['PROPERTY_MULTIPLE'],
					'LINK_IBLOCK_ID' => $item['PROPERTY_LINK_IBLOCK_ID'],
					'LOGICS' => $item['LOGICS'],
				];
			}

			if ($item['ENTITY'] == 'CATALOG_GROUP') {
				$this->hasCatalogPrice = true;

				$field = [
					'ENTITY' => $item['ENTITY'],
					'ENTITY_ID' => $item['CATALOG_GROUP_ID'],
					'ALIAS' => $item['ENTITY'] . '_' . $item['CATALOG_GROUP_ID'],
					'CATALOG_GROUP_ID' => $item['CATALOG_GROUP_ID'],
					'LOGICS' => $item['LOGICS'],
				];
			}

			$this->addEntityField($field);
		}
	}
}
