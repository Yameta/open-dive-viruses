<?php

namespace Aspro\Smartseo\Condition\Entities\Iblock;

use Aspro\Smartseo;

class IblockPropertyBuilder extends Builder
{
	const TYPE_MAIN = 'main';
	const TYPE_CHILD = 'child';

	protected $name = 'ip';
	protected $tableAlias = 'ip';
	protected $iblockId = null;

	protected $type = '';

	public function __construct($iblockId)
	{
		parent::__construct();

		$this->iblockId = $iblockId;

		$this->type = self::TYPE_MAIN;

		$this->initQuery();
	}

	public function getVersion()
	{
		return '1';
	}

	public function isSubQuery(bool $value)
	{
		$this->type = $value === true ? self::TYPE_CHILD : self::TYPE_MAIN;
	}

	public function setOnlyActive(bool $active)
	{
		if ($active == true) {
			$this->query->where('element.ACTIVE', '=', 'Y');
		}

		return $this;
	}

	public function setSectionMargins(array $sectionMargins, $isIncludeSubsection = true)
	{
		if(!$sectionMargins) {
			return;
		}

		$this->addJoin(self::CATEGORY_SECTION, [
			'name' => 'section_element',
			'class' => \Bitrix\Iblock\SectionElementTable::class,
			'join' => \Bitrix\Main\ORM\Query\Join::on('ref.IBLOCK_ELEMENT_ID', $this->getSectionReferenceField()),
			'joinType' => 'inner',
		]);

		Helper::appendWhereSectionMargin($this->whereSection, $sectionMargins, $isIncludeSubsection, 'section_element');

		return $this;
	}

	public function addSkuPropertyBuild($build)
	{
		if ($build->getVersion() == self::IBLOCK_VERSION_1) {
			$this->insertUnionSkuBuild($build);
		}

		if ($build->getVersion() == self::IBLOCK_VERSION_2) {
			$this->insertUnionSku2Build($build);
		}
	}

	public function addProperty($item)
	{
		$alias = $item['ALIAS'];

		if (!$this->hasJoinAlias($alias)) {
			$this->addJoinPropertyTable($item['PROPERTY_ID'], $alias);
		}

		if ($item['PROPERTY_TYPE'] === 'N') {
			$this->addPropertyNumber($item, $alias);
		} elseif ($item['PROPERTY_TYPE'] === 'E') {
			$this->addPropertyElement($item, $alias);
		} else {
			$this->addPropertyString($item, $alias);
		}
	}

	public function addPrice($item)
	{
		$alias = $this->getName() . '_' . $item['ALIAS'];

		if ($this->hasJoinAlias($alias)) {
			return;
		}

		$fields = [
			'MIN' => [
				'FIELD' => \Bitrix\Main\ORM\Query\Query::expr()->min($alias . '.PRICE_SCALE'),
				'ALIAS' => 'MIN_' . mb_strtoupper($alias),
			],
			'MAX' => [
				'FIELD' => \Bitrix\Main\ORM\Query\Query::expr()->max($alias . '.PRICE_SCALE'),
				'ALIAS' => 'MAX_' . mb_strtoupper($alias),
			],
		];

		$this->addJoinPriceTable($item['CATALOG_GROUP_ID'], $alias);

		$this->addWherePrice($item['CATALOG_GROUP_ID'], $item['LOGICS'], $alias);
	}

	protected function initQuery()
	{
		$this->query = \Bitrix\Iblock\IblockTable::query();

		$this->registerJoinElement();

		$this->query->setCustomBaseTableAlias($this->getName());

		$this->query->where('ID', '=', $this->iblockId);

		$this->addGroup(self::CATEGORY_PROPERTIES, 'ID');
	}

	protected function registerJoinElement()
	{
		$join = (new \Bitrix\Main\ORM\Fields\Relations\Reference(
			'element', \Bitrix\Iblock\ElementTable::class,
			\Bitrix\Main\ORM\Query\Join::on('ref.IBLOCK_ID', 'this.ID')
		))->configureJoinType('inner');

		$this->query->registerRuntimeField($join);
	}

	protected function getReferenceField()
	{
		return $this->type === self::TYPE_MAIN ? 'this.IBLOCK_ELEMENT_ID' : "this.{$this->getName()}.IBLOCK_ELEMENT_ID";
	}

	protected function getSectionReferenceField()
	{
		return 'this.element.ID';
	}

	protected function getElementReferenceField()
	{
		return $this->type === self::TYPE_MAIN ? 'this.element.ID' : "this.{$this->getName()}.IBLOCK_ELEMENT_ID";
	}

	protected function insertUnionSkuBuild($build)
	{
		if (!$build->hasJoins()) {
			return;
		}

		if ($build->getSelect(self::CATEGORY_PROPERTIES)) {
			$this->joins[self::CATEGORY_PRICES] = null;
			$this->select[self::CATEGORY_PRICES] = [];
			$this->wherePrices = \Bitrix\Main\Entity\Query::filter();
		}

		$join = (new \Bitrix\Main\ORM\Fields\Relations\Reference(
			$build->getName(), \Bitrix\Iblock\ElementPropertyTable::class,
			\Bitrix\Main\ORM\Query\Join::on('ref.VALUE', 'this.element.ID')
				->where('ref.IBLOCK_PROPERTY_ID', '=', $build->getSkuPropertyId())
		))->configureJoinType('left');

		$this->query->registerRuntimeField($join);

		foreach ($build->getJoins() as $join) {
			$this->query->registerRuntimeField($this->getReferenceRelationByJoin($join));
		}

		foreach ($build->getSelect() as $select) {
			$this->addSelect(self::CATEGORY_PROPERTIES, $select);
		}

		foreach ($build->getGroup() as $group) {
			$this->addGroup(self::CATEGORY_PROPERTIES, $group);
		}

		foreach ($build->getOrder() as $order) {
			$this->addOrder(self::CATEGORY_PROPERTIES, $order);
		}

		if ($build->getWhereJoins()->hasConditions()) {
			$this->addWhereJoins($build->getWhereJoins());
		}

		if ($build->getWhereValues()->hasConditions()) {
			$this->addWhereValues($build->getWhereValues());
		}

		if ($build->hasJoinCategory($build::CATEGORY_PRICES)) {
			$this->addWhereScopePrices($build->getWherePrices());
		}
	}

	protected function insertUnionSku2Build($build)
	{
		if(!$build->hasJoins() && !$build->getSelect()) {
			return;
		}

		if ($build->getSelect(self::CATEGORY_PROPERTIES)) {
			$this->joins[self::CATEGORY_PRICES] = null;
			$this->select[self::CATEGORY_PRICES] = [];
			$this->wherePrices = \Bitrix\Main\Entity\Query::filter();
		}

		$join = (new \Bitrix\Main\ORM\Fields\Relations\Reference(
			$build->getName(), $build->getModelClass(),
			\Bitrix\Main\ORM\Query\Join::on('ref.PROPERTY_' . $build->getSkuPropertyId(), 'this.element.ID')
		))->configureJoinType('left');

		$this->query->registerRuntimeField($join);

		foreach ($build->getJoins() as $join) {
			$this->query->registerRuntimeField($this->getReferenceRelationByJoin($join));
		}

		foreach ($build->getSelect() as $select) {
			$this->addSelect(self::CATEGORY_PROPERTIES, $select);
		}

		foreach ($build->getGroup() as $group) {
			$this->addGroup(self::CATEGORY_PROPERTIES, $group);
		}

		foreach ($build->getOrder() as $order) {
			$this->addOrder(self::CATEGORY_PROPERTIES, $order);
		}

		if ($build->getWhereValues()->hasConditions()) {
			$this->addWhereJoins($build->getWhereValues());
		}

		if ($build->hasJoinCategory($build::CATEGORY_PRICES)) {
			$this->addWhereScopePrices($build->getWherePrices());
		}
	}

	protected function addPropertyString($item, $alias = '')
	{
		$field = [
			'FIELD' => $alias . '.VALUE',
			'ALIAS' => 'F_PROPERTY_' . $item['PROPERTY_ID'],
			'GROUP_FIELD' => $alias . '.VALUE',
			'ORDER_FIELD' => $alias . '.VALUE',
			'PROPERTY_ID' => $item['PROPERTY_ID'],
			'PROPERTY_TYPE' => $item['PROPERTY_TYPE'],
			'LOGICS' => $item['LOGICS'],
		];

		$this->addSelect(self::CATEGORY_PROPERTIES, [
			'FIELD' => $field['FIELD'],
			'ALIAS' => $field['ALIAS'],
		]);

		$this->addGroup(self::CATEGORY_PROPERTIES, $field['GROUP_FIELD']);

		$this->addOrder(self::CATEGORY_PROPERTIES, $field['ORDER_FIELD']);

		$this->addWhereProperty($field, $alias);
	}

	protected function addPropertyElement($item, $alias = '')
	{
		$field = [
			'FIELD' => $alias . '.VALUE',
			'ALIAS' => 'F_PROPERTY_' . $item['PROPERTY_ID'],
			'GROUP_FIELD' => $alias . '.VALUE',
			'ORDER_FIELD' => $alias . '.VALUE',
			'PROPERTY_ID' => $item['PROPERTY_ID'],
			'PROPERTY_TYPE' => $item['PROPERTY_TYPE'],
			'LINK_IBLOCK_ID' => $item['LINK_IBLOCK_ID'],
			'LOGICS' => $item['LOGICS'],
		];

		$this->addSelect(self::CATEGORY_PROPERTIES, [
			'FIELD' => $field['FIELD'],
			'ALIAS' => $field['ALIAS'],
		]);

		$this->addGroup(self::CATEGORY_PROPERTIES, $field['GROUP_FIELD']);

		$this->addOrder(self::CATEGORY_PROPERTIES, $field['ORDER_FIELD']);

		$this->addWherePropertyElement($field, $alias);
	}

	protected function addPropertyNumber($item, $alias = '')
	{
		$fields = [
			'MIN' => [
				'FIELD' => \Bitrix\Main\ORM\Query\Query::expr()->min($alias . '.VALUE'),
				'ALIAS' => 'MIN_PROPERTY_' . $item['PROPERTY_ID'],
				'PROPERTY_ID' => $item['PROPERTY_ID'],
				'PROPERTY_TYPE' => $item['PROPERTY_TYPE'],
				'LOGICS' => $item['LOGICS'],
			],
			'MAX' => [
				'FIELD' => \Bitrix\Main\ORM\Query\Query::expr()->max($alias . '.VALUE'),
				'ALIAS' => 'MAX_PROPERTY_' . $item['PROPERTY_ID'],
				'PROPERTY_ID' => $item['PROPERTY_ID'],
				'PROPERTY_TYPE' => $item['PROPERTY_TYPE'],
				'LOGICS' => $item['LOGICS'],
			],
		];

		foreach ($fields as $field) {
			$this->addSelect(self::CATEGORY_PRICES, [
				'FIELD' => $field['FIELD'],
				'ALIAS' => $field['ALIAS'],
			]);
		}

		$this->addWherePropertyNumber([
			'PROPERTY_ID' => $item['PROPERTY_ID'],
			'LOGICS' => $item['LOGICS'],
		], $alias);
	}

	protected function addJoinPropertyTable($propertyId, $alias)
	{
		$this->addJoin(self::CATEGORY_PROPERTIES, [
			'name' => $alias,
			'class' => \Bitrix\Iblock\ElementPropertyTable::class,
			'join' => \Bitrix\Main\ORM\Query\Join::on('ref.IBLOCK_ELEMENT_ID', $this->getElementReferenceField())
				->where('ref.IBLOCK_PROPERTY_ID', '=', $propertyId),
			'joinType' => 'inner',
		]);
	}

	protected function addJoinPriceTable($catalogGroupId, $alias)
	{
		$this->addJoin(self::CATEGORY_PRICES, [
			'name' => $alias,
			'class' => \Bitrix\Catalog\PriceTable::class,
			'join' => \Bitrix\Main\ORM\Query\Join::on('ref.PRODUCT_ID', $this->getElementReferenceField())
				->where('ref.CATALOG_GROUP_ID', '=', $catalogGroupId),
			'joinType' => 'left',
		]);
	}
}
