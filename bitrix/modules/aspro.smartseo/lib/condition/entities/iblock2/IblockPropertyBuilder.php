<?php

namespace Aspro\Smartseo\Condition\Entities\Iblock2;

use Aspro\Smartseo,
	\Aspro\Smartseo\Models\SmartseoIblockElementPropSingleTable,
	\Aspro\Smartseo\Models\SmartseoIblockElementPropMultipleTable;

use Bitrix\Main\ORM\Data\DataManager;

class IblockPropertyBuilder extends Builder
{
	const TYPE_MAIN = 'main';
	const TYPE_CHILD = 'child';

	protected $name = 'ip';
	protected $tableAlias = 'ip';
	protected $iblockId = null;

	/** @var DataManager|null */
	protected $modelClass = null;
	/** @var DataManager|null */
	protected $modelMultipleClass = null;

	protected $type = '';

	public function __construct($iblockId)
	{
		parent::__construct();

		$this->iblockId = $iblockId;

		$this->type = self::TYPE_MAIN;

		$this->initModels();

		$this->initQuery();
	}

	public function getVersion()
	{
		return '2';
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

	public function addProperty($item, $alias = '')
	{
		if ($item['PROPERTY_MULTIPLE'] === 'Y') {
			$alias = $item['ALIAS'];

			if (!$this->hasJoinAlias($alias)) {
				$this->addJoinPropertyMultipleTable($item['PROPERTY_ID'], $alias);
			}

			if ($item['PROPERTY_TYPE'] === 'N') {

			} elseif ($item['PROPERTY_TYPE'] === 'E') {
				$this->addPropertyMultipleElement($item, $alias);
			} else {
				$this->addPropertyMultipleString($item, $alias);
			}

			return;
		}

		if (!$alias && $this->type === self::TYPE_CHILD) {
			$alias = $this->getName();
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

		if (!$this->hasJoinAlias($alias)) {
			$this->addJoinPriceTable($item['CATALOG_GROUP_ID'], $alias);
		}

		$this->addWherePrice($item['CATALOG_GROUP_ID'], $item['LOGICS'], $alias);
	}

	protected function initModels()
	{
		$this->modelClass = SmartseoIblockElementPropSingleTable::class;

		$this->modelMultipleClass = SmartseoIblockElementPropMultipleTable::class;

		SmartseoIblockElementPropSingleTable::setIblockId($this->iblockId);

		SmartseoIblockElementPropMultipleTable::setIblockId($this->iblockId);

		if (!$this->modelClass::isTableExists()) {
			throw new \Exception('Table name ' . SmartseoIblockElementPropSingleTable::getTableName() . ' no exists');
		}
	}

	protected function initQuery()
	{
		$this->query = $this->modelClass::query();

		$this->query->setCustomBaseTableAlias($this->getTableAlias());

		$this->registerJoinElement();
	}

	protected function getReferenceField()
	{
		return $this->type === self::TYPE_MAIN ? 'this.IBLOCK_ELEMENT_ID' : "this.{$this->getName()}.IBLOCK_ELEMENT_ID";
	}

	protected function getSectionReferenceField()
	{
		return $this->type === self::TYPE_MAIN ? 'this.IBLOCK_ELEMENT_ID' : "this.{$this->getName()}.IBLOCK_ELEMENT_ID";
	}

	protected function getElementReferenceField()
	{
		return $this->type === self::TYPE_MAIN ? 'this.IBLOCK_ELEMENT_ID' : "this.{$this->getName()}.IBLOCK_ELEMENT_ID";
	}

	protected function insertUnionSkuBuild($build)
	{
		if(!$build->hasJoins()) {
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
			\Bitrix\Main\ORM\Query\Join::on('ref.PROPERTY_' . $build->getSkuPropertyId(), 'this.IBLOCK_ELEMENT_ID')
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
			$this->addWhereValues($build->getWhereValues());
		}

		if ($build->hasJoinCategory($build::CATEGORY_PRICES)) {
			$this->addWhereScopePrices($build->getWherePrices());
		}
	}

	protected function addPropertyString($item, $alias = '')
	{
		$_alias = $alias ? $alias . '.' : '';

		$field = [
			'FIELD' => $_alias . 'PROPERTY_' . $item['PROPERTY_ID'],
			'ALIAS' => 'F_PROPERTY_' . $item['PROPERTY_ID'],
			'GROUP_FIELD' => 'F_PROPERTY_' . $item['PROPERTY_ID'],
			'ORDER_FIELD' => 'F_PROPERTY_' . $item['PROPERTY_ID'],
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

		$this->addWhereProperty($field['FIELD'], $field['LOGICS'], $this->getName());
	}

	protected function addPropertyMultipleString($item, $alias = '')
	{
		$_alias = $alias ? $alias . '.' : '';

		$field = [
			'FIELD' => $_alias . 'VALUE',
			'ALIAS' => 'F_PROPERTY_' . $item['PROPERTY_ID'],
			'GROUP_FIELD' => 'F_PROPERTY_' . $item['PROPERTY_ID'],
			'ORDER_FIELD' => 'F_PROPERTY_' . $item['PROPERTY_ID'],
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

		$this->addWhereProperty($field['FIELD'], $field['LOGICS']);
	}

	protected function addPropertyElement($item, $alias = '')
	{
		$_alias = $alias ? $alias . '.' : '';

		$field = [
			'FIELD' => $_alias . 'PROPERTY_' . $item['PROPERTY_ID'],
			'ALIAS' => 'F_PROPERTY_' . $item['PROPERTY_ID'],
			'GROUP_FIELD' => 'F_PROPERTY_' . $item['PROPERTY_ID'],
			'ORDER_FIELD' => 'F_PROPERTY_' . $item['PROPERTY_ID'],
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

		$this->addWherePropertyElement($field);
	}

	protected function addPropertyMultipleElement($item, $alias = '')
	{
		$_alias = $alias ? $alias . '.' : '';

		$field = [
			'FIELD' => $_alias . 'VALUE',
			'ALIAS' => 'F_PROPERTY_' . $item['PROPERTY_ID'],
			'GROUP_FIELD' => 'F_PROPERTY_' . $item['PROPERTY_ID'],
			'ORDER_FIELD' => 'F_PROPERTY_' . $item['PROPERTY_ID'],
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

		$this->addWherePropertyElement($field);
	}

	protected function addPropertyNumber($item, $alias = '')
	{
		$_alias = $alias ? $alias . '.' : '';

		$fields = [
			'MIN' => [
				'FIELD' => \Bitrix\Main\ORM\Query\Query::expr()->min($_alias . 'PROPERTY_' . $item['PROPERTY_ID']),
				'ALIAS' => 'MIN_PROPERTY_' . $item['PROPERTY_ID'],
				'PROPERTY_ID' => $item['PROPERTY_ID'],
				'PROPERTY_TYPE' => $item['PROPERTY_TYPE'],
				'LOGICS' => $item['LOGICS'],
			],
			'MAX' => [
				'FIELD' => \Bitrix\Main\ORM\Query\Query::expr()->max($_alias . 'PROPERTY_' . $item['PROPERTY_ID']),
				'ALIAS' => 'MAX_PROPERTY_' . $item['PROPERTY_ID'],
				'PROPERTY_ID' => $item['PROPERTY_ID'],
				'PROPERTY_TYPE' => $item['PROPERTY_TYPE'],
				'LOGICS' => $item['LOGICS'],
			],
		];

		foreach ($fields as $field) {
			$this->addSelect(self::CATEGORY_PROPERTIES, [
				'FIELD' => $field['FIELD'],
				'ALIAS' => $field['ALIAS'],
			]);
		}

		$this->addWherePropertyNumber('PROPERTY_' . $item['PROPERTY_ID'], $item['LOGICS']);
	}

	protected function registerJoinElement()
	{
		$join = (new \Bitrix\Main\ORM\Fields\Relations\Reference(
			'element', \Bitrix\Iblock\ElementTable::class,
			\Bitrix\Main\ORM\Query\Join::on('ref.ID', $this->getElementReferenceField())
		))->configureJoinType('inner');

		$this->query->registerRuntimeField($join);

		$this->query->where('element.IBLOCK_ID', '=', $this->iblockId);

		$this->addGroup(self::CATEGORY_PROPERTIES, 'element.IBLOCK_ID');
	}

	protected function addJoinPropertyMultipleTable($propertyId, $alias)
	{
		$this->addJoin(self::CATEGORY_PROPERTIES, [
			'name' => $alias,
			'class' => $this->modelMultipleClass,
			'join' => \Bitrix\Main\ORM\Query\Join::on('ref.IBLOCK_ELEMENT_ID', $this->getReferenceField())
				->where('ref.IBLOCK_PROPERTY_ID', '=', $propertyId),
			'joinType' => 'inner',
		]);
	}

	protected function addJoinPriceTable($catalogGroupId, $alias)
	{
		$this->addJoin(self::CATEGORY_PRICES, [
			'name' => $alias,
			'class' => \Bitrix\Catalog\PriceTable::class,
			'join' => \Bitrix\Main\ORM\Query\Join::on('ref.PRODUCT_ID', $this->getReferenceField())
				->where('ref.CATALOG_GROUP_ID', '=', $catalogGroupId),
			'joinType' => 'left',
		]);
	}
}
