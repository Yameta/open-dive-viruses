<?php

namespace Aspro\Smartseo\Condition\Entities\Iblock;

use Aspro\Smartseo;

class IblockSkuPropertyBuilder extends IblockPropertyBuilder
{
	protected $name = 'sku';
	protected $tableAlias = 'isp';

	protected $iblockId = null;
	protected $skuIblockId = null;
	protected $skuPropertyId = null;

	public function __construct($iblockId, $skuIblockId, $skuPropertyId)
	{
		$this->skuIblockId = $skuIblockId;
		$this->skuPropertyId = $skuPropertyId;

		parent::__construct($iblockId);
	}

	public function setOnlyActive(bool $active)
	{
		if ($active == true) {
			$this->query->where('element.ACTIVE', '=', 'Y');
			$this->query->where('parent_element.ACTIVE', '=', 'Y');
		}

		return $this;
	}

	public function getSkuPropertyId()
	{
		return $this->skuPropertyId;
	}

	public function getSkuIblockId()
	{
		return $this->skuIblockId;
	}

	protected function getSectionReferenceField()
	{
		return 'this.parent_element.ID';
	}

	protected function initQuery()
	{
		$this->query = \Bitrix\Iblock\IblockTable::query();

		$this->query->setCustomBaseTableAlias($this->getTableAlias());

		$this->registerJoinElement();

		$this->registerJoinParentElement();

		$this->query->where('ID', '=', $this->skuIblockId);

		$this->addGroup(self::CATEGORY_PROPERTIES, 'ID');
	}

	protected function registerJoinParentElement()
	{
		$join = (new \Bitrix\Main\ORM\Fields\Relations\Reference(
			$this->getName(), \Bitrix\Iblock\ElementPropertyTable::class,
			\Bitrix\Main\ORM\Query\Join::on('ref.IBLOCK_ELEMENT_ID', 'this.element.ID')
				->where('ref.IBLOCK_PROPERTY_ID', '=', $this->skuPropertyId)
		))->configureJoinType('left');

		$this->query->registerRuntimeField($join);

		$join = (new \Bitrix\Main\ORM\Fields\Relations\Reference(
			'parent_element', \Bitrix\Iblock\ElementTable::class,
			\Bitrix\Main\ORM\Query\Join::on('ref.ID', "this.{$this->getName()}.VALUE")
		))->configureJoinType('inner');

		$this->query->registerRuntimeField($join);

		$this->query->where('parent_element.IBLOCK_ID', '=', $this->iblockId);
	}
}
