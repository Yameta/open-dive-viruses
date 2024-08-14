<?php

namespace Aspro\Smartseo\Condition\Entities\Iblock2;

use Aspro\Smartseo;

class Builder
{
	const IBLOCK_VERSION_1 = '1';
	const IBLOCK_VERSION_2 = '2';

	const CATEGORY_PROPERTIES = 'properties';
	const CATEGORY_PRICES = 'price';
	const CATEGORY_SECTION = 'section';

	protected $name = '';
	protected $tableAlias = '';
	protected $separator = '__';

	protected $query = null;
	protected $select = [];
	protected $order = [];
	protected $groups = [];
	protected $joins = [];

	protected $whereEntityJoin = null;
	protected $whereValues = null;
	protected $whereSection = null;
	protected $wherePrices = null;
	protected $whereScopePrices = null;

	public function __construct()
	{
		$this->whereEntityJoin = \Bitrix\Main\Entity\Query::filter();
		$this->whereEntityJoin->logic('and');

		$this->whereSection = \Bitrix\Main\Entity\Query::filter();
		$this->whereSection->logic('and');

		$this->whereValues = \Bitrix\Main\Entity\Query::filter();
		$this->whereValues->logic('and');

		$this->whereScopePrices = \Bitrix\Main\Entity\Query::filter();
		$this->whereScopePrices->logic('or');

		$this->wherePrices = \Bitrix\Main\Entity\Query::filter();
		$this->wherePrices->logic('and');
	}

	public function getName()
	{
		return $this->name;
	}

	public function getTableAlias()
	{
		return $this->tableAlias;
	}

	public function getSelect($category = '')
	{
		if ($category) {
			return $this->select[$category] ?: [];
		}

		$result = [];

		foreach ($this->select as $category) {
			$result = array_merge($result, (array)$category);
		}

		return $result;
	}

	public function getOrder($category = '')
	{
		if ($category && $this->order[$category]) {
			return $this->order[$category];
		}

		$result = [];

		foreach ($this->order as $category) {
			$result = array_merge($result, (array)$category);
		}

		return $result;
	}

	public function getGroup($category = '')
	{
		if ($category && $this->groups[$category]) {
			return $this->groups[$category];
		}

		$result = [];

		foreach ($this->groups as $category) {
			$result = array_merge($result, (array)$category);
		}

		return $result;
	}

	public function getJoins($category = '')
	{
		if ($category && $this->joins[$category]) {
			return $this->joins[$category];
		}

		$result = [];

		foreach ($this->joins as $category) {
			$result = array_merge($result, (array)$category);
		}

		return $result;
	}

	public function hasJoins()
	{
		if ($this->joins) {
			return true;
		}

		return false;
	}

	public function hasJoinCategory($category)
	{
		if ($this->joins[$category]) {
			return true;
		}

		return false;
	}

	public function hasJoinAlias($alias)
	{
		foreach ($this->joins as $category) {
			if (isset($category[$alias]) && $category[$alias]) {
				return true;
			}
		}

		return false;
	}

	public function getWhereJoins()
	{
		return $this->whereEntityJoin;
	}

	public function getWhereValues()
	{
		return $this->whereValues;
	}

	public function getWhereSection()
	{
		return $this->whereSection;
	}

	public function getWherePrices()
	{
		return $this->wherePrices;
	}

	public function getWhereScopePrices()
	{
		return $this->whereScopePrices;
	}

	public function addWhereJoins($where)
	{
		$this->whereEntityJoin->where($where);
	}

	public function addWhereValues($where)
	{
		$this->whereValues->where($where);
	}

	public function addWherePrices($where)
	{
		$this->wherePrices->where($where);
	}

	public function addWhereScopePrices($where)
	{
		$this->whereScopePrices->where($where);
	}

	public function getQuery()
	{
		$this->buildQuery();

		return $this->query;
	}

	protected function buildQuery()
	{
		if ($this->getJoins(self::CATEGORY_PROPERTIES)) {
			foreach ($this->getJoins(self::CATEGORY_PROPERTIES) as $join) {
				$this->query->registerRuntimeField($this->getReferenceRelationByJoin($join));
			}
		}

		if ($this->getJoins(self::CATEGORY_PRICES)) {
			foreach ($this->getJoins(self::CATEGORY_PRICES) as $join) {
				$this->query->registerRuntimeField($this->getReferenceRelationByJoin($join));
			}
		}

		if ($this->getJoins(self::CATEGORY_SECTION)) {
			foreach ($this->getJoins(self::CATEGORY_SECTION) as $join) {
				$this->query->registerRuntimeField($this->getReferenceRelationByJoin($join));
			}
		}

		foreach ($this->getSelect(self::CATEGORY_PROPERTIES) as $select) {
			Helper::appendSelect($this->query, $select['FIELD'], $select['ALIAS']);
		}

		foreach ($this->getSelect(self::CATEGORY_PRICES) as $select) {
			Helper::appendSelect($this->query, $select['FIELD'], $select['ALIAS']);
		}

		foreach ($this->getGroup(self::CATEGORY_PROPERTIES) as $group) {
			Helper::appendGroupBy($this->query, $group);
		}

		foreach ($this->getOrder(self::CATEGORY_PROPERTIES) as $order) {
			Helper::appendOrderBy($this->query, $order);
		}

		if ($this->getWhereJoins()->hasConditions()) {
			$this->query->where($this->getWhereJoins());
		}

		if ($this->getWhereValues()->hasConditions()) {
			$this->query->where($this->getWhereValues());
		}

		if ($this->getWherePrices()->hasConditions()) {
			$this->addWhereScopePrices($this->getWherePrices());
		}

		if($this->getWhereScopePrices()->hasConditions()) {
			$this->query->where($this->getWhereScopePrices());
		}

		if ($this->getWhereSection()->hasConditions()) {
			$this->query->where($this->getWhereSection());
		}
	}

	protected function addSelect($category, $select)
	{
		$this->select[$category][] = $select;
	}

	protected function addOrder($category, $order)
	{
		$this->order[$category][] = $order;
	}

	protected function addGroup($category, $group)
	{
		$this->groups[$category][] = $group;
	}

	protected function addJoin($category, $join, $alias = '')
	{
		if ($alias) {
			$this->joins[$category][$alias] = $join;
		} else {
			$this->joins[$category][] = $join;
		}
	}

	protected function addWhereProperty($property, $logics, $alias = '')
	{
		Helper::appendWhereDefaultProperty($this->whereValues, $property, $logics, $alias);
	}

	protected function addWherePropertyElement($property, $alias = '')
	{
		$join = \Bitrix\Main\Entity\Query::filter();
		$join->whereColumn('ref.ID', 'this.' . $property['FIELD']);
		$join->where('ref.IBLOCK_ID', '=', $property['LINK_IBLOCK_ID']);
		$join->where('ref.ACTIVE', '=', 'Y');

		$this->query->registerRuntimeField(
			(new \Bitrix\Main\ORM\Fields\Relations\Reference(
				'link_' . $property['ALIAS'], \Bitrix\Iblock\ElementTable::class, $join))->configureJoinType('inner')
		);

		Helper::appendWhereDefaultProperty($this->whereValues, $property['FIELD'], $property['LOGICS']);
	}

	protected function addWherePropertyNumber($property, $logics, $alias = '')
	{
		Helper::appendWhereNumberProperty($this->whereValues, $property, $logics, $alias);
	}

	protected function addWherePrice($catalogGroupId, $logics, $alias = '')
	{
		Helper::appendWherePrice($this->wherePrices, $catalogGroupId, $logics, $alias);
	}

	protected function getReferenceRelationByJoin($join) {
		return (new \Bitrix\Main\ORM\Fields\Relations\Reference(
			$join['name'],
			$join['class'],
			$join['join']
		))->configureJoinType($join['joinType']);
	}

	protected function getAlias($inputAlias = '')
	{
		if ($inputAlias) {
			return mb_strtolower($this->separator . $this->getName() . $this->separator . $inputAlias);
		}

		return mb_strtolower($this->separator);
	}
}
